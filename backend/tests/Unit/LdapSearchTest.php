<?php

namespace Tests\Unit;

use App\Services\LdapSearch;
use App\Classes\Ldap\LdapGateway;
use App\Exceptions\LdapException;
use Tests\TestCase;
use Mockery;

class LdapSearchTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    private LdapSearch $ldapSearch;
    private $ldapGateway;

    public function setUp(): void
    {
        parent::setUp();
        $this->ldapGateway = Mockery::mock(LdapGateway::class);
        $this->ldapSearch = new LdapSearch($this->ldapGateway);
    }

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

   public function test_correct_search_user(): void
   {

    $expectedResult = [
        'count' => 1,
        '0' => ['uid' => 'test', 'cn' => 'Test', 'gidNumber' => '20000'],
    ];

    $this->ldapGateway
        ->shouldReceive('search')
        ->once()
        ->with(config('ldap.users_dn'), 'uid=test')
        ->andReturn([
            'count' => 1,
            '0' => $expectedResult,
        ]);

        $result = $this->ldapSearch->searchUser('test');
        $this->assertEquals($expectedResult, $result);
   }
   public function test_incorrect_search_user(): void
   {
    $this->ldapGateway
    ->shouldReceive('search')
    ->once()
    ->with(config('ldap.users_dn'), 'uid=test')
    ->andReturn([
        'count' => 0,
    ]);

    $this->expectException(LdapException::class);
    $this->ldapSearch->searchUser('test');
   }

   public function test_correct_search_group(): void
   {
    $expectedResult = [
        'count' => 1,
        '0' => ['cn' => 'Test', 'gidNumber' => '20000'],
    ];
    $this->ldapGateway
    ->shouldReceive('search')
    ->once()
    ->with(config('ldap.groups_dn'), 'cn=test')
    ->andReturn([
        'count' => 1,
        '0' => $expectedResult,
    ]);

    $result = $this->ldapSearch->searchGroup('test');
    $this->assertEquals($expectedResult, $result);
   }

   public function test_incorrect_search_group(): void
   {
    $this->ldapGateway
    ->shouldReceive('search')
    ->once()
    ->with(config('ldap.groups_dn'), 'cn=test')
    ->andReturn(
        [
            'count' => 0,
        ]
    );

    $this->expectException(LdapException::class);
    $this->ldapSearch->searchGroup('test');
   }

   public function test_correct_search_groups_user(): void
   {
    $expectedResult = [
        'count' => 2,
        '0' => ['gidNumber' => '20000'],
        '1' => ['gidNumber' => '20001'],
    ];

    $this->ldapGateway
    ->shouldReceive('search')
    ->once()
    ->with(config('ldap.users_dn'), 'gidNumber=20000')
    ->andReturn([
        'count' => 2,
        '0' => $expectedResult,
    ]);

    $result = $this->ldapSearch->searchGroupsUser('20000');
    $this->assertEquals($expectedResult, $result);
   }

   public function test_incorrect_search_groups_user(): void
   {
    $this->ldapGateway
    ->shouldReceive('search')
    ->once()
    ->with(config('ldap.users_dn'), 'gidNumber=20000')
    ->andReturn(
        [
            'count' => 0,
        ]
    );

    $this->expectException(LdapException::class);
    $this->ldapSearch->searchGroupsUser('20000');
   }
}
