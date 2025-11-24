<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\Sede;
use App\Models\Alarma;
use Illuminate\Support\Facades\DB;
use App\Models\Usuario;
use App\Models\MonitoreoLog;
use PDF;
use Carbon\Carbon;

class ReporteController extends Controller
{
    // Dashboard principal con estadísticas
    public function dashboard()
    {
        $stats = [
            'tickets_totales' => Ticket::count(),
            'tickets_abiertos' => Ticket::where('estado', 'abierto')->count(),
            'tickets_en_proceso' => Ticket::where('estado', 'en_proceso')->count(),
            'tickets_cerrados_hoy' => Ticket::where('estado', 'cerrado')
                ->whereDate('updated_at', today())->count(),
            
            'sedes_online' => Sede::where('estado_conexion', 'online')->count(),
            'sedes_offline' => Sede::where('estado_conexion', 'offline')->count(),
            'sedes_total' => Sede::count(),
            
            'alarmas_criticas' => Alarma::where('nivel', 'critical')
                ->where('enviada', false)->count(),
            'alarmas_hoy' => Alarma::whereDate('created_at', today())->count(),
            
            'tiempo_promedio_resolucion' => $this->calcularTiempoPromedioResolucion(),
            'sla_cumplimiento' => $this->calcularPorcentajeSLACumplido()
        ];

        $tickets_recientes = Ticket::with(['usuario', 'asignado', 'categoria', 'prioridad'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $sedes_estado = Sede::select('estado_conexion', \DB::raw('count(*) as total'))
            ->groupBy('estado_conexion')
            ->get();

        return view('dashboard', compact('stats', 'tickets_recientes', 'sedes_estado'));
    }

    // Reporte de tickets por período
    public function reporteTickets(Request $request)
    {
        $desde = $request->input('desde', now()->subMonth());
        $hasta = $request->input('hasta', now());

        $tickets = Ticket::whereBetween('created_at', [$desde, $hasta])
            ->with(['categoria', 'prioridad', 'asignado'])
            ->get();

        $resumen = [
            'total' => $tickets->count(),
            'por_estado' => $tickets->groupBy('estado')->map->count(),
            'por_categoria' => $tickets->groupBy('categoria.nombre')->map->count(),
            'por_prioridad' => $tickets->groupBy('prioridad.nombre')->map->count(),
            'tiempo_promedio' => $this->calcularTiempoPromedioResolucion($desde, $hasta),
            'tickets_generados_auto' => $tickets->where('generado_automaticamente', true)->count()
        ];

        return view('reportes.tickets', compact('tickets', 'resumen', 'desde', 'hasta'));
    }

    // Reporte de monitoreo de sedes
    public function reporteMonitoreo(Request $request)
    {
        $sedeId = $request->input('sede_id');
        $dias = $request->input('dias', 30);

        $sedes = Sede::with(['monitoreoLogs' => function($query) use ($dias) {
            $query->where('fecha_chequeo', '>=', now()->subDays($dias));
        }])->get();

        $estadisticas = [];
        foreach ($sedes as $sede) {
            $estadisticas[$sede->id] = [
                'nombre' => $sede->nombre,
                'uptime' => MonitoreoLog::calcularUptime($sede->id, $dias),
                'tiempo_respuesta_promedio' => MonitoreoLog::promedioTiempoRespuesta($sede->id, $dias),
                'total_incidentes' => $sede->monitoreoLogs->where('resultado', 'failed')->count(),
                'ultimo_chequeo' => $sede->ultima_comprobacion,
                'estado_actual' => $sede->estado_conexion
            ];
        }

        return view('reportes.monitoreo', compact('estadisticas', 'dias'));
    }

    // Reporte de alarmas
    public function reporteAlarmas(Request $request)
    {
        $desde = $request->input('desde', now()->subWeek());
        $hasta = $request->input('hasta', now());

        $alarmas = Alarma::whereBetween('created_at', [$desde, $hasta])
            ->with('ticket')
            ->orderBy('created_at', 'desc')
            ->get();

        $resumen = [
            'total' => $alarmas->count(),
            'por_nivel' => $alarmas->groupBy('nivel')->map->count(),
            'por_tipo' => $alarmas->groupBy('tipo_alarma')->map->count(),
            'enviadas' => $alarmas->where('enviada', true)->count(),
            'pendientes' => $alarmas->where('enviada', false)->count()
        ];

        return view('reportes.alarmas', compact('alarmas', 'resumen', 'desde', 'hasta'));
    }

    // Reporte de rendimiento de técnicos
    public function reporteTecnicos(Request $request)
    {
        $desde = $request->input('desde', now()->subMonth());
        $hasta = $request->input('hasta', now());

        $tecnicos = Usuario::where('rol', 'tecnico')
            ->with(['ticketsAsignados' => function($query) use ($desde, $hasta) {
                $query->whereBetween('created_at', [$desde, $hasta]);
            }])
            ->get();

        $estadisticas = [];
        foreach ($tecnicos as $tecnico) {
            $tickets = $tecnico->ticketsAsignados;
            
            $estadisticas[] = [
                'tecnico' => $tecnico->nombre . ' ' . $tecnico->apellido,
                'tickets_asignados' => $tickets->count(),
                'tickets_cerrados' => $tickets->where('estado', 'cerrado')->count(),
                'tickets_pendientes' => $tickets->whereIn('estado', ['abierto', 'en_proceso'])->count(),
                'tiempo_promedio_resolucion' => $this->calcularTiempoPromedioTecnico($tecnico->id, $desde, $hasta),
                'tasa_cumplimiento_sla' => $this->calcularTasaSLATecnico($tecnico->id, $desde, $hasta)
            ];
        }

        return view('reportes.tecnicos', compact('estadisticas', 'desde', 'hasta'));
    }

    // Exportar reporte a PDF
    public function exportarPDF($tipo, Request $request)
    {
        // Implementar exportación a PDF usando DomPDF o similar
        // Este es un ejemplo básico
        
        $pdf = \PDF::loadView('reportes.' . $tipo . '_pdf', [
            'datos' => $this->obtenerDatosReporte($tipo, $request)
        ]);

        return $pdf->download('reporte_' . $tipo . '_' . now()->format('Y-m-d') . '.pdf');
    }

    // Métodos auxiliares privados
    private function calcularTiempoPromedioResolucion($desde = null, $hasta = null)
    {
        $query = Ticket::where('estado', 'cerrado');
        
        if ($desde) $query->where('created_at', '>=', $desde);
        if ($hasta) $query->where('created_at', '<=', $hasta);
        
        $tickets = $query->get();
        
        if ($tickets->isEmpty()) return 0;
        
        $total_minutos = 0;
        foreach ($tickets as $ticket) {
            $total_minutos += $ticket->created_at->diffInMinutes($ticket->updated_at);
        }
        
        return round($total_minutos / $tickets->count() / 60, 2); // Retorna en horas
    }

    private function calcularPorcentajeSLACumplido()
    {
        $total = Ticket::where('estado', 'cerrado')->count();
        if ($total == 0) return 100;
        
        $cumplidos = Ticket::where('estado', 'cerrado')
            ->whereColumn('fecha_cierre', '<=', 'sla_vencimiento')
            ->count();
        
        return round(($cumplidos / $total) * 100, 2);
    }

    private function calcularTiempoPromedioTecnico($tecnicoId, $desde, $hasta)
    {
        $tickets = Ticket::where('asignado_a', $tecnicoId)
            ->where('estado', 'cerrado')
            ->whereBetween('created_at', [$desde, $hasta])
            ->get();
            
        if ($tickets->isEmpty()) return 0;
        
        $total_horas = 0;
        foreach ($tickets as $ticket) {
            $total_horas += $ticket->created_at->diffInHours($ticket->fecha_cierre);
        }
        
        return round($total_horas / $tickets->count(), 2);
    }

    private function calcularTasaSLATecnico($tecnicoId, $desde, $hasta)
    {
        $total = Ticket::where('asignado_a', $tecnicoId)
            ->where('estado', 'cerrado')
            ->whereBetween('created_at', [$desde, $hasta])
            ->count();
            
        if ($total == 0) return 100;
        
        $cumplidos = Ticket::where('asignado_a', $tecnicoId)
            ->where('estado', 'cerrado')
            ->whereBetween('created_at', [$desde, $hasta])
            ->whereColumn('fecha_cierre', '<=', 'sla_vencimiento')
            ->count();
        
        return round(($cumplidos / $total) * 100, 2);
    }
}
