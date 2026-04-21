<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\ArticleFileResource;

class ArticleResource extends JsonResource
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
            'user_id'=> $this->user_id,
       //     'path-file' => $this->{'path-file'},
            'section_id'=>$this->section_id,
            'created_by' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'nickname' => $this->user->nickname,
                    'email' => $this->user->email,
                ];
            }),
            'files' => $this->whenLoaded('files', function () {
                return ArticleFileResource::collection($this->files);
            }),
            'list_period_id' => $this->list_period_id,
            'list_period' => $this->whenLoaded('listPeriod', function () {
                return [
                    'id' => $this->listPeriod->id,
                    'name' => $this->listPeriod->name,
                    'retention_period' => $this->listPeriod->retention_period,
                ];
            }),
            'expiration_date' => $this->expiration_date?->toDateString(),
            'secrecy_grade' => $this->secrecy_grade,
            'location' => $this->location,
            'type_document_id' => $this->type_document_id,
            'type_document' => $this->whenLoaded('typeDocument', function () {
                return [
                    'id' => $this->typeDocument->id,
                    'name' => $this->typeDocument->name,
                ];
            }),
            'description' => $this->description,
            'status_id' => $this->status_id,
            'status' => $this->whenLoaded('status', function () {
                return [
                    'id' => $this->status->id,
                    'name' => $this->status->name,
                ];
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
