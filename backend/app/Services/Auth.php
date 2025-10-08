<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
//use App\Contracts\Auth;
use App\Classes\AuthContext;

class Auth
{
    private $authContext;

    public function __construct(AuthContext $authContext)
    {
        $this->authContext = $authContext;
    }
    /**
     * @throws ValidationException
     */
    public function login($data) : array
    {
        return $this->authContext->login($data);
    }

    public function register($data) : array
    {
        return $this->authContext->register($data);
    }

    public function logout($data) : void
    {
        $this->authContext->logout($data);
    }
}
