<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransportRouteResource extends JsonResource
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
            'route_number' => $this->route_number,
            'date' => $this->created_at ? $this->created_at->format('Y-m-d') : null,
            'origin' => $this->whenLoaded('origin', function () {
                return [
                    'id' => $this->origin->id,
                    'nombre' => $this->origin->nombre,
                ];
            }),
            'destination' => $this->whenLoaded('destination', function () {
                return [
                    'id' => $this->destination->id,
                    'nombre' => $this->destination->nombre,
                ];
            }),
            'status' => $this->status,
            'shipments_count' => $this->whenCounted('shipments'),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}