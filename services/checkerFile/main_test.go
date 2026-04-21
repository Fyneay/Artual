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

func TestRabbitMQIntegration(t *testing.T) {
	if os.Getenv("RABBITMQ_HOST") == "" {
		t.Skip("Пропуск: RABBITMQ_HOST не установлен")
	}

	conn, err := connectRabbitMQ()
	require.NoError(t, err, "Подключение к RabbitMQ")
	defer conn.Close()

	ch, err := conn.Channel()
	require.NoError(t, err, "Подключение к каналу")
	defer ch.Close()

	requestQueue := "file.check.request.test"
	responseQueue := "file.check.response.test"

	_, err = ch.QueueDeclare(
		requestQueue,
		false, // не durable
		false,
		false,
		true,
		nil,
	)
	require.NoError(t, err)

	_, err = ch.QueueDeclare(
		responseQueue,
		false,
		false,
		false,
		true,
		nil,
	)
	require.NoError(t, err)

	ch.QueuePurge(requestQueue, false)
	ch.QueuePurge(responseQueue, false)

	responseReceived := make(chan FileCheckResponse, 1)
	msgs, err := ch.Consume(
		responseQueue,
		"test_consumer",
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
				responseReceived <- response
			}
		}
	}()

	testRequest := FileCheckRequest{
		ID:            "test_request_123",
		Type:          "file_check",
		Timestamp:     time.Now().Format(time.RFC3339),
		CallbackQueue: responseQueue,
		CorrelationID: "test_correlation_123",
		Payload: FileCheckPayload{
			FileID:      123,
			FilePath:    "test/quarantine/test_file.txt",
			FullPath:    "/storage/test/quarantine/test_file.txt",
			StorageDisk: "sftp",
			SFTPConfig: SFTPConfig{
				Host:     getEnv("SFTP_HOST", "localhost"),
				Port:     22,
				Username: getEnv("SFTP_USER", "test"),
				Password: getEnv("SFTP_PASSWORD", "test"),
				Root:     getEnv("SFTP_ROOT", "/storage"),
			},
			Action:   "scan",
			Metadata: map[string]interface{}{},
		},
	}

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
			CorrelationId: testRequest.CorrelationID,
			ReplyTo:       responseQueue,
		},
	)
	require.NoError(t, err, "Публикация сообщения")

	select {
	case response := <-responseReceived:
		assert.Equal(t, testRequest.Payload.FileID, response.Payload.FileID, "FileID дожен совпасть")
		assert.Equal(t, testRequest.Payload.FilePath, response.Payload.FilePath, "FilePath должен совпасть")
		assert.NotNil(t, response.Result, "Результат не совпал")
		assert.NotNil(t, response.Result.IsSafe, "IsSafe не установлен")
		t.Logf("Получен ответ: FileID=%d, IsSafe=%v", response.Payload.FileID, response.Result.IsSafe)
	case <-time.After(30 * time.Second):
		t.Fatal("Timeout ответа")
	}

	ch.QueueDelete(requestQueue, false, false, false)
	ch.QueueDelete(responseQueue, false, false, false)
}

func TestRabbitMQConnection(t *testing.T) {
	if os.Getenv("RABBITMQ_HOST") == "" {
		t.Skip("Пропуск: RABBITMQ_HOST не установлен")
	}

	conn, err := connectRabbitMQ()
	require.NoError(t, err, "Нет подключения к RabbitMQ")
	defer conn.Close()

	ch, err := conn.Channel()
	require.NoError(t, err, "Канал не открыт")
	defer ch.Close()

	testQueue := "test_queue_connection"
	_, err = ch.QueueDeclare(
		testQueue,
		false,
		false,
		true,
		false,
		nil,
	)
	require.NoError(t, err, "Очередь не объявлена")

	ch.QueueDelete(testQueue, false, false, false)
}

