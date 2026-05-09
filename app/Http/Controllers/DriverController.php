<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class DriverController extends Controller
{
    public function index()
    {
        return view('drivers.index');
    }

    public function datatable()
    {
        $query = Driver::query();

        return DataTables::of($query)
            ->addColumn('acciones', function ($row) {
                $editUrl = route('drivers.edit', $row->id);
                $deleteUrl = route('drivers.destroy', $row->id);
                $csrf = csrf_token();
                $confirm = 'return confirm(\'¿Eliminar este conductor?\')';

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
            ->rawColumns(['acciones'])
            ->make(true);
    }

    public function create()
    {
        return view('drivers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255|unique:drivers,email',
            'license_number' => 'nullable|string|max:100|unique:drivers,license_number',
            'dni' => 'nullable|string|max:50|unique:drivers,dni',
        ]);

        $driver = Driver::create($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'driver' => $driver,
                'message' => 'Conductor guardado correctamente.',
            ]);
        }

        return redirect()->route('drivers.index')->with('success', 'Conductor guardado correctamente.');
    }

    public function edit(Driver $driver)
    {
        return view('drivers.edit', compact('driver'));
    }

    public function update(Request $request, Driver $driver)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255|unique:drivers,email,'.$driver->id,
            'license_number' => 'nullable|string|max:100|unique:drivers,license_number,'.$driver->id,
            'dni' => 'nullable|string|max:50|unique:drivers,dni,'.$driver->id,
        ]);

        $driver->update($validated);

        return redirect()->route('drivers.index')->with('success', 'Conductor actualizado correctamente.');
    }

    public function destroy(Driver $driver)
    {
        $driver->delete();

        return redirect()->route('drivers.index')->with('success', 'Conductor eliminado correctamente.');
    }
}
