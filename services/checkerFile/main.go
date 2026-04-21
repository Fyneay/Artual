package main

import (
	"context"
	"encoding/json"
	"fmt"
	"log"
	"os"
	"os/signal"
	"syscall"
	"time"

	"github.com/joho/godotenv"
	"github.com/pixfid/go-ksc/kaspersky"
	amqp "github.com/rabbitmq/amqp091-go"
)

// Структуры для RabbitMQ сообщений
type FileCheckRequest struct {
	ID            string           `json:"id"`
	Type          string           `json:"type"`
	Timestamp     string           `json:"timestamp"`
	Payload       FileCheckPayload `json:"payload"`
	CallbackQueue string           `json:"callback_queue"`
	CorrelationID string           `json:"correlation_id"`
}

type FileCheckPayload struct {
	FileID      int                    `json:"file_id"`
	FilePath    string                 `json:"file_path"`
	FullPath    string                 `json:"full_path"`
	StorageDisk string                 `json:"storage_disk"`
	SFTPConfig  SFTPConfig             `json:"sftp_config"`
	Action      string                 `json:"action"`
	Metadata    map[string]interface{} `json:"metadata"`
}

type SFTPConfig struct {
	Host     string `json:"host"`
	Port     int    `json:"port"`
	Username string `json:"username"`
	Password string `json:"password"`
	Root     string `json:"root"`
}

type FileCheckResponse struct {
	Payload FileCheckResponsePayload `json:"payload"`
	Result  FileCheckResult          `json:"result"`
	Error   *string                  `json:"error,omitempty"`
}

type FileCheckResponsePayload struct {
	FileID   int    `json:"file_id"`
	FilePath string `json:"file_path"`
}

type FileCheckResult struct {
	IsSafe     bool    `json:"is_safe"`
	ThreatName *string `json:"threat_name,omitempty"`
}

type KSCClient struct {
	client *kaspersky.KscClient
	ctx    context.Context
	taskID string
}

type TaskStatistics struct {
	PxgRetVal struct {
		Status1              int `json:"1"` // Распространение на хосты (1 = не распространена, 0 = распространена)
		Status2              int `json:"2"`
		Status4              int `json:"4"` // Успешный итог задачи
		Status8              int `json:"8"`
		Status16             int `json:"16"` // Неуспешный итог задачи
		Status32             int `json:"32"`
		Status64             int `json:"64"`
		GNRLCompletedPercent int `json:"GNRL_COMPLETED_PERCENT"`
		KLTSKNeedRbtCnt      int `json:"KLTSK_NEED_RBT_CNT"`
	} `json:"PxgRetVal"`
}

func main() {
	if err := godotenv.Load(); err != nil {
		log.Printf("Ошибка загрузки .env: %v", err)
	}

	kscClient, err := initKSCClient()
	if err != nil {
		log.Fatalf("Ошибка подключения к KSC: %v", err)
	}

	conn, err := connectRabbitMQ()
	if err != nil {
		log.Fatalf("Ошибка подключения к RabbitMQ: %v", err)
	}
	defer conn.Close()

	ch, err := conn.Channel()
	if err != nil {
		log.Fatalf("Ошибка канала: %v", err)
	}
	defer ch.Close()

	requestQueue := "file.check.request"
	responseQueue := "file.check.response"

	_, err = ch.QueueDeclare(
		requestQueue,
		true,
		false,
		false,
		false,
		nil,
	)
	if err != nil {
		log.Fatalf("Ошибка объявленияочереди на отправку: %v", err)
	}

	_, err = ch.QueueDeclare(
		responseQueue,
		true,
		false,
		false,
		false,
		nil,
	)
	if err != nil {
		log.Fatalf("Ошибка объявления очереди на ответ: %v", err)
	}

	err = ch.Qos(
		1,
		0,
		false,
	)
	if err != nil {
		log.Fatalf("Ошибка установки QoS: %v", err)
	}

	msgs, err := ch.Consume(
		requestQueue,
		"",
		false,
		false,
		false,
		false,
		nil,
	)
	if err != nil {
		log.Fatalf("Ошибка слушателя: %v", err)
	}

	log.Println("Сервис проверки запущен")

	sigChan := make(chan os.Signal, 1)
	signal.Notify(sigChan, os.Interrupt, syscall.SIGTERM)

	go func() {
		for msg := range msgs {
			handleMessage(ch, msg, kscClient)
		}
	}()

	<-sigChan
	log.Println("Завершение сервиса")
}

