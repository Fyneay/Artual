<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Classes\Ldap\LdapAuth;
use App\Services\LdapSearch;
use App\Repositories\UserRepository;
use App\Contracts\Auth;
use Illuminate\Contracts\Foundation\Application;
use App\Classes\DatabaseAuth;
use App\Services\UuidInviteService;
use App\Repositories\Contracts\CacheRepositoryInterface;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(LdapAuth::class, function (Application $app) {
            return new LdapAuth($app->make(LdapSearch::class), $app->make(UserRepository::class));
        });
        $this->app->bind(DatabaseAuth::class, function (Application $app) {
            return new DatabaseAuth($app->make(UserRepository::class));
        });

        // Сервис для работы с инвайт-ссылками
        $this->app->bind(UuidInviteService::class, function (Application $app) {
            return new UuidInviteService($app->make(CacheRepositoryInterface::class));
        });


        $this->app->bind(Auth::class, function (Application $app) {
            $driver = config('auth.driver', 'database');
            return match($driver) {
                'ldap' => $app->make(LdapAuth::class),
                'database' => $app->make(DatabaseAuth::class),
                default => throw new \Exception('Не определён драйвер аутентификации'),
            };
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
