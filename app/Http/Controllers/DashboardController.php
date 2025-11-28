<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ticket;
use Carbon\Carbon;
use App\Models\Categoria;
use App\Models\Prioridad;
use App\Models\Usuario;


class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Definir períodos de comparación
        $hoy = Carbon::now();
        $hace30 = $hoy->copy()->subDays(30);
        $hace60 = $hoy->copy()->subDays(60);

        // Query base según tipo de usuario
        $query = Ticket::query();

        if ($user->esCliente()) {
            $query->where('usuario_id', $user->id);
        } elseif ($user->esTecnico()) {
            $query->where('tecnico_id', $user->id);
        }

        // Estadísticas período actual (últimos 30 días)
        $queryActual = (clone $query)->where('fecha_apertura', '>=', $hace30);

        $stats = [
            'total' => $queryActual->count(),
            'abiertos' => (clone $queryActual)->where('estado', 'abierto')->count(),
            'en_proceso' => (clone $queryActual)->where('estado', 'en_proceso')->count(),
            'resueltos' => (clone $queryActual)->where('estado', 'resuelto')->count(),
            'cerrados' => (clone $queryActual)->where('estado', 'cerrado')->count(),
            'tiempo_respuesta_promedio' => (clone $queryActual)->avg('tiempo_respuesta_minutos') ?? 0,
            'tiempo_resolucion_promedio' => (clone $queryActual)->avg('tiempo_resolucion_minutos') ?? 0,
            'calificacion_promedio' => (clone $queryActual)->avg('calificacion') ?? 0,
        ];

        // Estadísticas período anterior (30-60 días atrás)
        $queryAnterior = (clone $query)
            ->whereBetween('fecha_apertura', [$hace60, $hace30]);

        $statsAnteriores = [
            'total' => $queryAnterior->count(),
            'abiertos' => (clone $queryAnterior)->where('estado', 'abierto')->count(),
            'en_proceso' => (clone $queryAnterior)->where('estado', 'en_proceso')->count(),
            'resueltos' => (clone $queryAnterior)->where('estado', 'resuelto')->count(),
            'tiempo_respuesta_promedio' => (clone $queryAnterior)->avg('tiempo_respuesta_minutos') ?? 0,
            'tiempo_resolucion_promedio' => (clone $queryAnterior)->avg('tiempo_resolucion_minutos') ?? 0,
            'calificacion_promedio' => (clone $queryAnterior)->avg('calificacion') ?? 0,
        ];

        // Calcular variaciones (porcentajes)
        $stats['variacion_total'] = $this->calcularVariacion($statsAnteriores['total'], $stats['total']);
        $stats['variacion_abiertos'] = $this->calcularVariacion($statsAnteriores['abiertos'], $stats['abiertos']);
        $stats['variacion_proceso'] = $this->calcularVariacion($statsAnteriores['en_proceso'], $stats['en_proceso']);
        $stats['variacion_resueltos'] = $this->calcularVariacion($statsAnteriores['resueltos'], $stats['resueltos']);

        // Variaciones en minutos (diferencia directa, no porcentaje)
        $stats['variacion_tiempo_respuesta'] = round($stats['tiempo_respuesta_promedio'] - $statsAnteriores['tiempo_respuesta_promedio']);
        $stats['variacion_tiempo_resolucion'] = round($stats['tiempo_resolucion_promedio'] - $statsAnteriores['tiempo_resolucion_promedio']);

        // Variación en calificación (diferencia directa)
        $stats['variacion_calificacion'] = round($stats['calificacion_promedio'] - $statsAnteriores['calificacion_promedio'], 1);

        $categorias = Categoria::activas()->get();
        $prioridades = Prioridad::ordenadoPorNivel()->get();
        $tecnicos = Usuario::where('rol', 'tecnico')->where('estado', true)->get();

        // Tickets recientes (todos los tickets, no solo los últimos 30 días)
        $tickets = (clone $query)
            ->with(['usuario', 'tecnico', 'categoria', 'prioridad'])
            ->latest('fecha_apertura')
            ->limit(10)
            ->get();

        return view('dashboard', compact('stats', 'tickets', 'categorias', 'prioridades', 'tecnicos'));
    }

    /**
     * Calcula el porcentaje de variación entre dos valores
     */
    private function calcularVariacion($anterior, $actual)
    {
        // Si no hay valor anterior, considerar 100% si hay actual, 0% si no
        if ($anterior == 0) {
            return $actual > 0 ? 100 : 0;
        }

        // Calcular porcentaje de cambio
        return round((($actual - $anterior) / $anterior) * 100);
    }
}
