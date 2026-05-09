<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Ubicacion;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Yajra\DataTables\Facades\DataTables;

class BranchController extends Controller
{
    public function index()
    {
        return view('branches.index');
    }

    public function datatable()
    {
        $query = Branch::query()
            ->with(['ubicacion', 'companies']);

        return DataTables::of($query)
            ->addColumn('acciones', function ($row) {
                $editUrl = route('branches.edit', $row->id);
                $deleteUrl = route('branches.destroy', $row->id);
                $csrf = csrf_token();
                $confirm = "return confirm('¿Eliminar esta sucursal?')";

                return "
                    <div class='flex items-center gap-2'>
                        <a href='{$editUrl}' title='Editar' class='inline-flex items-center justify-center p-2 rounded-md bg-blue-50 text-blue-700 border border-blue-200 hover:bg-blue-100 dark:bg-blue-900/40 dark:text-blue-400 dark:border-blue-800 dark:hover:bg-blue-800/60 transition-colors'>
                            <svg xmlns='http://www.w3.org/2000/svg' class='w-4 h-4' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><path d='M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7'/><path d='M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z'/></svg>
                        </a>
                        <form action='{$deleteUrl}' method='POST' onsubmit='{$confirm}' class='inline m-0'>
                            <input type='hidden' name='_token' value='{$csrf}'>
                            <input type='hidden' name='_method' value='DELETE'>
                            <button type='submit' title='Eliminar' class='inline-flex items-center justify-center p-2 rounded-md bg-red-50 text-red-700 border border-red-200 hover:bg-red-100 dark:bg-red-900/40 dark:text-red-400 dark:border-red-800 dark:hover:bg-red-800/60 transition-colors'>
                                <svg xmlns='http://www.w3.org/2000/svg' class='w-4 h-4' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><polyline points='3 6 5 6 21 6'/><path d='M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6'/><path d='M10 11v6'/><path d='M14 11v6'/><path d='M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2'/></svg>
                            </button>
                        </form>
                    </div>";
            })
            ->addColumn('ubicacion_nombre', fn ($row) => $row->ubicacion?->nombre ?? '—')
            ->addColumn('last_shipment_number', function ($row) {
                if ($row->companies->isEmpty()) {
                    return '—';
                }

                return $row->companies->map(function ($c) {
                    return "<span class='font-mono font-bold text-xs'>{$c->prefix}: {$c->pivot->last_shipment_number}</span>";
                })->implode('<br>');
            })
            ->addColumn('estado', fn ($row) => $row->active
                ? "<span class='dt-badge dt-badge-green'>Activa</span>"
                : "<span class='dt-badge dt-badge-gray'>Inactiva</span>")
            ->rawColumns(['acciones', 'estado', 'last_shipment_number'])
            ->make(true);
    }

    public function create()
    {
        $ubicaciones = Ubicacion::orderBy('nombre')->get();

        return view('branches.create', compact('ubicaciones'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|integer|min:1|max:99|unique:branches,code',
            'ubicacion_id' => 'required|integer|exists:ubicaciones,id',
            'active' => 'boolean',
        ]);

        $data['active'] = $request->boolean('active', true);

        Branch::create($data);

        return redirect()->route('branches.index')->with('success', 'Sucursal creada correctamente.');
    }

    public function edit(Branch $branch)
    {
        $ubicaciones = Ubicacion::orderBy('nombre')->get();

        return view('branches.edit', compact('branch', 'ubicaciones'));
    }

    public function update(Request $request, Branch $branch)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'code' => "required|integer|min:1|max:99|unique:branches,code,{$branch->id}",
            'ubicacion_id' => 'required|integer|exists:ubicaciones,id',
            'active' => 'boolean',
        ]);

        $data['active'] = $request->boolean('active', true);

        $branch->update($data);

        return redirect()->route('branches.index')->with('success', 'Sucursal actualizada correctamente.');
    }

    public function destroy(Branch $branch)
    {
        $branch->delete();

        return redirect()->route('branches.index')->with('success', 'Sucursal eliminada.');
    }
}
