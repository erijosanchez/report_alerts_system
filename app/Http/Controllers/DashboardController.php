<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ticket;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Estadísticas
        $query = Ticket::query();

        if ($user->esCliente()) {
            $query->where('usuario_id', $user->id);
        } elseif ($user->esTecnico()) {
            $query->where('tecnico_id', $user->id);
        }

        $stats = [
            'total' => $query->count(),
            'abiertos' => (clone $query)->where('estado', 'abierto')->count(),
            'en_proceso' => (clone $query)->where('estado', 'en_proceso')->count(),
            'resueltos' => (clone $query)->where('estado', 'resuelto')->count(),
            'cerrados' => (clone $query)->where('estado', 'cerrado')->count(),
            'tiempo_respuesta_promedio' => (clone $query)->avg('tiempo_respuesta_minutos'),
            'tiempo_resolucion_promedio' => (clone $query)->avg('tiempo_resolucion_minutos'),
            'calificacion_promedio' => (clone $query)->avg('calificacion'),
        ];

        // Tickets recientes
        $tickets = (clone $query)
            ->with(['usuario', 'tecnico', 'categoria', 'prioridad'])
            ->latest('fecha_apertura')
            ->limit(10)
            ->get();

        return view('dashboard', compact('stats', 'tickets'));
    }
}
