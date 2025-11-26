<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sede;

class SedeController extends Controller
{
    public function index()
    {
        //$this->authorize('gestionar-sedes');
        $sedes = Sede::orderBy('nombre')->get();
        return view('sedes.index', compact('sedes'));
    }

    public function store(Request $request)
    {
        //$this->authorize('gestionar-sedes');

        $validated = $request->validate([
            'nombre' => 'required|max:100',
            'codigo' => 'required|max:20|unique:sedes,codigo',
            'direccion' => 'required',
            'ciudad' => 'required|max:100',
            'ip_principal' => 'required|ip',
            'ip_backup' => 'nullable|ip',
            'intervalo_monitoreo' => 'required|integer|min:1|max:60',
        ]);

        Sede::create($validated);

        return back()->with('success', 'Sede creada correctamente');
    }

    public function update(Request $request, $id)
    {
        //$this->authorize('gestionar-sedes');

        $sede = Sede::findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'required|max:100',
            'direccion' => 'required',
            'ciudad' => 'required|max:100',
            'ip_principal' => 'required|ip',
            'ip_backup' => 'nullable|ip',
            'intervalo_monitoreo' => 'required|integer|min:1|max:60',
            'activa' => 'boolean',
        ]);

        $sede->update($validated);

        return back()->with('success', 'Sede actualizada correctamente');
    }
}
