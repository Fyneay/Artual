<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExchangeRequest;
use App\Http\Requests\UpdateExchangeRequest;
use App\Http\Resources\ExchangeResource;
use App\Repositories\ExchangeRepository;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Auth;
use App\Services\ExchangeDocumentService;
use App\Models\Status;
use App\Models\Article;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExchangeController extends Controller
{
    public function __construct(
        private ExchangeRepository $exchangeRepository,
        private ExchangeDocumentService $documentService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(): ResourceCollection
    {
        $exchanges = $this->exchangeRepository->getAllWithRelations(['creator', 'articles']);

        return ExchangeResource::collection($exchanges);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreExchangeRequest $request): ExchangeResource
    {
        $validated = $request->validated();

        $exchange = $this->exchangeRepository->create([
            'name' => $validated['name'],
            'reason' => $validated['reason'] ?? null,
            'fund_name' => $validated['fund_name'] ?? null,
            'receiving_organization' => $validated['receiving_organization'] ?? null,
            'created_by' => Auth::id(),
        ]);

        if (isset($validated['article_ids'])) {
            $exchange->articles()->attach($validated['article_ids']);
        }

        $exchange->load(['creator', 'articles']);

        return new ExchangeResource($exchange);
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): ExchangeResource
    {
        $exchange = $this->exchangeRepository->getOneWithRelations($id, ['creator', 'articles']);

        return new ExchangeResource($exchange);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateExchangeRequest $request, int $id): ExchangeResource
    {
        $exchange = $this->exchangeRepository->getOne($id);
        $validated = $request->validated();

        $updateData = [];
        if (isset($validated['name'])) {
            $updateData['name'] = $validated['name'];
        }
        if (isset($validated['reason'])) {
            $updateData['reason'] = $validated['reason'];
        }
        if (isset($validated['fund_name'])) {
            $updateData['fund_name'] = $validated['fund_name'];
        }
        if (isset($validated['receiving_organization'])) {
            $updateData['receiving_organization'] = $validated['receiving_organization'];
        }

        if (!empty($updateData)) {
            $exchange->update($updateData);
        }

        // Обновляем список статей, если указано
        if (isset($validated['article_ids'])) {
            $exchange->articles()->sync($validated['article_ids']);
        }

        // Загружаем отношения для ответа
        $exchange->load(['creator', 'articles']);

        return new ExchangeResource($exchange);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): ExchangeResource
    {
        $exchange = $this->exchangeRepository->getOne($id);

        // Удаляем акт приема
        $exchange->delete();

        return new ExchangeResource($exchange);
    }

    /**
     * Скачать документ акта приема
     */
    public function downloadAct(int $id): StreamedResponse
    {
        $exchange = $this->exchangeRepository->getOneWithRelations($id, ['creator', 'articles']);

        return $this->documentService->generateExchangeAct($exchange);
    }

    /**
     * Передать статьи, связанные с актом приема-передачи
     */
    public function transferArticles(int $id): JsonResponse
    {
        $exchange = $this->exchangeRepository->getOneWithRelations($id, ['articles']);

        // Находим статус "передан"
        $transferredStatus = Status::where('name', 'передан')->first();

        if (!$transferredStatus) {
            return response()->json([
                'error' => 'Статус "передан" не найден в базе данных'
            ], 404);
        }

        $articleIds = $exchange->articles->pluck('id')->toArray();
        Article::whereIn('id', $articleIds)->update(['status_id' => $transferredStatus->id]);

        $exchange->load(['creator', 'articles']);

        return response()->json([
            'message' => 'Статус статей успешно обновлен на "передан"',
            'data' => new ExchangeResource($exchange)
        ], 200);
    }
}
