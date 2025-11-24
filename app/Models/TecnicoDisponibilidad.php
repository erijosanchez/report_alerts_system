<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TecnicoDisponibilidad extends Model
{
    protected $table = 'tecnicos_disponibilidad';

    protected $fillable = [
        'tecnico_id',
        'disponible',
        'tickets_asignados',
        'capacidad_maxima',
        'especialidades',
        'nivel_prioridad',
        'ultima_asignacion'
    ];

    protected $casts = [
        'disponible' => 'boolean',
        'especialidades' => 'array',
        'ultima_asignacion' => 'datetime',
    ];

    public function tecnico()
    {
        return $this->belongsTo(Usuario::class, 'tecnico_id');
    }

    public function puedeRecibirTickets()
    {
        return $this->disponible && $this->tickets_asignados < $this->capacidad_maxima;
    }

    public function incrementarTicketsAsignados()
    {
        $this->increment('tickets_asignados');
        $this->update(['ultima_asignacion' => now()]);
    }

    public function decrementarTicketsAsignados()
    {
        if ($this->tickets_asignados > 0) {
            $this->decrement('tickets_asignados');
        }
    }
}
