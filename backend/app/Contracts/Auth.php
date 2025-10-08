<?php

namespace App\Contracts;

interface Auth {
    public function login($data) :array;

    public function register($data) :array;
    public function logout($data) :void;
}