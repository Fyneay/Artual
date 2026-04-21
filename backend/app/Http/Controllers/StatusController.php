<?php

namespace App\Http\Controllers;

use App\Http\Resources\StatusResource;
use App\Models\Status;
use Illuminate\Http\Resources\Json\ResourceCollection;

class StatusController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): ResourceCollection
    {
        $statuses = Status::all();
        
        return StatusResource::collection($statuses);
    }
}

