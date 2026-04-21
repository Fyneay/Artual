<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\ArticleResource;

class ExchangeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'reason' => $this->reason,
            'fund_name' => $this->fund_name,
            'receiving_organization' => $this->receiving_organization,
            'created_by' => $this->created_by,
            'articles' => $this->whenLoaded('articles', function () {
                return ArticleResource::collection($this->articles);
            }),
            'articles_count' => $this->when(isset($this->articles_count), $this->articles_count),
            'creator' => $this->whenLoaded('creator', function () {
                return [
                    'id' => $this->creator->id,
                    'nickname' => $this->creator->nickname,
                    'email' => $this->creator->email,
                ];
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
