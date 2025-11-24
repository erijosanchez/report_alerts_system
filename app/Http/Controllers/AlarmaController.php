<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Alarma;

class AlarmaController extends Controller
{
    public function index()
    {
        $this->authorize('ver-alarmas');
        
        $alarmas = Alarma::with(['ticket.sede', 'ticket.tecnico'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        $stats = [
            'total' => Alarma::count(),
            'pendientes' => Alarma::where('enviada', false)->count(),
            'criticas' => Alarma::where('nivel', 'critical')->where('activa', true)->count(),
        ];
        
        return view('alarmas.index', compact('alarmas', 'stats'));
    }
    
    public function desactivar($id)
    {
        $this->authorize('gestionar-alarmas');
        
        $alarma = Alarma::findOrFail($id);
        $alarma->update(['activa' => false]);
        
        return back()->with('success', 'Alarma desactivada');
    }
}
