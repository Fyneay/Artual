<?php

namespace App\Services;

use App\Classes\UuidGenerator;
use App\Values\Invites\InviteDTO;
use App\Repositories\Contracts\CacheRepositoryInterface;
use Generator;

class UuidInviteService
{
    private CacheRepositoryInterface $cacheRepository;

    public function __construct(CacheRepositoryInterface $cacheRepository)
    {
        $this->cacheRepository = $cacheRepository;
    }
    
    public function generate() : string
    {
        return UuidGenerator::generate();
    }

    public function create(string $key,InviteDTO $inviteDTO, ?int $ttl=3600) : bool
    {
        if (!$this->checkExists($key)) {
            return $this->cacheRepository->set($key, $inviteDTO->toJson(),$ttl);
        }
        return false;
    }

    public function delete(string $key) : bool
    {  

        if ($this->checkUsed($key) || $this->checkDate($key)) {
            return $this->cacheRepository->delete($key);
        }
        return false;
    }

    public function checkDate(string $key) : bool
    {
        $data = $this->cacheRepository->get($key);
        //$inviteDTO = InviteDTO::fromJson($data);
        //return $inviteDTO->getKey('expires_at') < time();
        return $data['expires_at'] > now()->format('Y-m-d H:i:s');
    }

    public function checkUsed(string $key) : bool
    {
        $data = $this->cacheRepository->get($key);
        //$inviteDTO = InviteDTO::fromJson($data);
        //return $inviteDTO->getKey('used') > 0;
        return $data['used'];
    }

    public function checkExists(string $key) : bool
    {
        return $this->cacheRepository->exists($key);
    }

    public function checkTtl(string $key, int $ttl) : bool
    {
        return $this->cacheRepository->checkTtl($key, $ttl);
    }

    public function getAll() : mixed
    {
        foreach ($this->cacheRepository->scan('*') as $key) {
            yield $key => $this->get($key);
        }
    }

    public function get(string $key) : mixed
    {
        return $this->cacheRepository->get($key);
    }
}