<?php

namespace App\Classes;

use Illuminate\Support\Str;

final class UuidGenerator
{
    public static function generate(): string
    {
        return (string) Str::uuid();
    }

    public static function isValid(string $uuid): bool
    {
        return Str::isUuid($uuid);
    }
}
