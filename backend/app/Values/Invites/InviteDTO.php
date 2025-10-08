<?php

namespace App\Values\Invites;

use DateTimeImmutable;



final class InviteDTO
{
    public readonly string $key;
    public readonly string $email;

    public readonly int $created_by;
    public readonly DateTimeImmutable $created_at;
    public readonly DateTimeImmutable $expires_at;
    public readonly int $user_id;
    public readonly string $user_role_id;
    public int $used;

    public function __construct(
        string $key,
        string $email,
        string $created_at,
        string $expires_at,
        int $user_id,
        string $user_role_id,
        int $used
    ) {
        $this->key = $key;
        $this->email = $email;
        $this->created_at = new DateTimeImmutable($created_at);
        $this->expires_at = new DateTimeImmutable($expires_at);
        $this->user_id = $user_id;
        $this->user_role_id = $user_role_id;
        $this->used = $used;
    }

    public static function make(string $key, string $email, string $created_at, string $expires_at, int $user_id, string $user_role_id, int $used) : self
    {
        return new self($key, $email, $created_at, $expires_at, $user_id, $user_role_id, $used);
    }



    public function toArray() : array
    {
        return [
            'key' => $this->key,
            'email' => $this->email,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'expires_at' => $this->expires_at->format('Y-m-d H:i:s'),
            'user_id' => $this->user_id,
            'user_role_id' => $this->user_role_id,
            'used' => $this->used,
        ];
    }

    public function toJson() : string
    {
        return json_encode($this->toArray(), JSON_THROW_ON_ERROR);
    }

    public static function fromJson(string $json) : self
    {
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        return self::make($data['key'], $data['email'], $data['created_at'], $data['expires_at'], $data['user_id'], $data['user_role_id'], $data['used']);
    }

    public function getKey(string $key) : mixed
    {
        return match($key) {
            'key' => $this->key,
            'email' => $this->email,
            'created_at' => $this->created_at,
            'expires_at' => $this->expires_at,
            'user_id' => $this->user_id,
            'user_role_id' => $this->user_role_id,
            'used' => $this->used,
            default => null,
        };
    }
}
