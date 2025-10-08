<?php

namespace App\Classes\Ldap;

use Illuminate\Support\Facades\Config;

class LdapGateway
{
    private $connection;

    private array $config;

    public function __construct()
    {
        $this->config = Config::get('ldap');
        $this->connect();
    }

    private function connect(): void
    {
        $this->connection = ldap_connect(
            $this->config['host'].':'.$this->config['port']
        );

        if ($this->connection) {
            ldap_set_option($this->connection, LDAP_OPT_PROTOCOL_VERSION, 3);
            ldap_bind(
                $this->connection,
                $this->config['admin_dn'],
                $this->config['admin_password'],
            );
        }
    }

    public function search(string $base, string $filter): array
    {
        $search = ldap_search($this->connection, $base, $filter);
        if($search) {
            $entries = ldap_get_entries($this->connection, $search);
            return $entries;
        }
        return [];
    }
}
