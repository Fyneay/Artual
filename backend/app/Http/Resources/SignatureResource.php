<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\ArticleResource;

class SignatureResource extends JsonResource
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
            'certificate_name' => $this->certificate_name,
            'certificate_subject' => $this->certificate_subject,
            'signature_hash' => $this->signature_hash,
            'signature_data' => $this->when(
                $request->query('include_signature') === 'true',
                $this->signature_data
            ),
            'signer' => $this->whenLoaded('signer', fn() => new UserResource($this->signer)),
            'articles' => $this->whenLoaded('articles', fn() => 
                ArticleResource::collection($this->articles)
            ),
            'signed_by' => $this->signed_by,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
