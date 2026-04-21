<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSectionRequest;
use App\Http\Resources\SectionResource;
use App\Models\Section;
use App\Repositories\SectionRepository;
use App\Services\SectionService;
use App\Services\OpisDocumentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Models\TypeSection;
use Symfony\Component\HttpFoundation\StreamedResponse;
class SectionController extends Controller
{
    protected readonly SectionRepository $sectionRepository;
    protected SectionService $sectionService;
    protected OpisDocumentService $opisDocumentService;

    public function __construct(
        SectionRepository $sectionRepository,
        SectionService $sectionService,
        OpisDocumentService $opisDocumentService
    ) {
        $this->sectionRepository = $sectionRepository;
        $this->sectionService = $sectionService;
        $this->opisDocumentService = $opisDocumentService;
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
        $section = $this->sectionRepository->getOne(intval($id));
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

    public function getSectionsByType() : JsonResponse
    {
        $typeSections = TypeSection::with('sections')->get();
        $tree = $typeSections->map(function ($typeSection) {
            return [
                'id' => $typeSection->id,
                'name' => $typeSection->name,
                'type' => 'type',
                'children' => $typeSection->sections->map(function ($section) {
                    return [
                        'id' => $section->id,
                        'name' => $section->name,
                        'type' => 'section',
                        'type_id' => $section->type_id,
                    ];
                })->toArray(),
            ];
        });

        return response()->json(['data' => $tree]);
    }

    public function generateOpis(string $id): StreamedResponse
    {
        $section = $this->sectionRepository->getOne(intval($id));
        return $this->opisDocumentService->generateOpis($section);
    }
}
