<?php

use App\Models\Branch;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\Party;
use App\Models\Shipment;
use App\Models\User;
use Spatie\Permission\Models\Role;

function setupBillingTestData() {
    Role::firstOrCreate(['name' => 'admin']);

    $adminUser = User::factory()->create();
    $adminUser->assignRole('admin');

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

    $party = Party::create([
        'name' => 'Client Test',
        'company_id' => $company->id,
    ]);

    $shipment1 = Shipment::create([
        'company_id' => $company->id,
        'branch_id' => $branch->id,
        'numero' => 'TEST-0001',
        'total' => 100.00,
        'fecha' => now(),
    ]);

    $shipment2 = Shipment::create([
        'company_id' => $company->id,
        'branch_id' => $branch->id,
        'numero' => 'TEST-0002',
        'total' => 150.00,
        'fecha' => now(),
    ]);

    $shipment3 = Shipment::create([
        'company_id' => $company->id,
        'branch_id' => $branch->id,
        'numero' => 'TEST-0003',
        'total' => 200.00,
        'fecha' => now(),
    ]);

    return compact('adminUser', 'company', 'branch', 'party', 'shipment1', 'shipment2', 'shipment3');
}

it('can generate a new invoice', function () {
    $data = setupBillingTestData();

    $response = $this->actingAs($data['adminUser'])->post(route('billing.store'), [
        'company_id' => $data['company']->id,
        'party_id' => $data['party']->id,
        'numero' => 'INV-1234',
        'fecha_factura' => '2026-06-16',
        'shipment_ids' => [$data['shipment1']->id, $data['shipment2']->id],
    ]);

    $response->assertRedirect();
    
    $invoice = Invoice::where('numero', 'INV-1234')->first();
    expect($invoice)->not->toBeNull();
    expect((float) $invoice->total)->toEqual(250.00); // 100 + 150
    expect($data['shipment1']->fresh()->invoice_id)->toEqual($invoice->id);
    expect($data['shipment2']->fresh()->invoice_id)->toEqual($invoice->id);
});

it('appends shipments to an existing unpaid invoice when using the same invoice number', function () {
    $data = setupBillingTestData();

    // Create initial invoice
    $invoice = Invoice::create([
        'company_id' => $data['company']->id,
        'party_id' => $data['party']->id,
        'numero' => 'INV-1234',
        'fecha_factura' => '2026-06-16',
        'total' => 100.00,
        'cobrada' => false,
    ]);
    $data['shipment1']->update(['invoice_id' => $invoice->id]);

    // Post again with the same number but different shipments
    $response = $this->actingAs($data['adminUser'])->post(route('billing.store'), [
        'company_id' => $data['company']->id,
        'party_id' => $data['party']->id,
        'numero' => 'INV-1234',
        'fecha_factura' => '2026-06-16',
        'shipment_ids' => [$data['shipment2']->id, $data['shipment3']->id],
    ]);

    $response->assertRedirect();
    
    $invoice = $invoice->fresh();
    expect((float) $invoice->total)->toEqual(450.00); // 100 + 150 + 200
    
    expect($data['shipment1']->fresh()->invoice_id)->toEqual($invoice->id); // remains attached!
    expect($data['shipment2']->fresh()->invoice_id)->toEqual($invoice->id); // newly attached
    expect($data['shipment3']->fresh()->invoice_id)->toEqual($invoice->id); // newly attached
});

it('throws validation error when trying to append shipments to a paid invoice', function () {
    $data = setupBillingTestData();

    // Create paid invoice
    $invoice = Invoice::create([
        'company_id' => $data['company']->id,
        'party_id' => $data['party']->id,
        'numero' => 'INV-1234',
        'fecha_factura' => '2026-06-16',
        'total' => 100.00,
        'cobrada' => true,
    ]);
    $data['shipment1']->update(['invoice_id' => $invoice->id]);

    $response = $this->actingAs($data['adminUser'])->post(route('billing.store'), [
        'company_id' => $data['company']->id,
        'party_id' => $data['party']->id,
        'numero' => 'INV-1234',
        'fecha_factura' => '2026-06-16',
        'shipment_ids' => [$data['shipment2']->id],
    ]);

    $response->assertSessionHasErrors(['shipment_ids']);
    expect((float) $invoice->fresh()->total)->toEqual(100.00);
});

it('can detach a shipment from an invoice', function () {
    $data = setupBillingTestData();

    // Create invoice with 2 shipments
    $invoice = Invoice::create([
        'company_id' => $data['company']->id,
        'party_id' => $data['party']->id,
        'numero' => 'INV-1234',
        'fecha_factura' => '2026-06-16',
        'total' => 250.00,
        'cobrada' => false,
    ]);
    $data['shipment1']->update(['invoice_id' => $invoice->id]);
    $data['shipment2']->update(['invoice_id' => $invoice->id]);

    // Detach shipment1
    $response = $this->actingAs($data['adminUser'])->delete(route('billing.detach-shipment', [$invoice, $data['shipment1']->id]));

    $response->assertRedirect();
    expect((float) $invoice->fresh()->total)->toEqual(150.00);
    expect($data['shipment1']->fresh()->invoice_id)->toBeNull();
    expect($data['shipment2']->fresh()->invoice_id)->toEqual($invoice->id);
});

it('can mark an invoice as paid with receipt details', function () {
    $data = setupBillingTestData();

    $invoice = Invoice::create([
        'company_id' => $data['company']->id,
        'party_id' => $data['party']->id,
        'numero' => 'INV-PAY',
        'fecha_factura' => '2026-06-16',
        'total' => 100.00,
        'cobrada' => false,
    ]);
    $data['shipment1']->update(['invoice_id' => $invoice->id]);

    $response = $this->actingAs($data['adminUser'])->post(route('billing.pay', $invoice), [
        'numero_recibo' => 'REC-001',
        'fecha_cobro' => '2026-08-01',
    ]);

    $response->assertRedirect();
    
    $invoice->refresh();
    expect($invoice->cobrada)->toBeTrue();
    expect($invoice->numero_recibo)->toEqual('REC-001');
    expect($invoice->fecha_cobro->format('Y-m-d'))->toEqual('2026-08-01');
    expect($data['shipment1']->fresh()->cobrada)->toBeTrue();
});

it('can unpay an invoice and clear receipt details', function () {
    $data = setupBillingTestData();

    $invoice = Invoice::create([
        'company_id' => $data['company']->id,
        'party_id' => $data['party']->id,
        'numero' => 'INV-UNPAY',
        'fecha_factura' => '2026-06-16',
        'total' => 100.00,
        'cobrada' => true,
        'numero_recibo' => 'REC-001',
        'fecha_cobro' => '2026-08-01',
    ]);
    $data['shipment1']->update(['invoice_id' => $invoice->id, 'cobrada' => true]);

    $response = $this->actingAs($data['adminUser'])->post(route('billing.unpay', $invoice));

    $response->assertRedirect();
    
    $invoice->refresh();
    expect($invoice->cobrada)->toBeFalse();
    expect($invoice->numero_recibo)->toBeNull();
    expect($invoice->fecha_cobro)->toBeNull();
    expect($data['shipment1']->fresh()->cobrada)->toBeFalse();
});
