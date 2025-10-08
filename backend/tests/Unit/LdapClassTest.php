<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Classes\Ldap\LdapGateway;
use Illuminate\Support\Facades\Config;
class LdapClassTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    protected LdapGateway $ldapGateway;
    protected array $config;


    public function setUp(): void
    {
        parent::setUp();
        $this->config = Config::get('ldap');
        $this->ldapGateway = new LdapGateway();
    }
     public function test_correct_get_one_user_ldap(): void
    {
        $user = $this->ldapGateway->search($this->config['users_dn'], 'uid=borodulin');
        $this->assertArrayHasKey('uid', $user[0]);
    }

    public function test_correct_get_group_ldap(): void
    {
        $group = $this->ldapGateway->search($this->config['groups_dn'], 'cn=users');
        $this->assertArrayHasKey('cn', $group[0]);
    }

    public function test_correct_get_groups_user_ldap(): void
    {
        $groups = $this->ldapGateway->search($this->config['users_dn'], 'gidNumber=20000');
        $this->assertArrayHasKey('cn', $groups[0]);
    }

    public function test_incorrect_get_one_user_ldap(): void
    {
        $user = $this->ldapGateway->search($this->config['users_dn'], 'uid=borodulin1432');
        $this->assertEquals(['count' => 0], $user);
    }

    public function test_incorrect_get_group_ldap(): void
    {
        $group = $this->ldapGateway->search($this->config['groups_dn'], 'cn=test');
        $this->assertEquals(['count' => 0], $group);
    }

    public function test_incorrect_get_groups_user_ldap(): void
    {
        $groups = $this->ldapGateway->search($this->config['users_dn'], 'gidNumber=20001');
        $this->assertEquals(['count' => 0], $groups);
    }
}
