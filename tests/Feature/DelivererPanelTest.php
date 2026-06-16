<?php

use App\Models\Branch;
use App\Models\Company;
use App\Models\Deliverer;
use App\Models\Delivery;
use App\Models\Shipment;
use App\Models\Ubicacion;
use App\Models\User;
use App\StateMachines\DeliveryStateMachine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

// Set up helpers and state for tests
function setupTestData() {
    // Create roles
    Role::firstOrCreate(['name' => 'repartidor']);
    Role::firstOrCreate(['name' => 'admin']);

    // Create company and branch
    $company = Company::create([
        'name' => 'Amicci Test Company',
        'prefix' => 'ATC',
        'active' => true,
    ]);

    $branch = Branch::create([
        'company_id' => $company->id,
        'name' => 'Sucursal Mendoza',
        'code' => 1,
        'active' => true,
    ]);

    $origin = Ubicacion::create([
        'nombre' => 'Dto origen',
        'branch_id' => $branch->id,
    ]);

    $destination = Ubicacion::create([
        'nombre' => 'Dto destino',
        'branch_id' => $branch->id,
    ]);

    // Create Deliverer User
    $delivererUser = User::factory()->create([
        'name' => 'Repartidor de Pruebas',
        'email' => 'repartidor@amicci.com',
    ]);
    $delivererUser->assignRole('repartidor');
    $delivererUser->companies()->attach($company->id);
    $delivererUser->branches()->attach($branch->id);

    // Create Deliverer Profile
    $deliverer = Deliverer::create([
        'name' => 'Juan Repartidor',
        'email' => 'repartidor@amicci.com',
        'user_id' => $delivererUser->id,
    ]);

    return compact('company', 'branch', 'origin', 'destination', 'delivererUser', 'deliverer');
}

it('redirects guests to login', function () {
    $response = $this->get(route('deliverer.index'));
    $response->assertRedirect('/login');
});

it('gives error message to users without deliverer profile', function () {
    Role::firstOrCreate(['name' => 'repartidor']);
    $otherUser = User::factory()->create();
    $otherUser->assignRole('repartidor');

    $response = $this->actingAs($otherUser)->get(route('deliverer.index'));
    $response->assertStatus(200);
    $response->assertSee('Tu usuario no está asociado a ningún perfil de Repartidor.');
});

it('shows active deliveries to the deliverer', function () {
    $data = setupTestData();

    // Create a delivery run assigned to this deliverer in ON_DELIVERY state
    $deliveryActive = Delivery::create([
        'company_id' => $data['company']->id,
        'delivery_number' => 'R-ACTIVE-01',
        'deliverer_id' => $data['deliverer']->id,
        'location_id' => $data['branch']->id,
        'status' => DeliveryStateMachine::ON_DELIVERY,
    ]);

    // Create a delivery run in READY state
    $deliveryReady = Delivery::create([
        'company_id' => $data['company']->id,
        'delivery_number' => 'R-READY-02',
        'deliverer_id' => $data['deliverer']->id,
        'location_id' => $data['branch']->id,
        'status' => DeliveryStateMachine::READY,
    ]);

    // Create a delivery run assigned to someone else
    $otherDeliverer = Deliverer::create(['name' => 'Otro']);
    $deliveryOther = Delivery::create([
        'company_id' => $data['company']->id,
        'delivery_number' => 'R-OTHER-03',
        'deliverer_id' => $otherDeliverer->id,
        'location_id' => $data['branch']->id,
        'status' => DeliveryStateMachine::ON_DELIVERY,
    ]);

    $response = $this->actingAs($data['delivererUser'])->get(route('deliverer.index'));

    $response->assertStatus(200);
    $response->assertSee('R-ACTIVE-01');
    $response->assertDontSee('R-READY-02');
    $response->assertDontSee('R-OTHER-03');
});

it('can view delivery detail page', function () {
    $data = setupTestData();

    $delivery = Delivery::create([
        'company_id' => $data['company']->id,
        'delivery_number' => 'R-DETAIL-01',
        'deliverer_id' => $data['deliverer']->id,
        'location_id' => $data['branch']->id,
        'status' => DeliveryStateMachine::ON_DELIVERY,
    ]);

    $shipment = Shipment::create([
        'company_id' => $data['company']->id,
        'numero' => 'G-TEST-100',
        'fecha' => now(),
        'ubicacion_actual' => 'En reparto',
        'delivery_id' => $delivery->id,
        'origen_id' => $data['origin']->id,
        'destino_id' => $data['destination']->id,
    ]);

    $response = $this->actingAs($data['delivererUser'])->get(route('deliverer.show', $delivery));

    $response->assertStatus(200);
    $response->assertSee('R-DETAIL-01');
    $response->assertSee('G-TEST-100');
});

it('cannot view other deliverer delivery details', function () {
    $data = setupTestData();

    $otherDeliverer = Deliverer::create(['name' => 'Otro']);
    $delivery = Delivery::create([
        'company_id' => $data['company']->id,
        'delivery_number' => 'R-OTHER-DETAIL',
        'deliverer_id' => $otherDeliverer->id,
        'location_id' => $data['branch']->id,
        'status' => DeliveryStateMachine::ON_DELIVERY,
    ]);

    $response = $this->actingAs($data['delivererUser'])->get(route('deliverer.show', $delivery));
    $response->assertStatus(403);
});

