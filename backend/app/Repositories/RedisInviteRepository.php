<?php

namespace App\Repositories;

use App\Repositories\Contracts\CacheRepositoryInterface;
use Exception;
use Generator;
use Illuminate\Support\Facades\Redis;
use App\Exceptions\InvalidRedisValueException;
use Predis\Collection\Iterator\Keyspace;

class RedisInviteRepository implements CacheRepositoryInterface
{
    private $redis;
    

    public function __construct(string $connection)
    {
        $this->redis = Redis::connection($connection);
    }

    public function get(string $key) : mixed
    {
        $prefix = $this->redis->getOptions()->prefix->getPrefix();
        //Убирает префикс из ключа
        $cleanKey = str_replace($prefix, '', $key);
        try {
            //$value = !empty($this->redis->get($key)) ? $this->redis->get($key) : null;
            $value = $this->redis->get($cleanKey);
            //Не нужно декодировать значение, т.к. оно уже декодировано в InviteDTO
            // $decodedValue = json_decode($value, true);
            // if (json_last_error() === JSON_ERROR_NONE) {
            //     return $decodedValue;
            // }
            return $value;
        } catch (Exception $e) {
            throw new InvalidRedisValueException("Ошибка при получении значения {$key} из Redis");
        }
    }
    
    public function set(string $key, mixed $value, ?int $ttl = 3600) : bool
    {
        try {
            $jsonValue = is_string($value) ? $value : json_encode($value);
            return $this->redis->setex($key, $ttl, $jsonValue)->getPayload() === 'OK';
        } catch (Exception $e) {
            throw new InvalidRedisValueException("Ошибка при установке значения {$key} в Redis");
        }
    }
    
    public function delete(string $key) : bool
    {
        try {
            return $this->redis->del($key);
        } catch (Exception $e) {
            throw new InvalidRedisValueException("Ошибка при удалении значения {$key} из Redis");
        }
    }

    public function exists(string $key) : bool
    {
        try {
            return $this->redis->exists($key);
        } catch (Exception $e) {
            throw new InvalidRedisValueException("Ошибка при проверке существования значения {$key} в Redis");
        }
    }
    
    public function clear() : bool
    {
        try {
            return (bool) $this->redis->flushdb();
        } catch (Exception $e) {
            throw new InvalidRedisValueException("Ошибка при очистке Redis");
        }
    }

    public function checkTtl(string $key, int $ttl = 0) : bool
    {
        try {
            return $this->redis->ttl($key)>$ttl;
        } catch (Exception $e) {
            throw new InvalidRedisValueException("Ошибка при проверке TTL значения {$key} в Redis");
        }
    }

    public function scan(string $pattern = '*') : Generator
    {
        $iterator = new Keyspace($this->redis->client(), $pattern);
        foreach ($iterator as $key) {
            yield $key;
        }
    }
}