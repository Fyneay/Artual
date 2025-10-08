<?php

namespace App\Http\Controllers;

use App\Models\TypeSection;
use App\Http\Requests\StoreTypeSectionRequest;
use App\Http\Resources\TypeSectionResource;
use App\Repositories\TypeSectionRepository;
use App\Services\TypeSectionService;
use Illuminate\Http\Resources\Json\ResourceCollection;

class TypeSectionController extends Controller
{

    protected readonly TypeSectionRepository $typeSectionRepository;
    protected TypeSectionService $typeSectionService;

    public function __construct(TypeSectionRepository $typeSectionRepository, TypeSectionService $typeSectionService)
    {
        $this->typeSectionRepository=$typeSectionRepository;
        $this->typeSectionService=$typeSectionService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index() : ResourceCollection
    {
        $typeSections = $this->typeSectionRepository->getAll();

        return TypeSectionResource::collection($typeSections);

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(storeTypeSectionRequest $request) : TypeSectionResource
    {
        $validated = $request->validated();
        $typeSection = $this->typeSectionService->save($validated);
        return new TypeSectionResource($typeSection);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id) : TypeSectionResource
    {
        $typeSection = $this->typeSectionRepository->getOne($id);
        return new TypeSectionResource($typeSection);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreTypeSectionRequest $request, string $id) : typeSectionResource
    {
        $validated = $request->validated();
        $typeSection = $this->typeSectionRepository->getOne($id);
        $typeSectionUpdated= $this->typeSectionService->update($validated,$typeSection);
        return new TypeSectionResource($typeSectionUpdated);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id) :typeSectionResource
    {
        $typeSection = $this->typeSectionRepository->getOne($id);
        $typeSectionDeleted = $this->typeSectionService->delete($typeSection);
        return new TypeSectionResource($typeSectionDeleted);
    }
}
