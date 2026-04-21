<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AccessResource extends JsonResource
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
            'granted_by' => $this->whenLoaded('grantedTo', function () {
                return [
                    'id' => $this->grantedTo->id,
                    'nickname' => $this->grantedTo->nickname,
                    'email' => $this->grantedTo->email,
                ];
            }),
            'article' => $this->whenLoaded('article', function () {
                return [
                    'id' => $this->article->id,
                    'name' => $this->article->name,
                ];
            }),
            'article_id' => $this->article_id,
            'access_date' => $this->access_date,
            'close_date' => $this->close_date,
            'reason' => $this->reason,
            'status' => $this->whenLoaded('status', function () {
                return [
                    'id' => $this->status->id,
                    'name' => $this->status->name,
                ];
            }),
            'status_id' => $this->status_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

