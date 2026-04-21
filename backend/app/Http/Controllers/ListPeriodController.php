<?php

namespace App\Http\Controllers;

use App\Http\Resources\ListPeriodResource;
use App\Repositories\ListPeriodRepository;
use App\Services\ListPeriodService;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ListPeriodController extends Controller
{
    protected readonly ListPeriodRepository $listPeriodRepository;
    protected ListPeriodService $listPeriodService;

    public function __construct(ListPeriodRepository $listPeriodRepository, ListPeriodService $listPeriodService)
    {
        $this->listPeriodRepository = $listPeriodRepository;
        $this->listPeriodService = $listPeriodService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): ResourceCollection
    {
        $listPeriods = $this->listPeriodRepository->getAll();

        return ListPeriodResource::collection($listPeriods);
    }
}
