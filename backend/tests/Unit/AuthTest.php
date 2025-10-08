<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Classes\DatabaseAuth;
use App\Classes\Ldap\LdapAuth;
use App\Classes\Ldap\LdapGateway;
use App\Services\LdapSearch;
use App\Repositories\UserRepository;
use App\Services\Auth;
use App\Classes\AuthContext;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use App\Exceptions\LdapException;

use Mockery;

class AuthTest extends TestCase
{
    /**
     * A basic unit test example.
     */

    protected DatabaseAuth $databaseAuth;
    protected LdapAuth $ldapAuth;
    protected $ldapSearch;
    protected $userRepository;
    protected $authContext;
    protected $auth;

    public function setUp(): void
    {
        parent::setUp();
        //Создание сервиса LDAP и моков для сервисов LDAP
        $this->ldapSearch = Mockery::mock(LdapSearch::class);
        $this->userRepository = Mockery::mock(UserRepository::class);
        $this->ldapAuth = new LdapAuth($this->ldapSearch, $this->userRepository);
        //Создание сервиса DatabaseAuth и контекста авторизации
        $this->databaseAuth = new DatabaseAuth($this->userRepository);
        $this->authContext = new AuthContext($this->databaseAuth);
        $this->auth = new Auth($this->authContext);
    }

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_correct_login_database_auth(): void
    {
        $credentials = ['email' => 'test@test.com', 'password' => 'password'];

        // $this->seed(UsersGroupsTableSeeder::class);

        $user = Mockery::mock(User::class)->makePartial();
        $user->id = 1;
        $user->nickname = 'test';
        $user->email = $credentials['email'];
        $user->password = Hash::make($credentials['password']);
        $user->role_id = 20000;
        $user->shouldReceive('createToken')->once()->andReturn((object)['plainTextToken' => 'test-token']);

        $this->userRepository->shouldReceive('findOneByEmail')->once()->with($credentials['email'])->andReturn($user);
        $result = $this->auth->login($credentials);
        $this->assertEquals('test-token', $result['token']);
    }

    public function test_incorrect_login_database_auth(): void
    {
        //Человека не существует в базе данных
        $credentials = ['email' => 'test@test.com', 'password' => 'password'];
        $this->userRepository->shouldReceive('findOneByEmail')->once()->with($credentials['email'])->andReturn(null);
        $this->expectException(ValidationException::class);
        $this->auth->login($credentials);
    }

