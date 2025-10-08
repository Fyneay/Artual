<?php

namespace App\Exceptions;

use Exception;

class InvalidRedisValueException extends Exception
{
    public function __construct(string $message = 'Ошибка при работе с значениями Redis')
    {
        parent::__construct($message);
    }


    
}
