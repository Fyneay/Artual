<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSectionRequest;
use App\Http\Resources\SectionResource;
use App\Models\Section;
use App\Repositories\SectionRepository;
use App\Services\SectionService;
use Illuminate\Http\Resources\Json\ResourceCollection;

class SectionController extends Controller
{
    protected readonly SectionRepository $sectionRepository;
    protected SectionService $sectionService;

    public function __construct(SectionRepository $sectionRepository, SectionService $sectionService)
    {
        $this->sectionRepository = $sectionRepository;
        $this->sectionService = $sectionService;
    }


    /**
     * Display a listing of the resource.
     */
    public function index() : ResourceCollection
    {
        $sections = $this->sectionRepository->getAll();
        return SectionResource::collection($sections);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSectionRequest $request)
    {
        $validated=$request->validated();
        $section = $this->sectionService->save($validated);
        return new SectionResource($section);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id) : SectionResource
    {
        $section = $this->sectionRepository->getOne($id);
        return new SectionResource($section);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreSectionRequest $request, string $id) : SectionResource
    {
        $section = $this->sectionRepository->getOne($id);
        $validated=$request->validated();
        $sectionUpdated = $this->sectionService->update($validated, $section);
        return new SectionResource($sectionUpdated);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id) : SectionResource
    {
        $section = $this->sectionRepository->getOne($id);
        $sectionDeleted= $this->sectionService->delete($section);
        return new SectionResource($sectionDeleted);
    }
}
