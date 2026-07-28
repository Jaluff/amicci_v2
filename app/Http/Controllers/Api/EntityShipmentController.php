<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\EntityShipmentFilterRequest;
use App\Services\Api\EntityShipmentService;
use Illuminate\Http\JsonResponse;

class EntityShipmentController extends Controller
{
    public function __construct(
        protected EntityShipmentService $shipmentService
    ) {}

    public function index(EntityShipmentFilterRequest $request): JsonResponse
    {
        $party = $request->user();
        $guias = $this->shipmentService->getShipmentsForParty($party, $request->validated());

        return response()->json(['guias' => $guias]);
    }
}
