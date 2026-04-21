<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\ArticleResource;

class DestructionResource extends JsonResource
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
            'created_by' => $this->whenLoaded('creator', function () {
                return [
                    'id' => $this->creator->id,
                    'nickname' => $this->creator->nickname,
                    'email' => $this->creator->email,
                ];
            }),
            'articles' => $this->whenLoaded('articles', function () {
                return ArticleResource::collection($this->articles);
            }),
            'articles_count' => $this->when(isset($this->articles_count), $this->articles_count),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
