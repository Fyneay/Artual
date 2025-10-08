<?php

namespace Tests\Feature;

use App\Repositories\Contracts\CacheRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Services\UuidInviteService;
use App\Repositories\RedisInviteRepository;
use App\Values\Invites\InviteDTO;


class UuidInviteServiceTest extends TestCase
{
    /**
     * A basic feature test example.
     */

    public function setUp() : void
    {
        parent::setUp();
        $this->app->bind(CacheRepositoryInterface::class, function () {
            return new RedisInviteRepository('invites');
        });
        (bool) app(CacheRepositoryInterface::class)->clear();
    }

    public function tearDown() : void
    {
        (bool) app(CacheRepositoryInterface::class)->clear();
        parent::tearDown();
    }

    public function test_correct_create() : void
    {
        $email=fake()->email;
        $service = $this->app->make(UuidInviteService::class);
        $key = $service->generate();
        $inviteDTO = InviteDTO::make($email, '1', '2025-01-01', '2025-01-02', '1', '1', '0');
        $result = $service->create($key, $inviteDTO);
        $this->assertTrue($result);
        // $this->assertTrue($service->checkExists($inviteDTO->getKey('email')));
        // $this->assertFalse($service->checkUsed($inviteDTO->getKey('email')));
    }

    public function test_incorrect_create_exists() : void
    {
        $email=fake()->email;
        $service = $this->app->make(UuidInviteService::class);
        $key = $service->generate();
        $inviteDTO = InviteDTO::make($email, '1', '2025-01-01', '2025-01-02', '1', '1', '0');
        $result = $service->create($key, $inviteDTO);
        $result2 = $service->create($key, $inviteDTO);
        $this->assertTrue($result);
        $this->assertFalse($result2);
    }

    public function test_correct_delete() : void
    {
        $email=fake()->email;
        $service = $this->app->make(UuidInviteService::class);
        $key = $service->generate();
        $inviteDTO = InviteDTO::make($email, '1', '2025-01-01', '2025-01-02', '1', '1', '1');
        $service->create($key, $inviteDTO);
        $result = $service->delete($key);
        $this->assertTrue($result);
    }

    public function test_incorrect_delete() : void
    {
        $email=fake()->email;
        $service = $this->app->make(UuidInviteService::class);
        $key = $service->generate();
        $inviteDTO = InviteDTO::make($email, '1', '2025-01-01', '2025-01-02', '1', '1', '0');
        $service->create($key, $inviteDTO);
        $result = $service->delete($key);
        $this->assertFalse($result);
    }

}
