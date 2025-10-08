<?php

namespace Tests\Unit;

use Database\Seeders\UsersGroupsTableSeeder;
use Tests\TestCase;
use App\Classes\Ldap\LdapAuth as LdapAuthClass;
use App\Services\LdapSearch;
use App\Repositories\UserRepository;
use App\Classes\Ldap\LdapGateway;
use Mockery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\UserGroup;
use App\Exceptions\LdapException;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Exceptions;

class LdapAuthClassTest extends TestCase
{

    use RefreshDatabase, WithFaker;
    /**
     * A basic unit test example.
     */
    protected LdapAuthClass $ldapAuth;

    protected $ldapSearch;
    protected $userRepository;

    public function setUp(): void
    {
        parent::setUp();
        //Создание моков для сервисов
        $this->ldapSearch = Mockery::mock(LdapSearch::class);
        $this->userRepository = Mockery::mock(UserRepository::class);

        $this->ldapAuth = new LdapAuthClass($this->ldapSearch, $this->userRepository);
    }

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_correct_login(): void
    {
        $loginData = [
            'nickname' => 'test',
            'password' => 'password'
        ];

        $this->seed(UsersGroupsTableSeeder::class);

        $user = Mockery::mock(User::class)->makePartial();
        $user->id = 1;
        $user->nickname = $loginData['nickname'];
        $user->password = Hash::make($loginData['password']);
        $user->role_id = 20000;

        $user->shouldReceive('createToken')
            ->with($loginData['nickname'])
            ->once()
            ->andReturn((object)['plainTextToken' => 'test-token']);

        $ldapUser = [
            'count' => 1,
            'nickname' => $loginData['nickname'],
            'password' => $loginData['password'],
            'gidNumber' => 20000
        ];


        $this->ldapSearch->shouldReceive('searchUser')->once()->with($loginData['nickname'])->andReturn($ldapUser);

        $this->userRepository->shouldReceive('findOneByNickname')->once()->with($loginData['nickname'])->andReturn($user);

        $result = $this->ldapAuth->login($loginData);

        $this->assertArrayHasKey('user', $result);
        $this->assertArrayHasKey('token', $result);
        $this->assertEquals($user, $result['user']);
        $this->assertEquals('test-token', $result['token']);
    }

    public function test_incorrect_login_password(): void
    {
        $loginData = [
            'nickname' => 'test',
            'password' => null
        ];

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Неверный логин или пароль');

        $this->ldapAuth->login($loginData);
    }

    public function test_incorrect_login_nickname(): void
    {
        $loginData = [
            'nickname' => null,
            'password' => 'password'
        ];

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Неверный логин или пароль');

        $this->ldapAuth->login($loginData);
    }

    public function test_incorrect_login_without_ldap_user(): void
    {
        $loginData = [
            'nickname' => 'test',
            'password' => 'password'
        ];

        $this->ldapSearch
        ->shouldReceive('searchUser')
        ->once()
        ->with($loginData['nickname'])
        ->andThrow(new LdapException('Пользователь не найден в домене'));

        $this->expectException(LdapException::class);
        $this->expectExceptionMessage('Пользователь не найден в домене');

        $this->ldapAuth->login($loginData);
    }

    public function test_incorrect_login_password_with_ldap_user(): void
    {
        $loginData = [
            'nickname' => 'test',
            'password' => 'password'
        ];

        $this->ldapSearch->shouldReceive('searchUser')->once()->with($loginData['nickname'])->andReturn([
            'count' => 1,
            'nickname' => $loginData['nickname'],
            'password' => 'password12',
            'gidNumber' => 20000
        ]);

        $this->expectException(LdapException::class);
        $this->expectExceptionMessage('Неверный пароль');

        $this->ldapAuth->login($loginData);
    }

