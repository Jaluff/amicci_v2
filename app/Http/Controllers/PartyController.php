<?php

namespace App\Http\Controllers;

use App\Models\Party;
use App\Models\PartyTariffSetting;
use App\Models\TariffTable;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class PartyController extends Controller
{
    public function index()
    {
        return view('parties.index');
    }

    public function datatable()
    {
        $query = Party::query()
            ->with('primaryAddress'); // Cargamos la dirección principal

        return DataTables::of($query)
            ->addColumn('direcciones', function ($row) {
            $addr = $row->primaryAddress;
            if (!$addr)
                return '-';
            return $addr->address_line1 . ($addr->city ? ', ' . $addr->city : '');
        })
            ->addColumn('contacto', function ($row) {
            $addr = $row->primaryAddress;
            if (!$addr)
                return '-';
            $contacto = [];
            if ($addr->phone)
                $contacto[] = '📞 ' . $addr->phone;
            if ($addr->email)
                $contacto[] = '✉️ ' . $addr->email;
            return implode(' <br> ', $contacto) ?: '-';
        })
            ->addColumn('acciones', function ($row) {
            $editUrl = route('parties.edit', $row->id);
            $deleteUrl = route('parties.destroy', $row->id);
            $csrf = csrf_token();
            $confirm = 'return confirm(\'¿Eliminar este cliente?\')';

            return "<div class='flex items-center gap-2'>
                    <a href='{$editUrl}' title='Editar' class='inline-flex items-center justify-center p-2 rounded-md bg-blue-50 text-blue-700 border border-blue-200 hover:bg-blue-100 dark:bg-blue-900/40 dark:text-blue-400 dark:border-blue-800 dark:hover:bg-blue-800/60 dark:hover:text-blue-300 transition-colors'>
                        <svg xmlns='http://www.w3.org/2000/svg' class='w-4 h-4' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><path d='M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7'/><path d='M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z'/></svg>
                    </a>
                    <form action='{$deleteUrl}' method='POST' onsubmit='{$confirm}' class='inline m-0'>
                        <input type='hidden' name='_token' value='{$csrf}'>
                        <input type='hidden' name='_method' value='DELETE'>
                        <button type='submit' title='Eliminar' class='inline-flex items-center justify-center p-2 rounded-md bg-red-50 text-red-700 border border-red-200 hover:bg-red-100 dark:bg-red-900/40 dark:text-red-400 dark:border-red-800 dark:hover:bg-red-800/60 dark:hover:text-red-300 transition-colors'>
                            <svg xmlns='http://www.w3.org/2000/svg' class='w-4 h-4' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><polyline points='3 6 5 6 21 6'/><path d='M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6'/><path d='M10 11v6'/><path d='M14 11v6'/><path d='M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2'/></svg>
                        </button>
                    </form>
                </div>";
        })
            ->rawColumns(['acciones', 'contacto'])
            ->make(true);
    }

    public function create()
    {
        return view('parties.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'document' => 'nullable|string|max:100',
            'document_type' => 'nullable|string|max:50',
            'tax_status' => 'nullable|string|max:100',

            // Array of addresses
            'addresses' => 'array',
            'addresses.*.id' => 'nullable',
            'addresses.*.type' => 'required|string|max:50',
            'addresses.*.address_line1' => 'nullable|string|max:255',
            'addresses.*.city' => 'nullable|string|max:100',
            'addresses.*.state' => 'required|string|max:100',
            'addresses.*.zip_code' => 'nullable|string|max:20',
            'addresses.*.phone' => 'nullable|string|max:100',
            'addresses.*.email' => 'nullable|email|max:255',
            'addresses.*.is_primary' => 'nullable',
        ]);

        $party = Party::create([
            'name'          => $validated['name'],
            'document'      => $validated['document'] ?? null,
            'document_type' => $validated['document_type'] ?? null,
            'tax_status'    => $validated['tax_status'] ?? null,
            'company_id'    => session('company_id'),
        ]);

        if ($request->has('addresses')) {
            $hasPrimary = collect($validated['addresses'])->contains('is_primary', true);

            foreach ($validated['addresses'] as $index => $addrData) {
                $isPrimary = $hasPrimary ? !empty($addrData['is_primary']) : ($index === 0);

                $party->addresses()->create([
                    'type' => $addrData['type'] ?? 'Sucursal',
                    'address_line1' => $addrData['address_line1'],
                    'city' => $addrData['city'] ?? null,
                    'state' => $addrData['state'] ?? null,
                    'zip_code' => $addrData['zip_code'] ?? null,
                    'phone' => $addrData['phone'] ?? null,
                    'email' => $addrData['email'] ?? null,
                    'is_primary' => $isPrimary,
                ]);
            }
        }

        // Guardar configuración tarifaria si se envió habilitada
        // Solo guarda si se marcó el checkbox (billing_mode tiene valor)
        if ($request->filled('tariff.billing_mode')) {
            $this->saveTariffSetting($party, $request->input('tariff'));
        }

        return redirect()->route('parties.index')->with('success', 'Cliente creado correctamente.');
    }

    public function edit(Party $party)
    {
        $party->load(['addresses', 'activeTariffSetting']);
        $tariffTables = TariffTable::active()->orderBy('name')->get();
        return view('parties.edit', compact('party', 'tariffTables'));
    }

    public function update(Request $request, Party $party)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'document' => 'nullable|string|max:100',
            'document_type' => 'nullable|string|max:50',
            'tax_status' => 'nullable|string|max:100',

            // Array of addresses
            'addresses' => 'array',
            'addresses.*.id' => 'nullable',
            'addresses.*.type' => 'required|string|max:50',
            'addresses.*.address_line1' => 'nullable|string|max:255',
            'addresses.*.city' => 'nullable|string|max:100',
            'addresses.*.state' => 'required|string|max:100',
            'addresses.*.zip_code' => 'nullable|string|max:20',
            'addresses.*.phone' => 'nullable|string|max:100',
            'addresses.*.email' => 'nullable|email|max:255',
            'addresses.*.is_primary' => 'nullable',
        ]);

        $party->update([
            'name'          => $validated['name'],
            'document'      => $validated['document']      ?? null,
            'document_type' => $validated['document_type'] ?? null,
            'tax_status'    => $validated['tax_status']    ?? null,
        ]);

        $existingAddressesIds = [];
        if ($request->has('addresses')) {
            $hasPrimary = collect($validated['addresses'])->contains('is_primary', true);

            foreach ($validated['addresses'] as $index => $addrData) {
                $isPrimary = $hasPrimary ? !empty($addrData['is_primary']) : ($index === 0);

                $address = $party->addresses()->updateOrCreate(
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
            }
        }

        // Remove deleted addresses
        $party->addresses()->whereNotIn('id', $existingAddressesIds)->delete();

        // Actualizar configuración tarifaria si se envió habilitada
        // Solo guarda si se marcó el checkbox (billing_mode tiene valor)
        if ($request->filled('tariff.billing_mode')) {
            $this->saveTariffSetting($party, $request->input('tariff'));
        }

        return redirect()->route('parties.index')->with('success', 'Cliente actualizado correctamente.');
    }

    /**
     * Devuelve la configuración tarifaria activa del cliente (AJAX).
     * Usada por el formulario de guías para calcular el flete automáticamente
     * cuando se selecciona el remitente o se modifican los ítems de carga.
     */
    public function tariffSetting(Party $party): \Illuminate\Http\JsonResponse
    {
        $setting = $party->activeTariffSetting;

        if (!$setting) {
            return response()->json(['has_tariff' => false]);
        }

        return response()->json([
            'has_tariff'        => true,
            'billing_mode'      => $setting->billing_mode,
            'billing_mode_label'=> $setting->billing_mode_label,
            'minimum_charge'    => (float) ($setting->minimum_charge    ?? 0),
            'rate_per_ton'      => (float) ($setting->rate_per_ton_custom ?? 0),
            'rate_per_m3'       => (float) ($setting->rate_per_m3_custom  ?? 0),
            'rate_per_bulto'    => (float) ($setting->rate_per_bulto      ?? 0),
            'minimum_per_bulto' => (float) ($setting->minimum_per_bulto   ?? 0),
            'rate_per_pallet'   => (float) ($setting->rate_per_pallet     ?? 0),
            'minimum_per_pallet'=> (float) ($setting->minimum_per_pallet  ?? 0),
            'declared_value_pct'=> (float) ($setting->declared_value_pct  ?? 0),
        ]);
    }

    public function destroy(Party $party)
    {
        // Las configuraciones tarifarias también se eliminan en cascada por FK
        $party->addresses()->delete();
        $party->delete();

        return redirect()->route('parties.index')->with('success', 'Cliente eliminado correctamente.');
    }


    /**
     * Guarda o actualiza la configuración tarifaria del cliente.
     * Usa updateOrCreate por party_id (un acuerdo por cliente).
     * El cuadro tarifario se determina en tiempo real por origen/destino de la guía.
     *
     * @param  Party  $party
     * @param  array  $tariffData  Datos del bloque tariff[] del formulario
     */
    private function saveTariffSetting(Party $party, array $tariffData): void
    {
        // Determinar el billing_mode final según los checkboxes de bultos/pallets
        $mode = $tariffData['billing_mode'] ?? '';

        if ($mode === 'bultos_pallets') {
            // Ambos seleccionados
            $finalMode = 'bultos_pallets';
        } else {
            $finalMode = $mode;
        }

        PartyTariffSetting::updateOrCreate(
            [
                // Un único acuerdo por cliente (sin filtrar por tariff_table_id)
                'party_id' => $party->id,
            ],
            [
                'tariff_table_id'     => null,
                'billing_mode'        => $finalMode,
                'minimum_charge'      => ($tariffData['minimum_charge']      ?? null) ?: null,
                'rate_per_ton_custom' => ($tariffData['rate_per_ton_custom']  ?? null) ?: null,
                'rate_per_m3_custom'  => ($tariffData['rate_per_m3_custom']   ?? null) ?: null,
                'rate_per_bulto'      => ($tariffData['rate_per_bulto']       ?? null) ?: null,
                'minimum_per_bulto'   => ($tariffData['minimum_per_bulto']    ?? null) ?: null,
                'rate_per_pallet'     => ($tariffData['rate_per_pallet']      ?? null) ?: null,
                'minimum_per_pallet'  => ($tariffData['minimum_per_pallet']   ?? null) ?: null,
                'declared_value_pct'  => ($tariffData['declared_value_pct']   ?? null) ?: null,
                'valid_from'          => $tariffData['valid_from']            ?? null,
                'valid_until'         => ($tariffData['valid_until']          ?? null) ?: null,
                'notes'               => ($tariffData['notes']               ?? null) ?: null,
            ]
        );
    }
}