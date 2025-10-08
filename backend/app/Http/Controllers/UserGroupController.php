<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserGroupRequest;
use App\Http\Resources\UserGroupResource;
use App\Models\UserGroup;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Log;
use App\Repositories\UserGroupsRepository;
use App\Services\UserGroupService;

class UserGroupController extends Controller
{

    protected readonly UserGroupsRepository $userGroupsRepository;
    protected  UserGroupService $userGroupService;

    public function __construct(UserGroupsRepository $userGroupsRepository, UserGroupService $userGroupService)
    {
        $this->userGroupsRepository = $userGroupsRepository;
        $this->userGroupsRepository->model = 'App\Models\UserGroup';
        $this->userGroupService = $userGroupService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index() : ResourceCollection
    {
        // $userGroup = UserGroup::all();
        $userGroup = $this->userGroupsRepository->getAll();

        return UserGroupResource::collection($userGroup);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserGroupRequest $request) : UserGroupResource
    {
        $validated = $request->validated();
        $userGroup = $this->userGroupService->save($validated);

        return new UserGroupResource($userGroup);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id) : UserGroupResource
    {
        $userGroup = $this->userGroupsRepository->getOne($id);

        return new UserGroupResource($userGroup);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreUserGroupRequest $request, string $id) : UserGroupResource
    {
        $validated = $request->validated();
        $userGroup = $this->userGroupsRepository->getOne($id);
        $userGroupUpdated = $this->userGroupService->update($validated, $userGroup);

        return new UserGroupResource($userGroupUpdated);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id) : UserGroupResource
    {
        $userGroup = $this->userGroupsRepository->getOne($id);
        $userGroupDeleted = $this->userGroupService->delete($userGroup);

        return new UserGroupResource($userGroupDeleted);
    }
}
