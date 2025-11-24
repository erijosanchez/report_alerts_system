<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonitoreoLog extends Model
{
    protected $fillable = [
        'sede_id',
        'tipo_chequeo',
        'resultado',
        'tiempo_respuesta_ms',
        'detalles',
        'fecha_chequeo'
    ];

    protected $casts = [
        'fecha_chequeo' => 'datetime',
        'tiempo_respuesta_ms' => 'integer'
    ];

    // Relación con Sede
    public function sede()
    {
        return $this->belongsTo(Sede::class);
    }

    // Método para determinar si el chequeo fue exitoso
    public function fueExitoso()
    {
        return $this->resultado === 'success';
    }

    // Método para obtener logs por sede y fecha
    public static function logsPorSede($sedeId, $desde = null, $hasta = null)
    {
        $query = self::where('sede_id', $sedeId);

        if ($desde) {
            $query->where('fecha_chequeo', '>=', $desde);
        }

        if ($hasta) {
            $query->where('fecha_chequeo', '<=', $hasta);
        }

        return $query->orderBy('fecha_chequeo', 'desc')->get();
    }

    // Método para calcular uptime de una sede
    public static function calcularUptime($sedeId, $dias = 30)
    {
        $desde = now()->subDays($dias);

        $total = self::where('sede_id', $sedeId)
            ->where('fecha_chequeo', '>=', $desde)
            ->count();

        if ($total == 0) return 0;

        $exitosos = self::where('sede_id', $sedeId)
            ->where('fecha_chequeo', '>=', $desde)
            ->where('resultado', 'success')
            ->count();

        return round(($exitosos / $total) * 100, 2);
    }

    // Método para obtener promedio de tiempo de respuesta
    public static function promedioTiempoRespuesta($sedeId, $dias = 7)
    {
        $desde = now()->subDays($dias);

        return self::where('sede_id', $sedeId)
            ->where('fecha_chequeo', '>=', $desde)
            ->where('resultado', 'success')
            ->avg('tiempo_respuesta_ms');
    }
}
