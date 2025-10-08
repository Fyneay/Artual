<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckLdapConnection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ldap:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check LDAP connection';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $ldapHost = env('LDAP_HOST') ?? 'ldap://openldap';
        $ldapPort = env('LDAP_PORT') ?? 389;

        $connect = ldap_connect($ldapHost, $ldapPort);

        if ($connect) {
            ldap_set_option($connect, LDAP_OPT_PROTOCOL_VERSION,3);

            $bind = ldap_bind($connect, env('LDAP_ADMIN_USERNAME') ?? "cn=admin,dc=example,dc=com",env('LDAP_ADMIN_PASSWORD') ?? "admin");
            if ($bind) {
               return $this->info('connection successfully');
            } else {
                $this->error('wrong dn');
            }
        }
        return $this->info('not connection');

    }
}
