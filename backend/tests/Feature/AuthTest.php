<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\UsersGroupsTableSeeder;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AuthTest extends TestCase
{

    use RefreshDatabase;

    protected Auth $auth;

    public function setUp() : void
    {
        parent::setUp();
        $this->auth = new Auth();
        $this->seed(UsersGroupsTableSeeder::class);
        $data = User::factory()->make();
    }

    public function test_register_new_user_with_token_key()
    {
        $data =  User::factory()->make();
        $response = $this->auth->register($data);

        $this->assertDatabaseHas('users', [
            'email' => $data->email,
        ]);

        $this->assertArrayHasKey('token', $response);

        $this->assertEquals($data['email'], $response['user']->email);
        DB::table('personal_access_tokens')->truncate();
    }

    public function test_login_new_user_with_token()
    {
        $data = User::factory()->make();

        $this->auth->register($data);

        $credentials = [
            'email' => $data->email,
            'password' => $data->password,
        ];

        $response = $this->auth->login($credentials);

        $this->assertArrayHasKey('token', $response);

        $this->assertEquals($data['email'], $response['user']->email);

        DB::table('personal_access_tokens')->truncate();
    }

    public function test_logout_new_user_without_token(){
        $data = User::factory()->make();
        $credentials = [
            'email' => $data->email,
            'password' => $data->password,
        ];

        $fiber = new \Fiber(function () use($data) {
            $this->auth->logout($data);
            \Fiber::suspend();
            $this->auth->logout($data);
        });

       $this->auth->register($data);

       $fiber->start();

       $this->auth->login($credentials);

       $fiber->resume();

        $this->assertDatabaseMissing('personal_access_tokens', [
            'name' => $credentials['email'],
        ]);
    }
}
