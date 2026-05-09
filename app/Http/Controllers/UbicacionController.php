<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUbicacionRequest;
use App\Http\Requests\UpdateUbicacionRequest;
use App\Models\Branch;
use App\Models\Ubicacion;
use App\Services\UbicacionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class UbicacionController extends Controller
{
    public function __construct(
        protected UbicacionService $ubicacionService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $ubicaciones = $this->ubicacionService->getAll();
        $branches = Branch::orderBy('name')->get();

        return view('ubicaciones.index', compact('ubicaciones', 'branches'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUbicacionRequest $request): RedirectResponse
    {
        $this->ubicacionService->store($request->validated());

        return redirect()->route('ubicaciones.index')->with('success', 'Ubicación creada correctamente.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUbicacionRequest $request, Ubicacion $ubicacione): RedirectResponse
    {
        $this->ubicacionService->update($ubicacione, $request->validated());

        return redirect()->route('ubicaciones.index')->with('success', 'Ubicación actualizada correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Ubicacion $ubicacione): RedirectResponse
    {
        try {
            $this->ubicacionService->delete($ubicacione);

            return redirect()->route('ubicaciones.index')->with('success', 'Ubicación eliminada correctamente.');
        } catch (\Exception $e) {
            return redirect()->route('ubicaciones.index')->with('error', $e->getMessage());
        }
    }
}
