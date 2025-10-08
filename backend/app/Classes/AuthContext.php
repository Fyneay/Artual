<?php
namespace App\Classes;

use App\Contracts\Auth;

//Pattern: Strategy

class AuthContext
{
    private $auth;

    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    public function setStrategy(Auth $auth)
    {
        $this->auth = $auth;
    }

    public function login(array $data) : array
    {
        return $this->auth->login($data);
    }

    public function register(array $data) : array
    {
        return $this->auth->register($data);
    }

    public function logout($data) : void
    {
        $this->auth->logout($data);
    }
}