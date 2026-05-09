<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class CompanyController extends Controller
{
    /**
     * Show the company selector page.
     */
    public function select()
    {
        $companies = Auth::user()->companies;

        if ($companies->count() === 1) {
            session()->put('company_id', $companies->first()->id);

            return redirect()->intended(route('dashboard'));
        }

        return view('company.select', compact('companies'));
    }

    /**
     * Switch the active company.
     */
    public function switch(Request $request)
    {
        $request->validate([
            'company_id' => ['required', 'integer'],
        ]);

        $company = Auth::user()
            ->companies()
            ->findOrFail($request->company_id);

        session()->put('company_id', $company->id);

        return redirect()->route('dashboard')->with('success', "Empresa activa: {$company->name}");
    }

    /**
     * List all companies (Admin only).
     */
    public function index()
    {
        $companies = Company::all();

        return view('company.index', compact('companies'));
    }

    /**
     * Show the company settings edit form.
     */
    public function edit(Company $company)
    {
        $company->load('addresses'); // Cargar todas las direcciones polimórficas

        return view('company.edit', compact('company'));
    }

    /**
     * Update the company's settings and its primary address.
     */
    public function update(Request $request, Company $company)
    {
        $validated = $request->validate([
            // Core Config
            'name' => ['required', 'string', 'max:255'],
            'prefix' => ['nullable', 'string', 'max:10'],
            'last_shipment_number' => ['required', 'integer', 'min:0'],
            'last_dispatch_number' => ['required', 'integer', 'min:0'],
            'last_route_number' => ['required', 'integer', 'min:0'],
            'contra_reembolso_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'color' => ['nullable', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],

            // Billing / Legal Profile
            'legal_name' => ['nullable', 'string', 'max:255'],
            'cuit' => ['nullable', 'string', 'max:50'],
            'gross_income' => ['nullable', 'string', 'max:50'],
            'establishment' => ['nullable', 'string', 'max:100'],
            'stamping_headquarters' => ['nullable', 'string', 'max:100'],
            'start_of_activities' => ['nullable', 'date'],

            // Legacy ones inside companies (opcional)
            'address_line1' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:255'],

            // Array of addresses
            'addresses' => ['array'],
            'addresses.*.id' => ['nullable'],
            'addresses.*.type' => ['required', 'string', 'max:50'],
            'addresses.*.address_line1' => ['required', 'string', 'max:255'],
            'addresses.*.city' => ['nullable', 'string', 'max:100'],
            'addresses.*.state' => ['nullable', 'string', 'max:100'],
            'addresses.*.zip_code' => ['nullable', 'string', 'max:20'],
            'addresses.*.phone' => ['nullable', 'string', 'max:100'],
            'addresses.*.email' => ['nullable', 'email', 'max:255'],
            'addresses.*.is_primary' => ['nullable'],
        ]);

        $company->update([
            'name' => $validated['name'],
            'prefix' => $validated['prefix'] ?? null,
            'last_shipment_number' => $validated['last_shipment_number'],
            'last_dispatch_number' => $validated['last_dispatch_number'],
            'last_route_number' => $validated['last_route_number'],
            'contra_reembolso_percent' => $validated['contra_reembolso_percent'] ?? 0,
            'color' => $validated['color'] ?? '#6366f1',

            'legal_name' => $validated['legal_name'] ?? null,
            'cuit' => $validated['cuit'] ?? null,
            'gross_income' => $validated['gross_income'] ?? null,
            'establishment' => $validated['establishment'] ?? null,
            'stamping_headquarters' => $validated['stamping_headquarters'] ?? null,
            'start_of_activities' => $validated['start_of_activities'] ?? null,

            // Dejar intactos los campos de dirección heredados; se actualizarán después con la Direccion Principal
        ]);

        // Sincronizar (crear/actualizar/borrar) múltiples direcciones polimórficas
        $existingAddressesIds = [];
        if ($request->has('addresses')) {
            // Check if there is at least one primary. If not, make first one primary.
            $hasPrimary = collect($validated['addresses'])->contains('is_primary', true);

            foreach ($validated['addresses'] as $index => $addrData) {
                $isPrimary = $hasPrimary ? ! empty($addrData['is_primary']) : ($index === 0);

                $address = $company->addresses()->updateOrCreate(
                    ['id' => $addrData['id'] ?? null],
                    [
                        'type' => $addrData['type'] ?? 'Sucursal',
                        'address_line1' => $addrData['address_line1'],
                        'city' => $addrData['city'] ?? null,
                        'state' => $addrData['state'] ?? null,
                        'zip_code' => $addrData['zip_code'] ?? null,
                        'phone' => $addrData['phone'] ?? null,
                        'email' => $addrData['email'] ?? null,
                        'is_primary' => $isPrimary,
                    ]
                );
                $existingAddressesIds[] = $address->id;

                // Actualizar los legacy records de company si es primaria
                if ($isPrimary) {
                    $company->update([
                        'address_line1' => $addrData['address_line1'],
                        'phone' => $addrData['phone'] ?? null,
                        'email' => $addrData['email'] ?? null,
                    ]);
                }
            }
        }

        // Eliminar las que fueron quitadas en el frontend
        $company->addresses()->whereNotIn('id', $existingAddressesIds)->delete();

        return redirect()->route('companies.index')->with('success', "Datos de la empresa '{$company->name}' actualizados correctamente.");
    }
}
