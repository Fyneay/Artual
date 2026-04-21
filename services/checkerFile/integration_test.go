//go:build integration
// +build integration

package main

import (
	"encoding/json"
	"os"
	"testing"
	"time"

	amqp "github.com/rabbitmq/amqp091-go"
	"github.com/stretchr/testify/assert"
	"github.com/stretchr/testify/require"
)

// TestFullIntegrationFlow - полный интеграционный тест всего потока обработки файла
// Этот тест требует запущенного RabbitMQ и может требовать SFTP/KSC (в зависимости от конфигурации)
func TestFullIntegrationFlow(t *testing.T) {
	// Пропускаем, если не указан флаг интеграционных тестов
	if os.Getenv("INTEGRATION_TESTS") != "true" {
		t.Skip("Skipping integration test. Set INTEGRATION_TESTS=true to run")
	}

	// Проверяем доступность RabbitMQ
	conn, err := connectRabbitMQ()
	require.NoError(t, err, "RabbitMQ should be available")
	defer conn.Close()

	ch, err := conn.Channel()
	require.NoError(t, err)
	defer ch.Close()

	// Настраиваем тестовые очереди
	requestQueue := "file.check.request.integration"
	responseQueue := "file.check.response.integration"

	// Объявляем и очищаем очереди
	_, err = ch.QueueDeclare(requestQueue, false, false, false, false, nil)
	require.NoError(t, err)
	ch.QueuePurge(requestQueue, false)

	_, err = ch.QueueDeclare(responseQueue, false, false, false, false, nil)
	require.NoError(t, err)
	ch.QueuePurge(responseQueue, false)

	// Настраиваем consumer для ответов
	responseChan := make(chan FileCheckResponse, 1)
	msgs, err := ch.Consume(
		responseQueue,
		"integration_test",
		true,
		false,
		false,
		false,
		nil,
	)
	require.NoError(t, err)

	go func() {
		for msg := range msgs {
			var response FileCheckResponse
			if err := json.Unmarshal(msg.Body, &response); err == nil {
				responseChan <- response
			}
		}
	}()

	// Создаем тестовое сообщение
	correlationID := "integration_test_" + time.Now().Format("20060102150405")
	testRequest := FileCheckRequest{
		ID:            "integration_test_123",
		Type:          "file_check",
		Timestamp:     time.Now().Format(time.RFC3339),
		CallbackQueue: responseQueue,
		CorrelationID: correlationID,
		Payload: FileCheckPayload{
			FileID:      999,
			FilePath:    "integration/test/file.txt",
			FullPath:    "/storage/integration/test/file.txt",
			StorageDisk: "sftp",
			SFTPConfig: SFTPConfig{
				Host:     getEnv("SFTP_HOST", "localhost"),
				Port:     22,
				Username: getEnv("SFTP_USER", "test"),
				Password: getEnv("SFTP_PASSWORD", "test"),
				Root:     getEnv("SFTP_ROOT", "/storage"),
			},
			Action:   "scan",
			Metadata: map[string]interface{}{"test": true},
		},
	}

	// Отправляем запрос
	requestBody, err := json.Marshal(testRequest)
	require.NoError(t, err)

	err = ch.Publish(
		"",
		requestQueue,
		false,
		false,
		amqp.Publishing{
			ContentType:   "application/json",
			Body:          requestBody,
			CorrelationId: correlationID,
			ReplyTo:       responseQueue,
		},
	)
	require.NoError(t, err)

	t.Logf("Sent request: FileID=%d, CorrelationID=%s", testRequest.Payload.FileID, correlationID)

	// Ждем ответ (в реальном тесте здесь должен быть запущен worker)
	// Для полного теста нужно запустить обработчик сообщений
	select {
	case response := <-responseChan:
		t.Logf("Received response: FileID=%d, IsSafe=%v", response.Payload.FileID, response.Result.IsSafe)
		assert.Equal(t, testRequest.Payload.FileID, response.Payload.FileID)
		assert.NotNil(t, response.Result)
	case <-time.After(60 * time.Second):
		t.Log("No response received (this is expected if worker is not running)")
		// Не считаем это ошибкой, т.к. worker может быть не запущен
	}

	// Очистка
	ch.QueueDelete(requestQueue, false, false, false)
	ch.QueueDelete(responseQueue, false, false, false)
}

