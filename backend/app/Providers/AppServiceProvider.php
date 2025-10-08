<?php

namespace App\Providers;

use App\Classes\FileUpload;
use App\Classes\Ldap\LdapGateway;
use App\Services\LdapSearch;
use App\Services\LocalFileUploader;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use App\Repositories\UserRepository;
use App\Classes\Ldap\LdapAuth;
use App\Contracts\Auth;
use App\Repositories\Contracts\CacheRepositoryInterface;
use App\Repositories\RedisInviteRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */

    public function register(): void
    {
        $this->app->bind(CacheRepositoryInterface::class, function (Application $app) {
            return new RedisInviteRepository('invites');
        });

        $this->app->singleton(FileUpload::class, function (Application $app) {
            return new LocalFileUploader();
        });

        $this->app->singleton(LdapGateway::class, function (Application $app) {
            return new LdapGateway();
        });

        // Сервис для поиска пользователей и групп в LDAP с использованием LDAP
        $this->app->singleton(LdapSearch::class, function (Application $app) {
            return new LdapSearch($app->make(LdapGateway::class));
        });

        $this->app->singleton(UserRepository::class, function (Application $app) {
            return new UserRepository();
        });

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // $this->app['request']->server->set('HTTPS', true);
        // URL::forceScheme('https');
    }
}
