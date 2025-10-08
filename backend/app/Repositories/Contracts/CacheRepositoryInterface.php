<?php

namespace App\Repositories\Contracts;

use Generator;

interface CacheRepositoryInterface
{
    public function get(string $key) : mixed;
    public function set(string $key, mixed $value, ?int $ttl = null) : bool;

    public function checkTtl(string $key, int $ttl) : bool;
    public function delete(string $key) : bool;
    public function exists(string $key) : bool;
    public function clear() : bool;
    public function scan(string $pattern) : Generator;

}