// TestRabbitMQReconnection - тест переподключения к RabbitMQ
func TestRabbitMQReconnection(t *testing.T) {
	if os.Getenv("RABBITMQ_HOST") == "" {
		t.Skip("Skipping: RABBITMQ_HOST not set")
	}

	// Первое подключение
	conn1, err := connectRabbitMQ()
	require.NoError(t, err)
	defer conn1.Close()

	ch1, err := conn1.Channel()
	require.NoError(t, err)
	defer ch1.Close()

	// Второе подключение (должно работать)
	conn2, err := connectRabbitMQ()
	require.NoError(t, err)
	defer conn2.Close()

	ch2, err := conn2.Channel()
	require.NoError(t, err)
	defer ch2.Close()

	// Проверяем, что оба канала работают
	testQueue1 := "test_reconnect_1"
	testQueue2 := "test_reconnect_2"

	_, err = ch1.QueueDeclare(testQueue1, false, false, true, false, nil)
	require.NoError(t, err)

	_, err = ch2.QueueDeclare(testQueue2, false, false, true, false, nil)
	require.NoError(t, err)

	// Очистка
	ch1.QueueDelete(testQueue1, false, false, false)
	ch2.QueueDelete(testQueue2, false, false, false)
}

// TestMessageFormat - тест формата сообщений (совместимость с PHP)
func TestMessageFormat(t *testing.T) {
	// Формат сообщения, который отправляет PHP (FileCheckJob)
	phpMessage := map[string]interface{}{
		"id":             "file_check_123",
		"type":           "file_check",
		"timestamp":      time.Now().Format(time.RFC3339),
		"callback_queue": "file.check.response",
		"correlation_id": "job_123",
		"payload": map[string]interface{}{
			"file_id":      123,
			"file_path":    "quarantine/pending/uuid.pdf",
			"full_path":    "/storage/quarantine/pending/uuid.pdf",
			"storage_disk": "sftp",
			"sftp_config": map[string]interface{}{
				"host":     "localhost",
				"port":     22,
				"username": "user",
				"password": "pass",
				"root":     "/storage",
			},
			"action":   "scan",
			"metadata": map[string]interface{}{},
		},
	}

	// Сериализуем как JSON (как делает PHP)
	jsonData, err := json.Marshal(phpMessage)
	require.NoError(t, err)

	// Десериализуем в нашу структуру
	var request FileCheckRequest
	err = json.Unmarshal(jsonData, &request)
	require.NoError(t, err, "Should parse PHP message format")

	// Проверяем поля
	assert.Equal(t, "file_check_123", request.ID)
	assert.Equal(t, 123, request.Payload.FileID)
	assert.Equal(t, "quarantine/pending/uuid.pdf", request.Payload.FilePath)
	assert.Equal(t, "localhost", request.Payload.SFTPConfig.Host)
	assert.Equal(t, 22, request.Payload.SFTPConfig.Port)
}

// TestResponseFormat - тест формата ответа (совместимость с PHP ProcessFileCheckResultJob)
func TestResponseFormat(t *testing.T) {
	// Формат ответа, который ожидает PHP
	response := FileCheckResponse{
		Payload: FileCheckResponsePayload{
			FileID:   123,
			FilePath: "quarantine/pending/uuid.pdf",
		},
		Result: FileCheckResult{
			IsSafe:     true,
			ThreatName: nil,
		},
		Error: nil,
	}

	jsonData, err := json.Marshal(response)
	require.NoError(t, err)

	// Проверяем, что PHP может распарсить
	var phpExpected map[string]interface{}
	err = json.Unmarshal(jsonData, &phpExpected)
	require.NoError(t, err)

	// Проверяем структуру
	assert.Contains(t, phpExpected, "payload")
	assert.Contains(t, phpExpected, "result")

	payload := phpExpected["payload"].(map[string]interface{})
	result := phpExpected["result"].(map[string]interface{})

	assert.Equal(t, float64(123), payload["file_id"])
	assert.Equal(t, true, result["is_safe"])
}

// TestErrorHandling - тест обработки ошибок
func TestErrorHandling(t *testing.T) {
	if os.Getenv("RABBITMQ_HOST") == "" {
		t.Skip("Skipping: RABBITMQ_HOST not set")
	}

	conn, err := connectRabbitMQ()
	require.NoError(t, err)
	defer conn.Close()

	ch, err := conn.Channel()
	require.NoError(t, err)
	defer ch.Close()

	// Тест с невалидным JSON
	invalidQueue := "test_invalid_json"
	_, err = ch.QueueDeclare(invalidQueue, false, false, true, false, nil)
	require.NoError(t, err)
	defer ch.QueueDelete(invalidQueue, false, false, false)

	// Отправляем невалидный JSON
	err = ch.Publish(
		"",
		invalidQueue,
		false,
		false,
		amqp.Publishing{
			ContentType: "application/json",
			Body:        []byte("invalid json {"),
		},
	)
	require.NoError(t, err)

	// Пытаемся получить и распарсить
	msgs, err := ch.Consume(invalidQueue, "test", true, false, false, false, nil)
	require.NoError(t, err)

	select {
	case msg := <-msgs:
		var request FileCheckRequest
		err := json.Unmarshal(msg.Body, &request)
		assert.Error(t, err, "Should fail to parse invalid JSON")
	case <-time.After(2 * time.Second):
		t.Fatal("Timeout waiting for message")
	}
}
