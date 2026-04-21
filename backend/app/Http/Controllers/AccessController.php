<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAccessRequest;
use App\Http\Requests\UpdateAccessRequest;
use App\Http\Resources\AccessResource;
use App\Repositories\AccessRepository;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Auth;

class AccessController extends Controller
{
    public function __construct(
        private AccessRepository $accessRepository
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(): ResourceCollection
    {
        $accesses = $this->accessRepository->getAllWithRelations([
            'creator',
            'grantedTo',
            'article',
            'status'
        ]);

        return AccessResource::collection($accesses);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAccessRequest $request): AccessResource
    {
        $validated = $request->validated();

        $validated['created_by'] = Auth::id();

        $access = $this->accessRepository->create($validated);

        $access->load(['creator', 'grantedTo', 'article', 'status']);

        return new AccessResource($access);
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): AccessResource
    {
        $access = $this->accessRepository->getOneWithRelations($id, [
            'creator',
            'grantedTo',
            'article',
            'status'
        ]);

        return new AccessResource($access);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAccessRequest $request, int $id): AccessResource
    {
        $access = $this->accessRepository->getOne($id);
        $validated = $request->validated();

        $access->update($validated);

        $access->load(['creator', 'grantedTo', 'article', 'status']);

        return new AccessResource($access);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): AccessResource
    {
        $access = $this->accessRepository->getOne($id);

        $access->delete();

        return new AccessResource($access);
    }

    /**
     * Получить допуски для конкретного дела
     */
    public function getByArticle(int $articleId): ResourceCollection
    {
        $accesses = $this->accessRepository->getByArticle($articleId);
        $accesses->load(['creator', 'grantedTo', 'article', 'status']);

        return AccessResource::collection($accesses);
    }

    /**
     * Получить допуски, предоставленные текущему пользователю
     */
    public function getMyAccesses(): ResourceCollection
    {
        $userId = Auth::id();

        $accesses = $this->accessRepository->getByGrantedTo($userId);
        $accesses->load(['creator', 'grantedTo', 'article', 'status']);

        return AccessResource::collection($accesses);
    }
}

