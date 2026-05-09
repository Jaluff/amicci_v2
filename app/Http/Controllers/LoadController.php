<?php

namespace App\Http\Controllers;

use App\Http\Requests\Load\StoreLoadRequest;
use App\Http\Requests\Load\UpdateLoadRequest;
use App\Http\Requests\Load\InvoiceLoadRequest;
use App\Http\Requests\Load\PayLoadRequest;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Driver;
use App\Models\Load;
use App\Models\Party;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class LoadController extends Controller
{
    public function index(): View
    {
        $companies = Company::active()->orderBy('name')->get();
        $userCompanies = auth()->user()->companies;
        return view('loads.index', compact('companies', 'userCompanies'));
    }

    public function datatable(Request $request): mixed
    {
        $query = Load::with(['company', 'remitente', 'destinatario', 'origen', 'destino', 'driver'])->select('loads.*');

        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }
        if ($request->filled('numero')) {
            $query->where('numero', 'like', '%' . $request->numero . '%');
        }
        if ($request->filled('estado') && $request->estado !== 'Todos') {
            $query->where('estado', $request->estado);
        }
        if ($request->filled('facturada') && in_array($request->facturada, ['1', '0'])) {
            if ($request->facturada === '1') {
                $query->whereNotNull('numero_factura');
            } else {
                $query->whereNull('numero_factura');
            }
        }
        if ($request->filled('cobrada') && in_array($request->cobrada, ['1', '0'])) {
            if ($request->cobrada === '1') {
                $query->whereNotNull('numero_recibo');
            } else {
                $query->whereNull('numero_recibo');
            }
        }

        return DataTables::of($query->orderByDesc('id'))
            ->editColumn('fecha_carga', function ($row) {
                return \Carbon\Carbon::parse($row->fecha_carga)->format('d/m/Y');
            })
            ->addColumn('company_name', function ($row) {
                $color = $row->company?->color ?? '#6366f1';
                $prefix = $row->company?->prefix ?? '-';
                return "<span class='px-2 py-1 rounded-full text-[10px] font-bold text-white shadow-sm' style='background-color: {$color}'>{$prefix}</span>";
            })
            ->addColumn('remitente_upper', fn($row) => '<span class="font-bold text-gray-800 dark:text-gray-200">' . mb_strtoupper($row->remitente?->name ?? '-') . '</span>')
            ->addColumn('destinatario_upper', fn($row) => '<span class="font-bold text-gray-800 dark:text-gray-200">' . mb_strtoupper($row->destinatario?->name ?? '-') . '</span>')
            ->addColumn('ruta_corta', function ($row) {
                $or = mb_strtoupper(str_ireplace('SUCURSAL ', '', $row->origen?->name ?? '-'));
                $ds = mb_strtoupper(str_ireplace('SUCURSAL ', '', $row->destino?->name ?? '-'));
                return "<div class='flex flex-col gap-0.5 items-center'>
                            <span class='px-1.5 py-0.5 rounded bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300 text-[9px] font-bold w-fit'>$or</span>
                            <span class='px-1.5 py-0.5 rounded bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 text-[9px] font-bold w-fit'>$ds</span>
                        </div>";
            })
            ->addColumn('chofer_name', fn($row) => $row->driver?->name ?? '-')
            ->addColumn('facturada_badge', fn($row) => $row->facturada
                ? "<div class='flex justify-center'><span class='dt-badge dt-badge-green'>Sí</span></div>"
                : "<div class='flex justify-center'><span class='dt-badge dt-badge-red'>No</span></div>")
            ->addColumn('cobrada_badge', fn($row) => $row->cobrada
                ? "<div class='flex justify-center'><span class='dt-badge dt-badge-green'>Sí</span></div>"
                : "<div class='flex justify-center'><span class='dt-badge dt-badge-red'>No</span></div>")
            ->addColumn('estado_badge', function ($row) {
                $statusColors = [
                    'Preparado' => 'dt-badge-blue',
                    'En viaje'  => 'dt-badge-yellow',
                    'Arribado'  => 'dt-badge-green',
                ];
                $color = $statusColors[$row->estado] ?? 'dt-badge-gray';
                return "<div class='flex justify-center'><span class='dt-badge {$color}'>{$row->estado}</span></div>";
            })
            ->addColumn('importe', function ($row) {
                if (!$row->importe_factura) {
                    return "<span class='text-gray-400 text-xs'>—</span>";
                }
                return "<span class='font-semibold text-gray-800 dark:text-gray-200'>$" . number_format($row->importe_factura, 2, ',', '.') . "</span>";
            })
            ->addColumn('actions', function ($row) {
                return view('loads._actions', ['load' => $row])->render();
            })
            ->rawColumns(['company_name', 'remitente_upper', 'destinatario_upper', 'ruta_corta', 'facturada_badge', 'cobrada_badge', 'estado_badge', 'importe', 'actions'])
            ->make(true);
    }

    public function create(Request $request): View|\Illuminate\Http\RedirectResponse
    {
        $user = auth()->user();
        $companies = $user->companies;

        if ($companies->isEmpty()) {
            abort(403, 'No tienes ninguna empresa asignada.');
        }

        $company_id = $request->input('company_id');

        if (!$company_id) {
            if ($companies->count() === 1) {
                $company_id = $companies->first()->id;
            } else {
                return redirect()->route('loads.index')->with('error', 'Debes seleccionar una empresa primero.');
            }
        } elseif (!$companies->contains('id', $company_id)) {
            abort(403, 'No tienes permiso para operar en esta empresa.');
        }

        $selected_company = $companies->firstWhere('id', $company_id);

        // Pasamos todas las companies al compact para compatibilidad con _form,
        // pero la vista forzará el selected_company
        $companies = Company::active()->orderBy('name')->get(); 
        $parties = Party::orderBy('name')->get();
        $branches = Branch::orderBy('name')->get();
        $drivers = Driver::orderBy('name')->get();

        return view('loads.create', compact('companies', 'parties', 'branches', 'drivers', 'selected_company', 'company_id'));
    }

    public function store(StoreLoadRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $load = DB::transaction(function () use ($data) {
            $company = \App\Models\Company::lockForUpdate()->findOrFail($data['company_id']);
            $company->last_load_number++;
            $company->save();

            $branchId = $data['origen_id'] ?? 0;
            $numeroCarga = sprintf('%s-%d-C-%08d', $company->prefix, $branchId, $company->last_load_number);

            return Load::create([
                ...$data,
                'numero' => $numeroCarga,
                'estado' => \App\StateMachines\LoadStateMachine::STATUS_PREPARADO,
            ]);
        });

        return redirect()->route('loads.index')->with('success', "Carga {$load->numero} creada exitosamente.");
    }

    public function edit(Load $load): View
    {
        $companies = Company::active()->orderBy('name')->get();
        $parties = Party::orderBy('name')->get();
        $branches = Branch::orderBy('name')->get();
        $drivers = Driver::orderBy('name')->get();

        return view('loads.edit', compact('load', 'companies', 'parties', 'branches', 'drivers'));
    }

    public function update(UpdateLoadRequest $request, Load $load): RedirectResponse
    {
        $load->update($request->validated());

        return redirect()->route('loads.index')->with('success', "Carga {$load->numero} actualizada.");
    }

    public function destroy(Load $load): RedirectResponse
    {
        $load->delete();
        return redirect()->route('loads.index')->with('success', "Carga eliminada.");
    }

    // ── Endpoint para Facturar ──
    public function invoice(InvoiceLoadRequest $request, Load $load): RedirectResponse
    {
        $load->update([
            'fecha_factura' => $request->validated('fecha_factura'),
            'numero_factura' => $request->validated('numero_factura'),
            'importe_factura' => $request->validated('importe_factura'),
        ]);

        return back()->with('success', "Factura registrada en la carga {$load->numero}.");
    }

    // ── Endpoint para Cobrar ──
    public function pay(PayLoadRequest $request, Load $load): RedirectResponse
    {
        if (!$load->facturada) {
            return back()->withErrors(['msg' => 'No se puede cobrar una carga que no ha sido facturada.']);
        }

        $load->update([
            'fecha_recibo' => $request->validated('fecha_recibo'),
            'numero_recibo' => $request->validated('numero_recibo'),
        ]);

        return back()->with('success', "Cobro registrado en la carga {$load->numero}.");
    }

    // ── Endpoint para Cambiar Estado ──
    public function changeState(Request $request, Load $load): RedirectResponse
    {
        $request->validate(['status' => 'required|string']);
        $newStatus = $request->status;

        $sm = $load->stateMachine();

        if (!$sm->canTransitionTo($sm->currentStatus(), $newStatus)) {
            return back()->withErrors(['msg' => "No se puede cambiar el estado de {$sm->currentStatus()} a {$newStatus}."]);
        }

        $sm->transitionTo($newStatus);

        return back()->with('success', "Estado actualizado a {$newStatus}.");
    }
}
