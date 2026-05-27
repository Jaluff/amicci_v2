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
        $company->load('branches');
        $branches = \App\Models\Branch::where('active', true)->orderBy('name')->get();

        return view('company.edit', compact('company', 'branches'));
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

            // Sucursales
            'branches' => ['nullable', 'array'],
            'branches.*' => ['integer', 'exists:branches,id'],
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
        ]);

        $branchesToSync = $validated['branches'] ?? [];
        $company->branches()->sync($branchesToSync);

        return redirect()->route('companies.index')->with('success', "Datos de la empresa '{$company->name}' actualizados correctamente.");
    }
}
