<?php

namespace App\Http\Controllers;

use App\Http\Requests\AjaxStorePartyRequest;
use App\Http\Requests\StorePartyRequest;
use App\Http\Requests\UpdatePartyRequest;
use App\Models\Party;
use App\Models\Shipment;
use App\Models\TariffTable;
use App\Services\PartyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class PartyController extends Controller
{
    public function __construct(
        private readonly PartyService $partyService
    ) {}

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
                if (! $addr) {
                    return '-';
                }

                return $addr->address_line1.($addr->city ? ', '.$addr->city : '');
            })
            ->addColumn('contacto', function ($row) {
                $addr = $row->primaryAddress;
                $contacto = [];
                // Check direct fields first
                if ($row->phone) {
                    $contacto[] = '📞 '.$row->phone;
                } elseif ($addr && $addr->phone) {
                    $contacto[] = '📞 '.$addr->phone;
                }

                if ($row->email) {
                    $contacto[] = '✉️ '.$row->email;
                } elseif ($addr && $addr->email) {
                    $contacto[] = '✉️ '.$addr->email;
                }

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

    public function store(StorePartyRequest $request)
    {
        $this->partyService->createParty($request->validated());

        return redirect()->route('parties.index')->with('success', 'Cliente creado correctamente.');
    }

    public function ajaxStore(AjaxStorePartyRequest $request)
    {
        $party = $this->partyService->ajaxCreateParty($request->validated());

        return response()->json([
            'success' => true,
            'party' => [
                'id' => $party->id,
                'name' => $party->name,
            ],
        ]);
    }

    public function ajaxSearch(Request $request): JsonResponse
    {
        $search = $request->input('q');

        $query = Party::withoutGlobalScope('company')->orderBy('name');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('document', 'like', "%{$search}%");
            });
        }

        $parties = $query->with('addresses')->limit(5)->get();

        $results = $parties->map(function ($party) {
            $hasAddress = $party->addresses->contains(function ($address) {
                return !empty(trim($address->address_line1));
            });

            return [
                'id' => $party->id,
                'text' => $party->name,
                'has_address' => $hasAddress,
            ];
        });

        return response()->json([
            'results' => $results,
        ]);
    }

    public function ajaxStoreAddress(Request $request, Party $party): JsonResponse
    {
        $validated = $request->validate([
            'address_line1' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'type' => 'nullable|string|max:255',
            'zip_code' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
        ]);

        $validated['type'] = $validated['type'] ?? 'Principal';
        $validated['is_primary'] = !$party->addresses()->where('is_primary', true)->exists();

        $address = $party->addresses()->create($validated);

        return response()->json([
            'success' => true,
            'address' => $address,
        ]);
    }

    public function edit(Party $party)
    {
        $party->load(['addresses', 'activeTariffSetting']);
        $tariffTables = TariffTable::active()->orderBy('name')->get();

        return view('parties.edit', compact('party', 'tariffTables'));
    }

    public function update(UpdatePartyRequest $request, Party $party)
    {
        $this->partyService->updateParty($party, $request->validated());

        return redirect()->route('parties.index')->with('success', 'Cliente actualizado correctamente.');
    }

    public function tariffSetting(Party $party): JsonResponse
    {
        $setting = $party->activeTariffSetting;

        if (! $setting) {
            return response()->json([
                'has_tariff' => false,
                'iva_percent' => (float) ($party->iva_percent ?? 0),
                'has_insurance' => (bool) $party->has_insurance,
                'insurance_percent' => (float) ($party->insurance_percent ?? 0),
            ]);
        }

        return response()->json([
            'has_tariff' => true,
            'iva_percent' => (float) ($party->iva_percent ?? 0),
            'has_insurance' => (bool) $party->has_insurance,
            'insurance_percent' => (float) ($party->insurance_percent ?? 0),
            'billing_mode' => $setting->billing_mode,
            'billing_mode_label' => $setting->billing_mode_label,
            'minimum_charge' => (float) ($setting->minimum_charge ?? 0),
            'rate_per_ton' => (float) ($setting->rate_per_ton_custom ?? $setting->tariffTable->rate_per_ton ?? 0),
            'rate_per_m3' => (float) ($setting->rate_per_m3_custom ?? $setting->tariffTable->rate_per_m3 ?? 0),
            'rate_per_bulto' => (float) ($setting->rate_per_bulto ?? 0),
            'minimum_per_bulto' => (float) ($setting->minimum_per_bulto ?? 0),
            'rate_per_pallet' => (float) ($setting->rate_per_pallet ?? 0),
            'minimum_per_pallet' => (float) ($setting->minimum_per_pallet ?? 0),
            'declared_value_pct' => (float) ($setting->declared_value_pct ?? 0),
        ]);
    }

    public function destroy(Party $party)
    {
        $hasShipments = Shipment::where('remitente_id', $party->id)
            ->orWhere('destinatario_id', $party->id)
            ->exists();

        if ($hasShipments) {
            return redirect()->route('parties.index')->with('error', 'No se puede eliminar el cliente porque ya está asociado a una o más guías de carga.');
        }

        // Las configuraciones tarifarias también se eliminan en cascada por FK
        $party->addresses()->delete();
        $party->delete();

        return redirect()->route('parties.index')->with('success', 'Cliente eliminado correctamente.');
    }
}
