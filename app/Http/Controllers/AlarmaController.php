<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Alarma;
use Carbon\Carbon;

class AlarmaController extends Controller
{
    public function index(Request $request)
    {
        //$this->authorize('ver-alarmas');
        
        $query = Alarma::with(['ticket.sede', 'ticket.tecnico', 'ticket.prioridad']);
        
        // Filtros
        if ($request->filled('tipo')) {
            switch ($request->tipo) {
                case 'criticas':
                    $query->where('nivel', 'critical')->where('activa', true);
                    break;
                case 'advertencias':
                    $query->where('nivel', 'warning')->where('activa', true);
                    break;
                case 'resueltas':
                    $query->where('activa', false);
                    break;
                case 'todas':
                default:
                    // No aplicar filtro
                    break;
            }
        }
        
        $alarmas = $query->orderBy('created_at', 'desc')->paginate(20);
        
        // Estadísticas
        $stats = [
            'criticas' => Alarma::where('nivel', 'critical')->where('activa', true)->count(),
            'advertencias' => Alarma::where('nivel', 'warning')->where('activa', true)->count(),
            'resueltas_hoy' => Alarma::where('activa', false)
                ->whereDate('updated_at', Carbon::today())
                ->count(),
            'tiempo_promedio' => $this->calcularTiempoPromedioResolucion(),
        ];
        
        return view('alarmas.index', compact('alarmas', 'stats'));
    }
    
    public function desactivar($id)
    {
        //$this->authorize('gestionar-alarmas');
        
        $alarma = Alarma::findOrFail($id);
        $alarma->update(['activa' => false]);
        
        return back()->with('success', 'Alarma marcada como resuelta');
    }

    /**
     * Calcular tiempo promedio de resolución de alarmas (en minutos)
     */
    private function calcularTiempoPromedioResolucion()
    {
        $alarmasResueltas = Alarma::where('activa', false)
            ->whereDate('updated_at', '>=', Carbon::today()->subDays(7))
            ->get();

        if ($alarmasResueltas->isEmpty()) {
            return 0;
        }

        $tiempoTotal = 0;
        foreach ($alarmasResueltas as $alarma) {
            $tiempoTotal += $alarma->created_at->diffInMinutes($alarma->updated_at);
        }

        return round($tiempoTotal / $alarmasResueltas->count());
    }
}