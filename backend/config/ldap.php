<?php

return [
    'host' => env('LDAP_HOST', 'localhost'),
    'port' => env('LDAP_PORT', 389),
    'admin_dn' => env('LDAP_ADMIN_USERNAME', 'cn=admin,dc=example,dc=com'),
    'admin_password' => env('LDAP_ADMIN_PASSWORD', 'password'),
    'base_dn' => env('LDAP_BASE_DN', 'dc=example,dc=com'),
    'users_dn' => env('LDAP_USER_ORGANISATION_DN', 'ou=users,dc=example,dc=com'),
    'groups_dn' => env('LDAP_GROUPS_ORGANISATION_DN', 'ou=groups,dc=example,dc=com'),
];