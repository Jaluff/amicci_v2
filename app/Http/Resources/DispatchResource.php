<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DispatchResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'               => $this->id,
            'dispatch_number'  => $this->dispatch_number,
            'status'           => $this->status,
            'cost'             => $this->cost,
            'seal_number'      => $this->seal_number,
            'semi_number'      => $this->semi_number,
            'chassis_number'   => $this->chassis_number,
            'origin'           => $this->whenLoaded('origin', fn() => ['id' => $this->origin->id, 'nombre' => $this->origin->nombre]),
            'destination'      => $this->whenLoaded('destination', fn() => ['id' => $this->destination->id, 'nombre' => $this->destination->nombre]),
            'driver'           => $this->whenLoaded('driver', fn() => ['id' => $this->driver->id, 'name' => $this->driver->name]),
            'routes_count'     => $this->routes_count ?? 0,
            'created_at'       => $this->created_at?->format('d/m/Y'),
        ];
    }
}