<?php

namespace App\Http\Controllers;

use App\Http\Resources\TypeDocumentResource;
use App\Repositories\TypeDocumentRepository;
use App\Services\TypeDocumentService;
use Illuminate\Http\Resources\Json\ResourceCollection;

class TypeDocumentController extends Controller
{
    protected readonly TypeDocumentRepository $typeDocumentRepository;
    protected TypeDocumentService $typeDocumentService;

    public function __construct(TypeDocumentRepository $typeDocumentRepository, TypeDocumentService $typeDocumentService)
    {
        $this->typeDocumentRepository = $typeDocumentRepository;
        $this->typeDocumentService = $typeDocumentService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): ResourceCollection
    {
        $typeDocuments = $this->typeDocumentRepository->getAll();

        return TypeDocumentResource::collection($typeDocuments);
    }
}