it('can confirm shipment deliveries', function () {
    $data = setupTestData();

    $delivery = Delivery::create([
        'company_id' => $data['company']->id,
        'delivery_number' => 'R-DELIVER-01',
        'deliverer_id' => $data['deliverer']->id,
        'location_id' => $data['branch']->id,
        'status' => DeliveryStateMachine::ON_DELIVERY,
    ]);

    $shipment1 = Shipment::create([
        'company_id' => $data['company']->id,
        'numero' => 'G-SHIP-1',
        'fecha' => now(),
        'ubicacion_actual' => 'En reparto',
        'delivery_id' => $delivery->id,
        'origen_id' => $data['origin']->id,
        'destino_id' => $data['destination']->id,
    ]);

    $shipment2 = Shipment::create([
        'company_id' => $data['company']->id,
        'numero' => 'G-SHIP-2',
        'fecha' => now(),
        'ubicacion_actual' => 'En reparto',
        'delivery_id' => $delivery->id,
        'origen_id' => $data['origin']->id,
        'destino_id' => $data['destination']->id,
    ]);

    $response = $this->actingAs($data['delivererUser'])->post(route('deliverer.confirm', $delivery), [
        'shipment_ids' => [$shipment1->id]
    ]);

    $response->assertRedirect(route('deliverer.show', $delivery));

    $this->assertDatabaseHas('shipments', [
        'id' => $shipment1->id,
        'ubicacion_actual' => 'Entregado',
    ]);

    $this->assertDatabaseHas('shipments', [
        'id' => $shipment2->id,
        'ubicacion_actual' => 'En reparto',
    ]);

    $this->assertDatabaseHas('status_histories', [
        'model_id' => $shipment1->id,
        'from_status' => 'En reparto',
        'to_status' => 'Entregado',
    ]);
});

it('cannot finalize delivery with pending shipments', function () {
    $data = setupTestData();

    $delivery = Delivery::create([
        'company_id' => $data['company']->id,
        'delivery_number' => 'R-FINALIZE-01',
        'deliverer_id' => $data['deliverer']->id,
        'location_id' => $data['branch']->id,
        'status' => DeliveryStateMachine::ON_DELIVERY,
    ]);

    $shipment1 = Shipment::create([
        'company_id' => $data['company']->id,
        'numero' => 'G-PEND-1',
        'fecha' => now(),
        'ubicacion_actual' => 'En reparto',
        'delivery_id' => $delivery->id,
        'origen_id' => $data['origin']->id,
        'destino_id' => $data['destination']->id,
    ]);

    $stateMachine = $delivery->stateMachine();

    // Should not be able to finalize since G-PEND-1 is still "En reparto"
    expect($stateMachine->canTransitionTo($stateMachine->currentStatus(), DeliveryStateMachine::FINISHED))->toBeFalse();

    // Mark it as delivered
    $shipment1->update(['ubicacion_actual' => 'Entregado']);

    // Refresh state machine
    $delivery->refresh();
    $stateMachine = $delivery->stateMachine();

    // Now it should allow finalization
    expect($stateMachine->canTransitionTo($stateMachine->currentStatus(), DeliveryStateMachine::FINISHED))->toBeTrue();
});

it('allows admin to view delivery detail page in any status', function () {
    $data = setupTestData();

    // Create an admin user
    $adminUser = User::factory()->create();
    $adminUser->assignRole('admin');

    // Create a delivery in READY status
    $delivery = Delivery::create([
        'company_id' => $data['company']->id,
        'delivery_number' => 'R-ADMIN-VIEW',
        'deliverer_id' => $data['deliverer']->id,
        'location_id' => $data['branch']->id,
        'status' => DeliveryStateMachine::READY,
    ]);

    $response = $this->actingAs($adminUser)->get(route('deliverer.show', $delivery));

    $response->assertStatus(200);
    $response->assertSee('R-ADMIN-VIEW');
});

it('can revert an already delivered shipment back to en reparto', function () {
    $data = setupTestData();

    $delivery = Delivery::create([
        'company_id' => $data['company']->id,
        'delivery_number' => 'R-REVERT-01',
        'deliverer_id' => $data['deliverer']->id,
        'location_id' => $data['branch']->id,
        'status' => DeliveryStateMachine::ON_DELIVERY,
    ]);

    $shipment = Shipment::create([
        'company_id' => $data['company']->id,
        'numero' => 'G-REVERT-1',
        'fecha' => now(),
        'ubicacion_actual' => 'Entregado',
        'delivery_id' => $delivery->id,
        'origen_id' => $data['origin']->id,
        'destino_id' => $data['destination']->id,
    ]);

    // Send empty shipment_ids array to unmark/revert the shipment
    $response = $this->actingAs($data['delivererUser'])->post(route('deliverer.confirm', $delivery), [
        'shipment_ids' => []
    ]);

    $response->assertRedirect(route('deliverer.show', $delivery));

    $this->assertDatabaseHas('shipments', [
        'id' => $shipment->id,
        'ubicacion_actual' => 'En reparto',
        'fecha_entrega' => null,
    ]);

    $this->assertDatabaseHas('status_histories', [
        'model_id' => $shipment->id,
        'from_status' => 'Entregado',
        'to_status' => 'En reparto',
    ]);
});