func initKSCClient() (*KSCClient, error) {
	ctx := context.Background()

	cfg := kaspersky.Config{
		Server:             os.Getenv("KSC_HOST"),
		UserName:           os.Getenv("KSC_USER"),
		Password:           os.Getenv("KSC_PASSWORD"),
		Domain:             os.Getenv("KSC_DOMAIN"),
		XKscSession:        false,
		InsecureSkipVerify: true,
	}

	client := kaspersky.NewKscClient(cfg)
	if err := client.Login(ctx, kaspersky.BasicAuth, ""); err != nil {
		return nil, fmt.Errorf("Ошибка подключения к KSC: %w", err)
	}

	taskID := os.Getenv("KSC_SCAN_TASK_ID")
	if taskID == "" {
		taskID = "102"
	}

	log.Printf("KSC клиент инициализирован. Задача ID: %s", taskID)

	return &KSCClient{
		client: client,
		ctx:    ctx,
		taskID: taskID,
	}, nil
}

func connectRabbitMQ() (*amqp.Connection, error) {
	host := getEnv("RABBITMQ_HOST", "rabbitmq")
	port := getEnv("RABBITMQ_PORT", "5672")
	user := getEnv("RABBITMQ_USER", "admin")
	password := getEnv("RABBITMQ_PASSWORD", getEnv("RABBITMQ_PASS", "admin"))
	vhost := getEnv("RABBITMQ_VHOST", "/")

	url := fmt.Sprintf("amqp://%s:%s@%s:%s%s", user, password, host, port, vhost)

	var conn *amqp.Connection
	var err error
	maxRetries := 5
	retryDelay := 5 * time.Second

	for i := 0; i < maxRetries; i++ {
		conn, err = amqp.Dial(url)
		if err == nil {
			log.Println("Подключение к RabbitMQ")
			return conn, nil
		}
		log.Printf("Ошибка подключения к RabbitMQ (попытка %d/%d): %v.", i+1, maxRetries, err)
		time.Sleep(retryDelay)
	}

	return nil, fmt.Errorf("Ошибка подключения к RabbitMQ после %d попыток: %w", maxRetries, err)
}

func handleMessage(ch *amqp.Channel, msg amqp.Delivery, kscClient *KSCClient) {
	var request FileCheckRequest

	if err := json.Unmarshal(msg.Body, &request); err != nil {
		log.Printf("Ошибка десериализации: %v", err)
		msg.Nack(false, false)
		return
	}

	log.Printf("Отпрака запроса: ID=%s, FileID=%d, Path=%s",
		request.ID, request.Payload.FileID, request.Payload.FilePath)

	response := processFileCheck(request, kscClient)

	if err := sendResponse(ch, request.CallbackQueue, request.CorrelationID, response); err != nil {
		log.Printf("Error sending response: %v", err)
		msg.Nack(false, true)
		return
	}

	msg.Ack(false)
	log.Printf("Файл проверен: FileID=%d, IsSafe=%v",
		request.Payload.FileID, response.Result.IsSafe)
}

func processFileCheck(request FileCheckRequest, kscClient *KSCClient) FileCheckResponse {
	response := FileCheckResponse{
		Payload: FileCheckResponsePayload{
			FileID:   request.Payload.FileID,
			FilePath: request.Payload.FilePath,
		},
	}

	isSafe, threatName, err := runKSCScan(kscClient)
	if err != nil {
		errorMsg := fmt.Sprintf("Ошибка запуска KSC задачи: %v", err)
		response.Error = &errorMsg
		return response
	}

	response.Result = FileCheckResult{
		IsSafe:     isSafe,
		ThreatName: threatName,
	}

	return response
}

