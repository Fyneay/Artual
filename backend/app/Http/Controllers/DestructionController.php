<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDestructionRequest;
use App\Http\Requests\UpdateDestructionRequest;
use App\Http\Resources\DestructionResource;
use App\Repositories\DestructionRepository;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Auth;
use App\Services\DestructionDocumentService;
use App\Models\Status;
use App\Models\Article;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
class DestructionController extends Controller
{
    public function __construct(
        private DestructionRepository $destructionRepository,
        private DestructionDocumentService $documentService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(): ResourceCollection
    {
        $destructions = $this->destructionRepository->getAllWithRelations(['creator', 'articles']);

        return DestructionResource::collection($destructions);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDestructionRequest $request): DestructionResource
    {
        $validated = $request->validated();

        // Создаем акт уничтожения
        $destruction = $this->destructionRepository->create([
            'name' => $validated['name'],
            'created_by' => Auth::id(),
        ]);

        // Привязываем статьи
        if (isset($validated['article_ids'])) {
            $destruction->articles()->attach($validated['article_ids']);
        }

        // Загружаем отношения для ответа
        $destruction->load(['creator', 'articles']);

        return new DestructionResource($destruction);
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): DestructionResource
    {
        $destruction = $this->destructionRepository->getOneWithRelations($id, ['creator', 'articles']);

        return new DestructionResource($destruction);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDestructionRequest $request, int $id): DestructionResource
    {
        $destruction = $this->destructionRepository->getOne($id);
        $validated = $request->validated();

        if (isset($validated['name'])) {
            $destruction->update(['name' => $validated['name']]);
        }

        if (isset($validated['article_ids'])) {
            $destruction->articles()->sync($validated['article_ids']);
        }

        $destruction->load(['creator', 'articles']);

        return new DestructionResource($destruction);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): DestructionResource
    {
        $destruction = $this->destructionRepository->getOne($id);

        $destruction->articles()->detach();

        // Удаляем акт уничтожения
        $destruction->delete();

        return new DestructionResource($destruction);
    }

    public function downloadAct(int $id): StreamedResponse
    {
        $destruction = $this->destructionRepository->getOneWithRelations($id, ['creator', 'articles']);

        return $this->documentService->generateDestructionAct($destruction);
    }

    /**
     * Уничтожить статьи, связанные с актом уничтожения
     */
    public function destroyArticles(int $id): JsonResponse
    {
        $destruction = $this->destructionRepository->getOneWithRelations($id, ['articles']);

        $destroyedStatus = Status::where('name', 'уничтожен')->first();

        if (!$destroyedStatus) {
            return response()->json([
                'error' => 'Статус "уничтожен" не найден в базе данных'
            ], 404);
        }

        $articleIds = $destruction->articles->pluck('id')->toArray();
        Article::whereIn('id', $articleIds)->update(['status_id' => $destroyedStatus->id]);

        $destruction->load(['creator', 'articles']);

        return response()->json([
            'message' => 'Статус статей успешно обновлен на "уничтожен"',
            'data' => new DestructionResource($destruction)
        ], 200);
    }
}