    public function test_correct_register(): void
    {
        $registerData = [
            'nickname' => 'test',
            'password' => 'password',
            'email' => 'test@test.com',
            'role_id' => 20000
        ];

        $this->seed(UsersGroupsTableSeeder::class);

        $this->ldapSearch->shouldReceive('searchUser')
        ->once()
        ->with($registerData['nickname'])
        ->andReturn([
            'count' => 1,
            'nickname' => $registerData['nickname'],
            'password' => $registerData['password'],
            'email' => 'test@test.com',
            'gidNumber' => 20000
        ]);

        $this->userRepository
        ->shouldReceive('findOneByNickname')
        ->once()
        ->with($registerData['nickname'])
        ->andReturn(null);

        $user = Mockery::mock(User::class)->makePartial();
        $user->id = 1;
        $user->nickname = $registerData['nickname'];
        $user->email = $registerData['email'];
        $user->password = Hash::make($registerData['password']);
        $user->role_id = 20000;

        $this->userRepository
        ->shouldReceive('create')
        ->once()
        ->with(Mockery::on(function ($data) use ($registerData) {
            return $data['nickname'] === $registerData['nickname']
                && $data['email'] === $registerData['email']
                && $data['role_id'] === $registerData['role_id']
                && is_string($data['password'])
                && Hash::check($registerData['password'], $data['password']);
        }))
        ->andReturn($user);

        $user->shouldReceive('createToken')
        ->once()
        ->andReturn((object)['plainTextToken' => 'test-token']);

        $result = $this->ldapAuth->register($registerData);
        
        $this->assertEquals('test-token', $result['token']);
    }
    
    public function test_incorrect_register_user_already_exists(): void
    {
        $registerData = [
            'nickname' => 'test',
            'password' => 'password'
        ];

        $user = Mockery::mock(User::class)->makePartial();
        $user->id = 1;
        $user->nickname = $registerData['nickname'];
        $user->password = Hash::make($registerData['password']);
        $user->email = 'test@test.com';
        $user->role_id = 20000;

        $this->ldapSearch
        ->shouldReceive('searchUser')
        ->once()
        ->with($registerData['nickname'])
        ->andReturn([
            'count' => 1,
            'nickname' => $user->nickname,
            'password' => $user->password,
            'email' => $user->email,
            'gidNumber' => 20000
        ]);

        $this->userRepository
        ->shouldReceive('findOneByNickname')
        ->once()
        ->with($registerData['nickname'])
        ->andReturn($user);

        $this->expectException(LdapException::class);
        $this->expectExceptionMessage('Пользователь уже зарегистрирован в каталоге');

        $this->ldapAuth->register($registerData);
    }

    public function test_incorrect_register_user_not_found_in_ldap(): void
    {
        $registerData = [
            'nickname' => 'test1',
            'password' => 'password',
            'email' => 'test@test.com',
            'role_id' => 20000
        ];

        $this->ldapSearch
        ->shouldReceive('searchUser')
        ->once()
        ->with($registerData['nickname'])
        ->andThrow(new LdapException('Пользователь не найден в домене'));

        $this->expectException(LdapException::class);
        $this->expectExceptionMessage('Пользователь не найден в домене');

        $this->ldapAuth->register($registerData);
    }

    public function test_incorrect_register_without_password(): void
    {
        $registerData = [
            'nickname' => 'test',
            'password' => null
        ];

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Неверный логин или пароль');

        $this->ldapAuth->register($registerData);
    }

    public function test_incorrect_register_without_nickname(): void
    {
        $registerData = [
            'nickname' => null,
            'password' => 'password'
        ];

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Неверный логин или пароль');

        $this->ldapAuth->register($registerData);
    }

    public function test_incorrect_register_with_wrong_password(): void
    {
        $registerData = [
            'nickname' => 'test',
            'password' => 'password'
        ];

        $this->ldapSearch->shouldReceive('searchUser')->once()->with($registerData['nickname'])->andReturn([
            'count' => 1,
            'nickname' => $registerData['nickname'],
            'password' => 'password12',
            'email' => 'test@test.com',
            'gidNumber' => 20000
        ]);

        $this->userRepository
        ->shouldReceive('findOneByNickname')
        ->once()
        ->with($registerData['nickname'])
        ->andReturn(null);


        $this->expectException(LdapException::class);
        $this->expectExceptionMessage('Неверный пароль');

        $this->ldapAuth->register($registerData);
    }

    public function test_correct_logout(): void
    {
     
        $this->seed(UsersGroupsTableSeeder::class);

        $user = User::factory()->create([
            'nickname' => 'test',
            'password' => Hash::make('password'),
            'email' => 'test@test.com',
            'role_id' => 20000
        ]);

        $token = $user->createToken('token-token')->plainTextToken;

        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'tokenable_type' => User::class,
        ]);

        $this->ldapAuth->logout($user);

        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
        ]);
    }


}