    public function test_correct_register_database_auth(): void
    {
        $credentials = ['email' => 'test@test.com', 'password' => 'password', 'nickname' => 'test', 'role_id' => 20000];

        $user = Mockery::mock(User::class)->makePartial();
        $user->shouldReceive('createToken')->once()->andReturn((object)['plainTextToken' => 'test-token']);

        $this->userRepository
            ->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function ($data) use ($credentials) {
                return $data['nickname'] === $credentials['nickname']
                    && $data['email'] === $credentials['email']
                    && $data['role_id'] === $credentials['role_id']
                    && is_string($data['password'])
                    && Hash::check($credentials['password'], $data['password']);
            }))
            ->andReturn($user);

        $result = $this->auth->register($credentials);
        $this->assertEquals('test-token', $result['token']);
    }

    public function test_correct_logout_database_auth(): void
    {
        $credentials = ['email' => 'test@test.com', 'password' => 'password', 'nickname' => 'test', 'role_id' => 20000];
        $user = Mockery::mock(User::class)->makePartial();

        $user->id = 1;
        $user->nickname = $credentials['nickname'];
        $user->email = $credentials['email'];
        $user->role_id = $credentials['role_id'];
        $user->password = Hash::make($credentials['password']);


        $user
        ->shouldReceive('createToken')
        ->once()
        ->andReturn((object)['plainTextToken' => 'test-token']);

        $this->userRepository
        ->shouldReceive('findOneByEmail')
        ->once()
        ->with($credentials['email'])
        ->andReturn($user);
        $this->auth->login($credentials);
        $this->auth->logout($user);
        $this->assertDatabaseMissing('personal_access_tokens', [
            'name' => $credentials['email'],
        ]);
    }


    public function test_correct_login_ldap_auth(): void
    {
        $this->authContext->setStrategy($this->ldapAuth);
        $login = [
            'nickname' => 'test',
            'password' => 'password'
        ];

        $ldapUser = [
            'count' => 1,
            'nickname' => $login['nickname'],
            'password' => $login['password'],
            'gidNumber' => 20000
        ];

        $user = Mockery::mock(User::class)->makePartial();
        $user->id = 1;
        $user->nickname = $login['nickname'];
        $user->password = Hash::make($login['password']);
        $user->role_id = 20000;

        $user->shouldReceive('createToken')
        ->once()
        ->andReturn((object)['plainTextToken' => 'test-token']);

        $this->ldapSearch->shouldReceive('searchUser')
        ->once()
        ->with($login['nickname'])
        ->andReturn($ldapUser);

        $this->userRepository
        ->shouldReceive('findOneByNickname')
        ->once()
        ->with($login['nickname'])
        ->andReturn($user);
        $result=$this->auth->login($login);

        $this->assertEquals('test-token', $result['token']);
    }

    public function test_incorrect_login_ldap_auth(): void
    {
        $login = [
            'nickname' => 'test',
            'password' => 'password'
        ];

        $this->authContext->setStrategy($this->ldapAuth);

        
        $this->ldapSearch->shouldReceive('searchUser')
        ->once()
        ->with($login['nickname'])
        ->andThrow(new LdapException('User not found'));

        $this->expectException(LdapException::class);
        $this->expectExceptionMessage('Пользователь не найден в домене');
        
        $this->auth->login($login);
       
    }

    public function test_correct_register_ldap_auth(): void
    {
        $this->authContext->setStrategy($this->ldapAuth);

        $credentials = [
        'email' => 'test@test.com', 
        'password' => 'password', 
        'nickname' => 'test', 
        'role_id' => 20000,
        ];   

        $ldapUser = [
            'count' => 1,
            'nickname' => $credentials['nickname'],
            'password' => $credentials['password'],
            'gidNumber' => 20000
        ];

        $user = Mockery::mock(User::class)->makePartial();
        $user->id = 1;
        $user->nickname = $credentials['nickname'];
        $user->email = $credentials['email'];
        $user->password = Hash::make($credentials['password']);
        $user->role_id = 20000;

        $this->ldapSearch->shouldReceive('searchUser')
        ->once()
        ->with($credentials['nickname'])
        ->andReturn($ldapUser);

        //Для приватного метода checkUser
        $this->userRepository->shouldReceive('findOneByNickname')
        ->once()
        ->with($credentials['nickname'])
        ->andReturn(null);

        $this->userRepository->shouldReceive('create')
        ->once()
        ->with(Mockery::on(function ($data) use ($credentials) {
            return $data['nickname'] === $credentials['nickname']
                && $data['email'] === $credentials['email']
                && $data['role_id'] === $credentials['role_id']
                && is_string($data['password'])
                && Hash::check($credentials['password'], $data['password']);

        }))
        ->andReturn($user);

        $user->shouldReceive('createToken')
        ->once()
        ->andReturn((object)['plainTextToken' => 'test-token']);

        $result = $this->auth->register($credentials);
        $this->assertEquals('test-token', $result['token']);
    }

    public function test_incorrect_register_ldap_auth_with_password_wrong(): void
    {
        $this->authContext->setStrategy($this->ldapAuth);

        $credentials = [
        'email' => 'test@test.com', 
        'password' => 'password12', 
        'nickname' => 'test', 
        'role_id' => 20000];   

        $ldapUser = [
            'count' => 1,
            'nickname' => $credentials['nickname'],
            'password' => 'password',
            'gidNumber' => 20000
        ];

        $this->ldapSearch->shouldReceive('searchUser')
        ->once()
        ->with($credentials['nickname'])
        ->andReturn($ldapUser);


        $this->userRepository->shouldReceive('findOneByNickname')
        ->once()
        ->with($credentials['nickname'])
        ->andReturn(null);


        $this->expectException(LdapException::class);
        $this->expectExceptionMessage('Неверный пароль');

        $this->auth->register($credentials);
    }

    public function test_incorrect_register_ldap_without_user(): void
    {
        $this->authContext->setStrategy($this->ldapAuth);

        $credentials = [
        'email' => 'test@test.com', 
        'password' => 'password', 
        'nickname' => 'test', 
        'role_id' => 20000];   

        $this->ldapSearch->shouldReceive('searchUser')
        ->once()
        ->with($credentials['nickname'])
        ->andThrow(new LdapException('User not found'));
        
        $this->expectException(LdapException::class);
        $this->expectExceptionMessage('Пользователь не найден в домене');

        $this->auth->register($credentials);

    }
    public function test_incorrect_register_ldap_user_already_exists(): void
    {
        $this->authContext->setStrategy($this->ldapAuth);

        $credentials = [
        'email' => 'test@test.com', 
        'password' => 'password', 
        'nickname' => 'test', 
        'role_id' => 20000];   

        $ldapUser = [
            'count' => 1,
            'nickname' => $credentials['nickname'],
            'password' => $credentials['password'],
            'gidNumber' => 20000
        ];

        $user = Mockery::mock(User::class)->makePartial();
        $user->id = 1;
        $user->nickname = $credentials['nickname'];
        $user->email = $credentials['email'];
        $user->password = Hash::make($credentials['password']);
        $user->role_id = 20000;

        $this->ldapSearch->shouldReceive('searchUser')
        ->once()
        ->with($credentials['nickname'])
        ->andReturn($ldapUser);

        $this->userRepository->shouldReceive('findOneByNickname')
        ->once()
        ->with($credentials['nickname'])
        ->andReturn($user);
        
        $this->expectException(LdapException::class);
        $this->expectExceptionMessage('Пользователь уже зарегистрирован в каталоге');

        $this->auth->register($credentials);
    }

}
