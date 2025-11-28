<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'numero_ticket',
        'titulo',
        'descripcion',
        'usuario_id',
        'tecnico_id',
        'categoria_id',
        'prioridad_id',
        'estado',
        'fecha_apertura',
        'fecha_asignacion',
        'fecha_primera_respuesta',
        'fecha_resolucion',
        'fecha_cierre',
        'fecha_limite_respuesta',
        'fecha_limite_resolucion',
        'tiempo_respuesta_minutos',
        'tiempo_resolucion_minutos',
        'sla_respuesta_cumplido',
        'sla_resolucion_cumplido',
        'numero_escalamientos',
        'calificacion',
        'comentario_satisfaccion'
    ];

    protected $casts = [
        'fecha_apertura' => 'datetime',
        'fecha_asignacion' => 'datetime',
        'fecha_primera_respuesta' => 'datetime',
        'fecha_resolucion' => 'datetime',
        'fecha_cierre' => 'datetime',
        'fecha_limite_respuesta' => 'datetime',
        'fecha_limite_resolucion' => 'datetime',
        'sla_respuesta_cumplido' => 'boolean',
        'sla_resolucion_cumplido' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($ticket) {
            // Generar número de ticket
            $year = date('Y');
            $ultimo = self::where('numero_ticket', 'like', "TKT-{$year}-%")->latest('id')->first();
            $consecutivo = $ultimo ? intval(substr($ultimo->numero_ticket, -5)) + 1 : 1;
            $ticket->numero_ticket = sprintf("TKT-%s-%05d", $year, $consecutivo);

            // Calcular fechas límite SLA
            $prioridad = Prioridad::find($ticket->prioridad_id);
            if ($prioridad) {
                $ticket->fecha_limite_respuesta = now()->addHours($prioridad->tiempo_respuesta_horas);
                $ticket->fecha_limite_resolucion = now()->addHours($prioridad->tiempo_resolucion_horas);
            }
        });
    }

    // Relaciones
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function tecnico()
    {
        return $this->belongsTo(Usuario::class, 'tecnico_id');
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function prioridad()
    {
        return $this->belongsTo(Prioridad::class);
    }

    public function comentarios()
    {
        return $this->hasMany(Comentario::class);
    }

    // Métodos útiles
    public function asignarTecnico($tecnicoId)
    {
        $this->update([
            'tecnico_id' => $tecnicoId,
            'fecha_asignacion' => now(),
            'estado' => 'en_proceso'
        ]);
    }

    public function cambiarEstado($nuevoEstado)
    {
        $datos = ['estado' => $nuevoEstado];

        if ($nuevoEstado === 'resuelto' && !$this->fecha_resolucion) {
            $datos['fecha_resolucion'] = now();
            $datos['tiempo_resolucion_minutos'] = now()->diffInMinutes($this->fecha_apertura);
            $datos['sla_resolucion_cumplido'] = now() <= $this->fecha_limite_resolucion;
        }

        if ($nuevoEstado === 'cerrado' && !$this->fecha_cierre) {
            $datos['fecha_cierre'] = now();
        }

        $this->update($datos);
    }

    public function sede()
    {
        return $this->belongsTo(Sede::class, 'sede_id', 'id');
    }
}
