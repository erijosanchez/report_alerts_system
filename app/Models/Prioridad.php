<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Prioridad extends Model
{
    use HasFactory;

    protected $table = 'prioridades';

    protected $fillable = [
        'nombre', 'nivel', 'tiempo_respuesta_horas', 
        'tiempo_resolucion_horas', 'color', 'descripcion'
    ];

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function scopeOrdenadoPorNivel($query)
    {
        return $query->orderBy('nivel');
    }
}
