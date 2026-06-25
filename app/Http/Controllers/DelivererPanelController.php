<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Services\DelivererService;
use Illuminate\Http\Request;

class DelivererPanelController extends Controller
{
    public function __construct(
        private readonly DelivererService $service,
    ) {}

    public function index(Request $request)
    {
        $user = auth()->user();
        $deliveries = $this->service->getActiveDeliveries($user);

        if (! $user->hasAnyRole(['admin', 'supervisor']) && ! $user->deliverer) {
            return view('deliverer_panel.index', [
                'deliveries' => collect(),
                'error' => 'Tu usuario no está asociado a ningún perfil de Repartidor.',
                'layout' => 'layouts.simple',
            ]);
        }

        return view('deliverer_panel.index', [
            'deliveries' => $deliveries,
            'layout' => 'layouts.simple',
        ]);
    }

    public function show(Request $request, Delivery $delivery)
    {
        $user = auth()->user();
        $error = $this->service->authorizeDeliveryAccess($user, $delivery);

        if ($error) {
            if (str_contains($error, 'permiso')) {
                abort(403, $error);
            }

            return redirect()->route('deliverer.index')->with('error', $error);
        }

        $this->service->loadDeliveryWithShipments($delivery);

        return view('deliverer_panel.show', [
            'delivery' => $delivery,
            'layout' => 'layouts.simple',
        ]);
    }

    public function confirmDelivery(Request $request, Delivery $delivery)
    {
        $user = auth()->user();

        $accessError = $this->service->authorizeDeliveryAccess($user, $delivery);
        if ($accessError && str_contains($accessError, 'permiso')) {
            abort(403, $accessError);
        }

        $validated = $request->validate([
            'shipment_ids' => 'nullable|array',
            'shipment_ids.*' => 'required|integer|exists:shipments,id',
        ]);

        $checkedIds = array_map('intval', $validated['shipment_ids'] ?? []);

        $result = $this->service->confirmDeliveries($delivery, $checkedIds, $user);

        if ($request->wantsJson()) {
            return response()->json($result, $result['success'] ? 200 : 400);
        }

        return redirect()->route('deliverer.show', $delivery)
            ->with($result['success'] ? 'success' : 'error', $result['message']);
    }
}
