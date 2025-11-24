<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sede extends Model
{
    protected $fillable = [
        'nombre', 'codigo', 'direccion', 'ciudad', 'ip_principal', 'ip_backup',
        'servicios_monitorear', 'intervalo_monitoreo', 'activa', 
        'ultima_comprobacion', 'estado_conexion'
    ];

    protected $casts = [
        'servicios_monitorear' => 'array',
        'activa' => 'boolean',
        'ultima_comprobacion' => 'datetime',
    ];

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function monitoreoLogs()
    {
        return $this->hasMany(MonitoreoLog::class);
    }

    public function estaOnline()
    {
        return $this->estado_conexion === 'online';
    }

    public function marcarComoOffline()
    {
        $this->update([
            'estado_conexion' => 'offline',
            'ultima_comprobacion' => now()
        ]);
    }

    public function marcarComoOnline()
    {
        $this->update([
            'estado_conexion' => 'online',
            'ultima_comprobacion' => now()
        ]);
    }
}
