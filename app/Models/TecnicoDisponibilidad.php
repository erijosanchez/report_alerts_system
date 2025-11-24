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
        'tickets_asignados' => 'integer',
        'capacidad_maxima' => 'integer',
        'especialidades' => 'array',
        'nivel_prioridad' => 'integer',
        'ultima_asignacion' => 'datetime'
    ];

    // Relación con Usuario (Técnico)
    public function tecnico()
    {
        return $this->belongsTo(Usuario::class, 'tecnico_id');
    }

    // Verificar si el técnico está disponible
    public function estaDisponible()
    {
        return $this->disponible && 
               $this->tickets_asignados < $this->capacidad_maxima;
    }

    // Incrementar contador de tickets asignados
    public function incrementarAsignaciones()
    {
        $this->increment('tickets_asignados');
        $this->update(['ultima_asignacion' => now()]);
        
        // Si llegó al límite, marcar como no disponible
        if ($this->tickets_asignados >= $this->capacidad_maxima) {
            $this->update(['disponible' => false]);
        }
    }

    // Decrementar contador cuando se cierra un ticket
    public function decrementarAsignaciones()
    {
        if ($this->tickets_asignados > 0) {
            $this->decrement('tickets_asignados');
            
            // Si estaba lleno y ahora tiene espacio, marcar como disponible
            if (!$this->disponible && $this->tickets_asignados < $this->capacidad_maxima) {
                $this->update(['disponible' => true]);
            }
        }
    }

    // Verificar si tiene especialidad
    public function tieneEspecialidad($especialidad)
    {
        return in_array($especialidad, $this->especialidades ?? []);
    }

    // Obtener técnicos disponibles ordenados por carga
    public static function obtenerTecnicosDisponibles($especialidad = null)
    {
        $query = self::where('disponible', true)
            ->where('tickets_asignados', '<', 'capacidad_maxima')
            ->with('tecnico')
            ->orderBy('tickets_asignados', 'asc')
            ->orderBy('nivel_prioridad', 'desc');
            
        if ($especialidad) {
            $query->whereJsonContains('especialidades', $especialidad);
        }
        
        return $query->get();
    }
}
