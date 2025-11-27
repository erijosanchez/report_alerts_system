<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sede;
use App\Models\MonitoreoLog;
use Carbon\Carbon;

class SedeController extends Controller
{
    public function index(Request $request)
    {
        //$this->authorize('gestionar-sedes');

        $query = Sede::query();

        // Búsqueda
        if ($request->filled('busqueda')) {
            $query->where(function ($q) use ($request) {
                $q->where('nombre', 'like', '%' . $request->busqueda . '%')
                    ->orWhere('codigo', 'like', '%' . $request->busqueda . '%')
                    ->orWhere('ciudad', 'like', '%' . $request->busqueda . '%');
            });
        }

        $sedes = $query->orderBy('nombre')->paginate(10);

        // Calcular métricas para cada sede
        $hace30Dias = Carbon::now()->subDays(30);
        foreach ($sedes as $sede) {
            // Uptime últimos 30 días
            $sedeChecks = MonitoreoLog::where('sede_id', $sede->id)
                ->where('fecha_chequeo', '>=', $hace30Dias)
                ->get();

            $sedeSuccessChecks = $sedeChecks->where('resultado', 'success')->count();
            $sede->uptime_30d = $sedeChecks->count() > 0
                ? round(($sedeSuccessChecks / $sedeChecks->count()) * 100, 1)
                : 0;

            // Última verificación
            $ultimoLog = MonitoreoLog::where('sede_id', $sede->id)
                ->latest('fecha_chequeo')
                ->first();

            if ($ultimoLog) {
                $sede->ultima_verificacion = $ultimoLog->fecha_chequeo;
            }
        }

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
            'ip_monitoreo' => 'required|ip',
            'ip_backup' => 'nullable|ip',
            'intervalo_monitoreo' => 'required|integer|min:1|max:60',
        ]);

        $validated['activa'] = true;
        $validated['estado_conexion'] = 'pendiente'; // Estado inicial

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
            'ip_monitoreo' => 'required|ip',
            'ip_backup' => 'nullable|ip',
            'intervalo_monitoreo' => 'required|integer|min:1|max:60',
            'activa' => 'boolean',
        ]);

        $sede->update($validated);

        return back()->with('success', 'Sede actualizada correctamente');
    }

    public function toggleEstado($id)
    {
        //$this->authorize('gestionar-sedes');

        $sede = Sede::findOrFail($id);
        $sede->update(['activa' => !$sede->activa]);

        $mensaje = $sede->activa ? 'Sede activada correctamente' : 'Sede desactivada correctamente';

        return back()->with('success', $mensaje);
    }
}
