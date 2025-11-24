<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sede;;
use App\Models\MonitoreoLog;

class MonitoreoController extends Controller
{
    public function index()
    {
        $this->authorize('ver-monitoreo');

        // Obtener todas las sedes con su último estado
        $sedes = Sede::with(['monitoreoLogs' => function ($q) {
            $q->latest('fecha_chequeo')->limit(1);
        }])->where('activa', true)->get();

        // Estadísticas de monitoreo
        $stats = [
            'total_sedes' => $sedes->count(),
            'sedes_online' => $sedes->where('estado_conexion', 'online')->count(),
            'sedes_offline' => $sedes->where('estado_conexion', 'offline')->count(),
            'sedes_degradado' => $sedes->where('estado_conexion', 'degradado')->count(),
        ];

        // Tickets automáticos activos
        $ticketsAutomaticos = \App\Models\Ticket::where('generado_automaticamente', true)
            ->whereIn('estado', ['abierto', 'en_proceso'])
            ->with(['sede', 'tecnico', 'prioridad'])
            ->get();

        return view('monitoreo.index', compact('sedes', 'stats', 'ticketsAutomaticos'));
    }

    public function detalleSede($id)
    {
        $this->authorize('ver-monitoreo');

        $sede = Sede::with(['tickets', 'monitoreoLogs'])->findOrFail($id);

        // Historial de monitoreo (últimas 24 horas)
        $historial = MonitoreoLog::where('sede_id', $id)
            ->where('fecha_chequeo', '>=', now()->subHours(24))
            ->orderBy('fecha_chequeo', 'desc')
            ->get();

        // Calcular uptime
        $totalChecks = $historial->count();
        $successChecks = $historial->where('resultado', 'success')->count();
        $uptime = $totalChecks > 0 ? ($successChecks / $totalChecks) * 100 : 0;

        return view('monitoreo.detalle', compact('sede', 'historial', 'uptime'));
    }

    public function forzarMonitoreo($id)
    {
        $this->authorize('gestionar-monitoreo');

        $sede = Sede::findOrFail($id);

        $monitoreoService = app(\App\Services\MonitoreoService::class);
        $monitoreoService->monitorearSede($sede);

        return back()->with('success', 'Monitoreo forzado ejecutado correctamente');
    }
}
