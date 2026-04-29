<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\TariffBracket;
use App\Models\TariffTable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class TariffTableController extends Controller
{
    /**
     * Lista todos los cuadros tarifarios en una DataTable.
     */
    public function index(): View
    {
        return view('tariffTables.index');
    }

    /**
     * Fuente de datos para la DataTable (AJAX).
     */
    public function datatable()
    {
        $query = TariffTable::with(['origin', 'destination'])->withCount('brackets');

        return DataTables::of($query)
            ->addColumn('ruta', fn($row) => ($row->origin?->nombre ?? '?') . ' → ' . ($row->destination?->nombre ?? '?'))
            ->addColumn('rate_per_ton_fmt', fn($row) => '$ ' . number_format((float) $row->rate_per_ton, 2, ',', '.'))
            ->addColumn('rate_per_m3_fmt', fn($row) => '$ ' . number_format((float) $row->rate_per_m3, 2, ',', '.'))
            ->addColumn('vigencia', function ($row) {
                $desde = $row->valid_from->format('d/m/Y');
                $hasta = $row->valid_until ? $row->valid_until->format('d/m/Y') : 'Sin vencimiento';
                return "{$desde} → {$hasta}";
            })
            ->addColumn('estado', function ($row) {
                return $row->is_active
                    ? '<span class="badge-status badge-active">Activo</span>'
                    : '<span class="badge-status badge-inactive">Inactivo</span>';
            })
            ->addColumn('acciones', function ($row) {
                $editUrl   = route('tariff-tables.edit', $row->id);
                $deleteUrl = route('tariff-tables.destroy', $row->id);
                $csrf      = csrf_token();

                return "
                    <div class='flex items-center gap-2'>
                        <a href='{$editUrl}' title='Editar' class='inline-flex items-center justify-center p-2 rounded-md bg-blue-50 text-blue-700 border border-blue-200 hover:bg-blue-100 dark:bg-blue-900/40 dark:text-blue-400 dark:border-blue-800 dark:hover:bg-blue-800/60 transition-colors'>
                            <svg xmlns='http://www.w3.org/2000/svg' class='w-4 h-4' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><path d='M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7'/><path d='M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z'/></svg>
                        </a>
                        <form action='{$deleteUrl}' method='POST' onsubmit='return confirm(\"¿Eliminar este cuadro tarifario? Se eliminarán también sus tramos.\")' class='inline m-0'>
                            <input type='hidden' name='_token' value='{$csrf}'>
                            <input type='hidden' name='_method' value='DELETE'>
                            <button type='submit' title='Eliminar' class='inline-flex items-center justify-center p-2 rounded-md bg-red-50 text-red-700 border border-red-200 hover:bg-red-100 dark:bg-red-900/40 dark:text-red-400 dark:border-red-800 dark:hover:bg-red-800/60 transition-colors'>
                                <svg xmlns='http://www.w3.org/2000/svg' class='w-4 h-4' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><polyline points='3 6 5 6 21 6'/><path d='M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6'/><path d='M10 11v6'/><path d='M14 11v6'/><path d='M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2'/></svg>
                            </button>
                        </form>
                    </div>";
            })
            ->rawColumns(['estado', 'acciones'])
            ->make(true);
    }

    /**
     * Formulario para crear un nuevo cuadro tarifario.
     */
    public function create(): View
    {
        $ubicaciones = \App\Models\Ubicacion::orderBy('nombre')->get();
        return view('tariffTables.create', compact('ubicaciones'));
    }

    /**
     * Guarda un nuevo cuadro tarifario con sus tramos.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'origin_id'      => 'required|exists:ubicaciones,id',
            'destination_id' => 'required|exists:ubicaciones,id',
            'rate_per_ton'   => 'required|numeric|min:0',
            'rate_per_m3'    => 'required|numeric|min:0',
            'valid_from'     => 'required|date',
            'valid_until'    => 'nullable|date|after:valid_from',
            'is_active'      => 'boolean',
            'contra_reembolso_percent' => 'nullable|numeric|min:0|max:100',

            // Tramos de peso (deben siempre enviarse al menos uno)
            'brackets'              => 'required|array|min:1',
            'brackets.*.weight_from' => 'required|integer|min:1',
            'brackets.*.weight_to'   => 'required|integer|gt:brackets.*.weight_from',
            'brackets.*.rate'        => 'required|numeric|min:0',
        ]);

        $table = TariffTable::create([
            'name'           => $validated['name'],
            'origin_id'      => $validated['origin_id'],
            'destination_id' => $validated['destination_id'],
            'rate_per_ton'   => $validated['rate_per_ton'],
            'rate_per_m3'    => $validated['rate_per_m3'],
            'valid_from'     => $validated['valid_from'],
            'valid_until'    => $validated['valid_until'] ?? null,
            'is_active'      => $request->boolean('is_active', true),
            'contra_reembolso_percent' => $validated['contra_reembolso_percent'] ?? 0,
        ]);

        // Insertar todos los tramos enviados
        foreach ($validated['brackets'] as $bracket) {
            $table->brackets()->create([
                'weight_from' => $bracket['weight_from'],
                'weight_to'   => $bracket['weight_to'],
                'rate'        => $bracket['rate'],
            ]);
        }

        return redirect()->route('tariff-tables.index')
            ->with('success', 'Cuadro tarifario creado correctamente.');
    }

    /**
     * Formulario para editar un cuadro tarifario existente con sus tramos.
     */
    public function edit(TariffTable $tariffTable): View
    {
        $tariffTable->load('brackets');
        $ubicaciones = \App\Models\Ubicacion::orderBy('nombre')->get();

        return view('tariffTables.edit', compact('tariffTable', 'ubicaciones'));
    }

    /**
     * Actualiza el cuadro tarifario y sincroniza sus tramos.
     */
    public function update(Request $request, TariffTable $tariffTable): RedirectResponse
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'origin_id'      => 'required|exists:ubicaciones,id',
            'destination_id' => 'required|exists:ubicaciones,id',
            'rate_per_ton'   => 'required|numeric|min:0',
            'rate_per_m3'    => 'required|numeric|min:0',
            'valid_from'     => 'required|date',
            'valid_until'    => 'nullable|date|after:valid_from',
            'is_active'      => 'boolean',
            'contra_reembolso_percent' => 'nullable|numeric|min:0|max:100',

            'brackets'               => 'required|array|min:1',
            'brackets.*.weight_from' => 'required|integer|min:1',
            'brackets.*.weight_to'   => 'required|integer',
            'brackets.*.rate'        => 'required|numeric|min:0',
        ]);

        $tariffTable->update([
            'name'           => $validated['name'],
            'origin_id'      => $validated['origin_id'],
            'destination_id' => $validated['destination_id'],
            'rate_per_ton'   => $validated['rate_per_ton'],
            'rate_per_m3'    => $validated['rate_per_m3'],
            'valid_from'     => $validated['valid_from'],
            'valid_until'    => $validated['valid_until'] ?? null,
            'is_active'      => $request->boolean('is_active', true),
            'contra_reembolso_percent' => $validated['contra_reembolso_percent'] ?? 0,
        ]);

        // Reemplazar los tramos: borrar todos y reinsertar
        // (más simple y menos propenso a errores que un sync parcial)
        $tariffTable->brackets()->delete();

        foreach ($validated['brackets'] as $bracket) {
            $tariffTable->brackets()->create([
                'weight_from' => $bracket['weight_from'],
                'weight_to'   => $bracket['weight_to'],
                'rate'        => $bracket['rate'],
            ]);
        }

        return redirect()->route('tariff-tables.index')
            ->with('success', 'Cuadro tarifario actualizado correctamente.');
    }

    /**
     * Elimina un cuadro tarifario y sus tramos en cascada.
     */
    public function destroy(TariffTable $tariffTable): RedirectResponse
    {
        // Los brackets se eliminan en cascada por la FK con cascadeOnDelete()
        $tariffTable->delete();

        return redirect()->route('tariff-tables.index')
            ->with('success', 'Cuadro tarifario eliminado.');
    }
}
