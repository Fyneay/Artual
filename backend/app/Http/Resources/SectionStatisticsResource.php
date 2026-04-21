<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SectionStatisticsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = is_array($this->resource) ? $this->resource : (array) $this->resource;

        return [
            'total_documents' => $data['total_documents'] ?? 0,
            'total_accesses' => $data['total_accesses'] ?? 0,
            'total_weight' => $data['total_weight'] ?? 0, // в байтах
        ];
    }
}
