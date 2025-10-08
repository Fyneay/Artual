<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Classes\UuidGenerator;

class InviteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'key' => $this->key,
            'email' => $this->email,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'expires_at' => $this->expires_at->format('Y-m-d H:i:s'),
            'user_id' => $this->user_id,
            'user_role_id' => $this->user_role_id,
            'used' => (bool) $this->used,
        ];
    }

    public function with(Request $request): array
    {
        return [
            'meta' => [
                'request_id' => UuidGenerator::generate(),
                'generated_at' => now()->toIso8601String(),
                'invite_status' => $this->getInviteStatus(),
            ],
        ];
    }

    private function getInviteStatus(): string
    {
        if ($this->used) return 'used';
        if ($this->expires_at < now()) return 'expired';
        return 'active';
    }
}
