<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Services\DelivererService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeliveryController extends Controller
{
    public function __construct(
        private readonly DelivererService $service,
    ) {}

    /**
     * Lista los repartos activos del repartidor autenticado.
     */
    public function index(Request $request): JsonResponse
    {
        $deliveries = $this->service->getActiveDeliveries($request->user());

        return response()->json([
            'data' => $deliveries->map(function (Delivery $d) {
                $totalCount = $d->shipments()->count();
                $deliveredCount = $d->shipments()->where('ubicacion_actual', 'Entregado')->count();
                $isCompleted = $totalCount > 0 && $deliveredCount === $totalCount;

                return [
                    'id' => $d->id,
                    'delivery_number' => $d->delivery_number,
                    'status' => $isCompleted ? 'Completado' : $d->status,
                    'vehicle_plate' => $d->vehicle_plate,
                    'guide_count' => $d->guide_count,
                    'package_count' => $d->package_count,
                    'load_date' => $d->load_date?->format('Y-m-d'),
                    'dispatch_date' => $d->dispatch_date?->format('Y-m-d'),
                    'location' => $d->location ? [
                        'id' => $d->location->id,
                        'name' => $d->location->name,
                    ] : null,
                    'deliverer' => $d->deliverer ? [
                        'id' => $d->deliverer->id,
                        'name' => $d->deliverer->name,
                    ] : null,
                ];
            }),
        ]);
    }

    /**
     * Detalle de un reparto con todas sus guías.
     */
    public function show(Request $request, Delivery $delivery): JsonResponse
    {
        $error = $this->service->authorizeDeliveryAccess($request->user(), $delivery);

        if ($error) {
            return response()->json(['message' => $error], 403);
        }

        $this->service->loadDeliveryWithShipments($delivery);

        $totalCount = $delivery->shipments->count();
        $deliveredCount = $delivery->shipments->where('ubicacion_actual', 'Entregado')->count();
        $isCompleted = $totalCount > 0 && $deliveredCount === $totalCount;

        return response()->json([
            'data' => [
                'id' => $delivery->id,
                'delivery_number' => $delivery->delivery_number,
                'status' => $isCompleted ? 'Completado' : $delivery->status,
                'vehicle_plate' => $delivery->vehicle_plate,
                'guide_count' => $delivery->guide_count,
                'package_count' => $delivery->package_count,
                'load_date' => $delivery->load_date?->format('Y-m-d'),
                'dispatch_date' => $delivery->dispatch_date?->format('Y-m-d'),
                'location' => $delivery->location ? [
                    'id' => $delivery->location->id,
                    'name' => $delivery->location->name,
                ] : null,
                'deliverer' => $delivery->deliverer ? [
                    'id' => $delivery->deliverer->id,
                    'name' => $delivery->deliverer->name,
                ] : null,
                'shipments' => $delivery->shipments->map(fn ($s) => [
                    'id' => $s->id,
                    'numero' => $s->numero,
                    'ubicacion_actual' => $s->ubicacion_actual,
                    'fecha_entrega' => $s->fecha_entrega?->format('Y-m-d'),
                    'bultos' => $s->bultos ?? 0,
                    'tipo_flete' => $s->tipo_flete,
                    'contra_reembolso' => $s->contra_reembolso,
                    'monto_contra_reembolso' => $s->monto_contra_reembolso,
                    'total' => $s->total,
                    'notas' => $s->notas,
                    'has_active_problem' => $s->hasActiveProblem(),
                    'sender' => $s->sender ? [
                        'id' => $s->sender->id,
                        'name' => $s->sender->name,
                        'address' => $s->sender->primaryAddress 
                            ? trim($s->sender->primaryAddress->address_line1 . ' ' . $s->sender->primaryAddress->address_line2) 
                            : ($s->sender->address ?: null),
                        'phone' => $s->sender->primaryAddress && $s->sender->primaryAddress->phone 
                            ? $s->sender->primaryAddress->phone 
                            : ($s->sender->phone ?: null),
                    ] : null,
                    'recipient' => $s->recipient ? [
                        'id' => $s->recipient->id,
                        'name' => $s->recipient->name,
                        'address' => $s->recipient->primaryAddress 
                            ? trim($s->recipient->primaryAddress->address_line1 . ' ' . $s->recipient->primaryAddress->address_line2) 
                            : ($s->recipient->address ?: null),
                        'locality' => $s->recipient->primaryAddress 
                            ? $s->recipient->primaryAddress->state 
                            : ($s->recipient->locality ?: null),
                        'city' => $s->recipient->primaryAddress 
                            ? $s->recipient->primaryAddress->city 
                            : ($s->recipient->city ?: null),
                        'phone' => $s->recipient->primaryAddress && $s->recipient->primaryAddress->phone 
                            ? $s->recipient->primaryAddress->phone 
                            : ($s->recipient->phone ?: null),
                    ] : null,
                    'origin' => $s->origin ? [
                        'id' => $s->origin->id,
                        'nombre' => $s->origin->nombre,
                    ] : null,
                    'destination' => $s->destination ? [
                        'id' => $s->destination->id,
                        'nombre' => $s->destination->nombre,
                    ] : null,
                ]),
            ],
        ]);
    }

    /**
     * Confirma entregas de guías (marca como entregado o revierte).
     */
    public function confirm(Request $request, Delivery $delivery): JsonResponse
    {
        $error = $this->service->authorizeDeliveryAccess($request->user(), $delivery);

        if ($error) {
            return response()->json(['message' => $error], 403);
        }

        $validated = $request->validate([
            'shipment_ids' => 'nullable|array',
            'shipment_ids.*' => 'required|integer|exists:shipments,id',
        ]);

        $checkedIds = array_map('intval', $validated['shipment_ids'] ?? []);

        $result = $this->service->confirmDeliveries($delivery, $checkedIds, $request->user());

        return response()->json($result, $result['success'] ? 200 : 400);
    }
}
