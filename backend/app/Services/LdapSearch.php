<?php

namespace App\Services;

use App\Classes\Ldap\LdapGateway;
use App\Exceptions\LdapException;
class LdapSearch
{
    private LdapGateway $ldapGateway;

    public function __construct(LdapGateway $ldapGateway)
    {
        $this->ldapGateway = $ldapGateway;
    }

    public function searchUser(string $data): array | LdapException
    {
        $user = $this->ldapGateway->search(config('ldap.users_dn'), 'uid='.$data);
        if($user['count'] > 0) {
            return $user[0];
        }
        throw new LdapException('User not found');
    }

    public function searchGroup(string $data): array | LdapException
    {
        $group = $this->ldapGateway->search(config('ldap.groups_dn'), 'cn='.$data);
        if($group['count'] > 0) {
            return $group[0];
        }
        throw new LdapException('Group not found');
    }

    public function searchGroupsUser(string $data): array | LdapException
    {
        $groups = $this->ldapGateway->search(config('ldap.users_dn'), 'gidNumber='.$data);
        if($groups['count'] > 0) {
            return $groups[0];
        }
        throw new LdapException('Groups not found');
    }
}