func waitForTaskCompletion(kscClient *KSCClient, maxWaitTime time.Duration, checkInterval time.Duration) (*TaskStatistics, error) {
	startTime := time.Now()

	for {
		if time.Since(startTime) > maxWaitTime {
			return nil, fmt.Errorf("timeout процесса задачи %v", maxWaitTime)
		}

		_, bytes, err := kscClient.client.Tasks.GetTaskStatistics(kscClient.ctx, kscClient.taskID)
		if err != nil {
			log.Printf("Ошибка получения статистики: %v", err)
			time.Sleep(checkInterval)
			continue
		}

		var stats TaskStatistics
		if err := json.Unmarshal(bytes, &stats); err != nil {
			log.Printf("Ошибка парсинга статистики: %v, raw: %s", err, string(bytes))
			time.Sleep(checkInterval)
			continue
		}

		if stats.PxgRetVal.Status1 != 0 {
			log.Printf("Задача ещё не распространена на хосты (Status1=%d), ожидание...", stats.PxgRetVal.Status1)
			time.Sleep(checkInterval)
			continue
		}

		if stats.PxgRetVal.Status4 == 0 && stats.PxgRetVal.Status16 == 0 {
			log.Printf("Задача распространена, но ещё не завершена (Status4=%d, Status16=%d), ожидание...",
				stats.PxgRetVal.Status4, stats.PxgRetVal.Status16)
			time.Sleep(checkInterval)
			continue
		}

		// Задача завершена, возвращаем финальную статистику
		log.Printf("Задача завершена. Статистика: Status4=%d (успешно), Status16=%d (неуспешно), Завершено=%d%%",
			stats.PxgRetVal.Status4, stats.PxgRetVal.Status16, stats.PxgRetVal.GNRLCompletedPercent)
		return &stats, nil
	}
}

func runKSCScan(kscClient *KSCClient) (bool, *string, error) {
	log.Printf("Запуск KSC задачи (ID: %s)", kscClient.taskID)

	result, err := kscClient.client.Tasks.RunTask(kscClient.ctx, kscClient.taskID)
	if err != nil {
		return false, nil, fmt.Errorf("Ошибка запуска KSC задачи: %w", err)
	}

	log.Printf("KSC задача запущена, результат: %s", string(result))

	maxWaitTime := 10 * time.Minute
	checkInterval := 5 * time.Second

	log.Printf("Ожидание завершения задачи проверки...")
	finalStats, err := waitForTaskCompletion(kscClient, maxWaitTime, checkInterval)
	if err != nil {
		return false, nil, fmt.Errorf("Ошибка выполнения задачи (timeout): %w", err)
	}

	log.Printf("Финальная статистика задачи:")
	log.Printf("  - Успешно завершено: %d", finalStats.PxgRetVal.Status4)
	log.Printf("  - Неуспешно завершено: %d", finalStats.PxgRetVal.Status16)
	log.Printf("  - Процент выполнения: %d%%", finalStats.PxgRetVal.GNRLCompletedPercent)
	log.Printf("  - Полная статистика: %+v", finalStats)

	isSafe := finalStats.PxgRetVal.Status16 == 0 && finalStats.PxgRetVal.Status4 > 0
	var threatName *string = nil

	if !isSafe && finalStats.PxgRetVal.Status16 > 0 {
		threatMsg := fmt.Sprintf("Обнаружены угрозы (неуспешных проверок: %d)", finalStats.PxgRetVal.Status16)
		threatName = &threatMsg
	}

	return isSafe, threatName, nil
}

func sendResponse(ch *amqp.Channel, queue string, correlationID string, response FileCheckResponse) error {
	body, err := json.Marshal(response)
	if err != nil {
		return fmt.Errorf("Ошибка сериализации ответа: %w", err)
	}

	err = ch.Publish(
		"",
		queue,
		false,
		false,
		amqp.Publishing{
			ContentType:   "application/json",
			CorrelationId: correlationID,
			Body:          body,
			DeliveryMode:  amqp.Persistent,
			Timestamp:     time.Now(),
		},
	)

	if err != nil {
		return fmt.Errorf("Ошибка публикации ответа: %w", err)
	}

	log.Printf("Ответ отправлен в очередь: %s, CorrelationID: %s", queue, correlationID)
	return nil
}

func getEnv(key, defaultValue string) string {
	if value := os.Getenv(key); value != "" {
		return value
	}
	return defaultValue
}
