<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Models\Shipment;
use App\Models\StatusHistory;
use App\StateMachines\DeliveryStateMachine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DelivererPanelController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Admins and supervisors can view all deliveries in reparto or test
        $isAdminOrSupervisor = $user->hasAnyRole(['admin', 'supervisor']);
        
        if ($isAdminOrSupervisor) {
            $deliveries = Delivery::where('status', DeliveryStateMachine::ON_DELIVERY)
                ->with(['deliverer', 'location'])
                ->orderByDesc('created_at')
                ->get();
        } else {
            $deliverer = $user->deliverer;
            
            if (!$deliverer) {
                return view('deliverer_panel.index', [
                    'deliveries' => collect(),
                    'error' => 'Tu usuario no está asociado a ningún perfil de Repartidor.'
                ]);
            }
            
            $deliveries = Delivery::where('deliverer_id', $deliverer->id)
                ->where('status', DeliveryStateMachine::ON_DELIVERY)
                ->with(['deliverer', 'location'])
                ->orderByDesc('created_at')
                ->get();
        }

        return view('deliverer_panel.index', compact('deliveries'));
    }

    public function show(Delivery $delivery)
    {
        $user = auth()->user();
        $isAdminOrSupervisor = $user->hasAnyRole(['admin', 'supervisor']);
        
        if (!$isAdminOrSupervisor) {
            $deliverer = $user->deliverer;
            if (!$deliverer || $delivery->deliverer_id !== $deliverer->id) {
                abort(403, 'No tienes permiso para ver este reparto.');
            }
        }

        if ($delivery->status !== DeliveryStateMachine::ON_DELIVERY && !$isAdminOrSupervisor) {
            return redirect()->route('deliverer.index')
                ->with('error', 'El reparto debe estar en estado "En reparto" para gestionar sus entregas.');
        }

        $delivery->load(['deliverer', 'location', 'shipments' => function ($q) {
            $q->with(['sender', 'recipient'])
                ->withCount(['items as bultos' => function ($query) {
                    $query->select(DB::raw('COALESCE(SUM(cantidad), 0)'));
                }]);
        }]);

        return view('deliverer_panel.show', compact('delivery'));
    }

    public function confirmDelivery(Request $request, Delivery $delivery)
    {
        $user = auth()->user();
        $isAdminOrSupervisor = $user->hasAnyRole(['admin', 'supervisor']);
        
        if (!$isAdminOrSupervisor) {
            $deliverer = $user->deliverer;
            if (!$deliverer || $delivery->deliverer_id !== $deliverer->id) {
                abort(403, 'No tienes permiso para operar en este reparto.');
            }
        }

        if ($delivery->status !== DeliveryStateMachine::ON_DELIVERY) {
            return response()->json([
                'success' => false,
                'message' => 'El reparto no se encuentra en estado "En reparto".'
            ], 400);
        }

        $validated = $request->validate([
            'shipment_ids' => 'nullable|array',
            'shipment_ids.*' => 'required|integer|exists:shipments,id',
        ]);

        $checkedIds = $validated['shipment_ids'] ?? [];

        // Verify that all checked shipment IDs belong to this delivery
        if (!empty($checkedIds)) {
            $verifyCount = Shipment::whereIn('id', $checkedIds)
                ->where('delivery_id', $delivery->id)
                ->count();
            if ($verifyCount !== count($checkedIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Algunas de las guías seleccionadas no pertenecen a este reparto.'
                ], 400);
            }
        }

        $allShipments = Shipment::where('delivery_id', $delivery->id)->get();

        DB::transaction(function () use ($allShipments, $checkedIds, $user) {
            foreach ($allShipments as $shipment) {
                $isChecked = in_array((string)$shipment->id, array_map('strval', $checkedIds), true);

                if ($isChecked && $shipment->ubicacion_actual !== 'Entregado') {
                    $shipment->update([
                        'ubicacion_actual' => 'Entregado',
                        'fecha_entrega' => now(),
                    ]);

                    StatusHistory::create([
                        'model_type' => Shipment::class,
                        'model_id' => $shipment->id,
                        'from_status' => 'En reparto',
                        'to_status' => 'Entregado',
                        'comment' => 'Entregado por Repartidor desde Planilla Móvil',
                        'user_id' => $user->id,
                        'transitioned_at' => now(),
                    ]);

                    if (method_exists($shipment, 'logActivity')) {
                        $shipment->logActivity(
                            "Cambio de estado: En reparto ➔ Entregado (Entregado por repartidor)",
                            'status_changed',
                            ['from' => 'En reparto', 'to' => 'Entregado']
                        );
                    }
                } elseif (!$isChecked && $shipment->ubicacion_actual === 'Entregado') {
                    $shipment->update([
                        'ubicacion_actual' => 'En reparto',
                        'fecha_entrega' => null,
                    ]);

                    StatusHistory::create([
                        'model_type' => Shipment::class,
                        'model_id' => $shipment->id,
                        'from_status' => 'Entregado',
                        'to_status' => 'En reparto',
                        'comment' => 'Entrega revertida a En reparto por Repartidor desde Planilla Móvil',
                        'user_id' => $user->id,
                        'transitioned_at' => now(),
                    ]);

                    if (method_exists($shipment, 'logActivity')) {
                        $shipment->logActivity(
                            "Cambio de estado: Entregado ➔ En reparto (Entrega revertida por repartidor)",
                            'status_changed',
                            ['from' => 'Entregado', 'to' => 'En reparto']
                        );
                    }
                }
            }
        });

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Cambios guardados correctamente.'
            ]);
        }

        return redirect()->route('deliverer.show', $delivery)
            ->with('success', 'Cambios guardados correctamente.');
    }
}
