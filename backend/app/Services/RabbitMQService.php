<?php
namespace App\Services;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Channel\AMQPChannel;
use Illuminate\Support\Facades\Log;
use Exception;


#Pattern Singleton
class RabbitMQService
{
    private ?AMQPStreamConnection $connection=null;
    private ?AMQPChannel $channel=null;

    private array $config;

    public function __construct()
    {
        $this->config = [
            'host' => env('RABBITMQ_HOST', 'rabbitmq'),
            'port' => env('RABBITMQ_PORT', 5672),
            'user' => env('RABBITMQ_USER', 'admin'),
            'password' => env('RABBITMQ_PASSWORD', 'admin'),
            'vhost' => env('RABBITMQ_VHOST', '/'),
        ];
    }

    private function getConnection() : AMQPStreamConnection
    {
        if ($this->connection === null) {
            $this->connection = new AMQPStreamConnection(
                $this->config['host'],
            $this->config['port'],
            $this->config['user'],
            $this->config['password'],
            $this->config['vhost']);
        }
        return $this->connection;
    }

    private function getChannel() : AMQPChannel
    {
        if ($this->channel === null) {
            $this->channel = $this->getConnection()->channel();
        }
        return $this->channel;
    }

    public function publish(string $queue, array $data, bool $durable = true, string $routingKey = '', string $exchange = '') : void
    {
        try {
            $channel = $this->getChannel();
            $channel->queue_declare($queue,
            false,
            $durable,
            false,
            false);

            $message = new AMQPMessage(json_encode($data),
            [
                'delivery_mode' => 2, // persistent мод
                'content_type' => 'application/json',
                'timestamp' => time(),
        ]);
        $routingKey = $routingKey ?: $queue;
        $channel->basic_publish($message, $exchange, $routingKey);
        Log::info("Сообщение успешно отправлено в RabbitMQ: {$queue}", [
            'exchange' => $exchange ?: '(default)',
            'routing_key' => $routingKey,
            'queue' => $queue
        ]);
        } catch (Exception $e) {
            Log::error("Ошибка при публикации сообщения в RabbitMQ: {$e->getMessage()}");
        }
    }

    public function consume(string $queue, callable $callback, bool $durable = true): void
    {
        try {
            $channel = $this->getChannel();

            $channel->queue_declare(
                $queue,
                false,
                $durable,
                false,
                false
            );

            $channel->basic_qos(null, 1, null);

            $channel->basic_consume(
                $queue,
                '',
                false,
                false,
                false,
                false,
                function ($msg) use ($callback, $queue) {
                    try {
                        $data = json_decode($msg->body, true);
                        Log::info("Сообщение получено из очереди: {$queue}", ['data' => $data]);

                        $result = $callback($data, $msg);

                        $msg->ack();

                        Log::info("Сообщение успешно обработано: {$queue}");
                    } catch (\Exception $e) {
                        Log::error("Ошибка при обработке сообщения из очереди: {$queue}", [
                            'error' => $e->getMessage(),
                            'body' => $msg->body
                        ]);
                        $msg->nack(false, true);
                    }
                }
            );

            Log::info("Начало потребления очереди: {$queue}");

            while ($channel->callbacks) {
                try {
                    $channel->wait(null,false,1);
                } catch (\PhpAmqpLib\Exception\AMQPTimeoutException  $e) {
                    Log::error("Ошибка при ожидании сообщения: {$e->getMessage()}");
                    continue;
                }
            }
        } catch (\Exception $e) {
            Log::error("Ошибка при потреблении очереди: {$queue}", [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function request(string $requestQueue, string $responseQueue, array $data, int $timeout = 30): ?array
    {
        $channel = $this->getChannel();
        $correlationId = uniqid('req_', true);

        $channel->queue_declare($requestQueue, false, true, false, false);
        list($callbackQueue,,) = $channel->queue_declare($responseQueue, false, true, false, false);

        $response = null;
        $responseReceived = false;

        // Callback для получения ответа
        $callback = function ($msg) use ($correlationId, &$response, &$responseReceived) {
            if ($msg->get('correlation_id') == $correlationId) {
                $response = json_decode($msg->body, true);
                $responseReceived = true;
                $msg->ack();
            }
        };

        $channel->basic_consume($callbackQueue,
        '',
        false,
        false,
        false,
        false,
        $callback);

        $msg = new AMQPMessage(
            json_encode($data),
            [
                'correlation_id' => $correlationId,
                'reply_to' => $callbackQueue,
                'delivery_mode' => 2,
            ]
        );

        $channel->basic_publish($msg, '', $requestQueue);

        $startTime = time();
        while (!$responseReceived && (time() - $startTime) < $timeout) {
            $channel->wait(null, false, $timeout);
        }

        return $response;
    }

    public function close(): void
    {
        if ($this->channel !== null) {
            $this->channel->close();
            $this->channel = null;
        }

        if ($this->connection !== null) {
            $this->connection->close();
            $this->connection = null;
        }
    }

    public function __destruct()
    {
        $this->close();
    }

}
