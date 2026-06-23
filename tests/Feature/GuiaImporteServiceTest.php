<?php

use App\Models\Company;
use App\Models\Party;
use App\Models\PartyTariffSetting;
use App\Models\Shipment;
use App\Models\ShipmentItem;
use App\Models\TariffTable;
use App\Models\Ubicacion;
use App\Services\GuiaImporteService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('calculates freight only if all items belong to the configured tariff mode', function () {
    // 1. Setup Company
    $company = Company::create([
        'name' => 'Test Company',
        'prefix' => 'TC',
        'active' => true,
    ]);

    // 2. Setup Ubicaciones
    $origin = Ubicacion::create(['nombre' => 'Buenos Aires']);
    $destination = Ubicacion::create(['nombre' => 'Mendoza']);

    // 3. Setup Client (Party)
    $client = Party::create([
        'name' => 'Client A',
        'company_id' => $company->id,
    ]);

    // 4. Setup Tariff Table
    $tariffTable = TariffTable::create([
        'name' => 'Tarifa Base',
        'origin_id' => $origin->id,
        'destination_id' => $destination->id,
        'rate_per_ton' => 100.00,
        'rate_per_m3' => 50.00,
        'valid_from' => now()->subDay(),
        'is_active' => true,
    ]);

    // 5. Setup Party Tariff Setting for "bultos"
    $setting = PartyTariffSetting::create([
        'party_id' => $client->id,
        'tariff_table_id' => $tariffTable->id,
        'billing_mode' => 'bultos',
        'rate_per_bulto' => 50.00,
        'valid_from' => now()->subDay(),
    ]);

    // 6. Setup Shipment
    $shipment = Shipment::create([
        'company_id' => $company->id,
        'remitente_id' => $client->id,
        'destinatario_id' => $client->id,
        'flete_a_pagar_en' => 'origen',
        'numero' => 'GUIDE-1234',
        'fecha' => now(),
    ]);

    // 6.1 Case A: All items are "bultos" -> should calculate
    $item1 = ShipmentItem::create([
        'shipment_id' => $shipment->id,
        'tipo_paquete' => 'bultos',
        'cantidad' => 10,
        'peso' => 10,
        'volumen' => 1,
    ]);

    $service = new GuiaImporteService();
    $result = $service->calcular($shipment, $tariffTable->id);
    expect($result)->not->toBeNull();
    expect((float) $result['importe_final'])->toEqual(500.00); // 10 * 50

    // 6.2 Case B: Mixed items (bultos + palets) -> should NOT calculate (returns null)
    $item2 = ShipmentItem::create([
        'shipment_id' => $shipment->id,
        'tipo_paquete' => 'palets',
        'cantidad' => 5,
        'peso' => 100,
        'volumen' => 2,
    ]);

    $shipment->unsetRelation('items'); // Force reload items
    $resultMixed = $service->calcular($shipment, $tariffTable->id);
    expect($resultMixed)->toBeNull();
});

it('allows "pallets" billing mode with palets items only', function () {
    $company = Company::create(['name' => 'Test Company', 'prefix' => 'TC', 'active' => true]);
    $origin = Ubicacion::create(['nombre' => 'Buenos Aires']);
    $destination = Ubicacion::create(['nombre' => 'Mendoza']);
    $client = Party::create(['name' => 'Client B', 'company_id' => $company->id]);
    
    $tariffTable = TariffTable::create([
        'name' => 'Tarifa Base',
        'origin_id' => $origin->id,
        'destination_id' => $destination->id,
        'rate_per_ton' => 100.00,
        'rate_per_m3' => 50.00,
        'valid_from' => now()->subDay(),
        'is_active' => true,
    ]);

    $setting = PartyTariffSetting::create([
        'party_id' => $client->id,
        'tariff_table_id' => $tariffTable->id,
        'billing_mode' => 'pallets',
        'rate_per_pallet' => 200.00,
        'valid_from' => now()->subDay(),
    ]);

    $shipment = Shipment::create([
        'company_id' => $company->id,
        'remitente_id' => $client->id,
        'flete_a_pagar_en' => 'origen',
        'numero' => 'GUIDE-1235',
        'fecha' => now(),
    ]);

    ShipmentItem::create([
        'shipment_id' => $shipment->id,
        'tipo_paquete' => 'palets',
        'cantidad' => 3,
        'peso' => 60,
    ]);

    $service = new GuiaImporteService();
    $result = $service->calcular($shipment, $tariffTable->id);
    expect($result)->not->toBeNull();
    expect((float) $result['importe_final'])->toEqual(600.00); // 3 * 200

    // Add invalid item type (bultos)
    ShipmentItem::create([
        'shipment_id' => $shipment->id,
        'tipo_paquete' => 'bultos',
        'cantidad' => 1,
        'peso' => 5,
    ]);

    $shipment->unsetRelation('items');
    $resultMixed = $service->calcular($shipment, $tariffTable->id);
    expect($resultMixed)->toBeNull();
});