func TestMessageSerialization(t *testing.T) {
	request := FileCheckRequest{
		ID:            "test_123",
		Type:          "file_check",
		Timestamp:     time.Now().Format(time.RFC3339),
		CallbackQueue: "file.check.response",
		CorrelationID: "corr_123",
		Payload: FileCheckPayload{
			FileID:      456,
			FilePath:    "test/path/file.txt",
			FullPath:    "/storage/test/path/file.txt",
			StorageDisk: "sftp",
			SFTPConfig: SFTPConfig{
				Host:     "localhost",
				Port:     22,
				Username: "user",
				Password: "pass",
				Root:     "/storage",
			},
			Action:   "scan",
			Metadata: map[string]interface{}{"key": "value"},
		},
	}

	data, err := json.Marshal(request)
	require.NoError(t, err)
	assert.NotEmpty(t, data)

	var decodedRequest FileCheckRequest
	err = json.Unmarshal(data, &decodedRequest)
	require.NoError(t, err)

	assert.Equal(t, request.ID, decodedRequest.ID)
	assert.Equal(t, request.Payload.FileID, decodedRequest.Payload.FileID)
	assert.Equal(t, request.Payload.FilePath, decodedRequest.Payload.FilePath)
	assert.Equal(t, request.Payload.SFTPConfig.Host, decodedRequest.Payload.SFTPConfig.Host)
}

func TestFileCheckResponse(t *testing.T) {
	threatName := "TestThreat"

	safeResponse := FileCheckResponse{
		Payload: FileCheckResponsePayload{
			FileID:   123,
			FilePath: "test/file.txt",
		},
		Result: FileCheckResult{
			IsSafe:     true,
			ThreatName: nil,
		},
		Error: nil,
	}

	data, err := json.Marshal(safeResponse)
	require.NoError(t, err)

	var decoded FileCheckResponse
	err = json.Unmarshal(data, &decoded)
	require.NoError(t, err)

	assert.True(t, decoded.Result.IsSafe)
	assert.Nil(t, decoded.Result.ThreatName)
	assert.Nil(t, decoded.Error)

	unsafeResponse := FileCheckResponse{
		Payload: FileCheckResponsePayload{
			FileID:   123,
			FilePath: "test/file.txt",
		},
		Result: FileCheckResult{
			IsSafe:     false,
			ThreatName: &threatName,
		},
		Error: nil,
	}

	data, err = json.Marshal(unsafeResponse)
	require.NoError(t, err)

	err = json.Unmarshal(data, &decoded)
	require.NoError(t, err)

	assert.False(t, decoded.Result.IsSafe)
	assert.NotNil(t, decoded.Result.ThreatName)
	assert.Equal(t, threatName, *decoded.Result.ThreatName)
}

func TestSendResponse(t *testing.T) {
	if os.Getenv("RABBITMQ_HOST") == "" {
		t.Skip("Пропуск: RABBITMQ_HOST не задан")
	}

	conn, err := connectRabbitMQ()
	require.NoError(t, err)
	defer conn.Close()

	ch, err := conn.Channel()
	require.NoError(t, err)
	defer ch.Close()

	testQueue := "test_response_queue"
	_, err = ch.QueueDeclare(
		testQueue,
		false,
		false,
		true,
		false,
		nil,
	)
	require.NoError(t, err)
	defer ch.QueueDelete(testQueue, false, false, false)

	response := FileCheckResponse{
		Payload: FileCheckResponsePayload{
			FileID:   789,
			FilePath: "test/response.txt",
		},
		Result: FileCheckResult{
			IsSafe:     true,
			ThreatName: nil,
		},
	}

	err = sendResponse(ch, testQueue, "test_corr_123", response)
	require.NoError(t, err, "Не отправлен ответ")

	msgs, err := ch.Consume(
		testQueue,
		"test_consumer",
		true,
		false,
		false,
		false,
		nil,
	)
	require.NoError(t, err)

	select {
	case msg := <-msgs:
		var decoded FileCheckResponse
		err := json.Unmarshal(msg.Body, &decoded)
		require.NoError(t, err)
		assert.Equal(t, response.Payload.FileID, decoded.Payload.FileID)
		assert.Equal(t, response.Result.IsSafe, decoded.Result.IsSafe)
	case <-time.After(5 * time.Second):
		t.Fatal("Timeout ответа")
	}
}
