<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alarma extends Model
{
    protected $fillable = [
        'ticket_id', 'tipo_alarma', 'nivel', 'titulo', 'mensaje',
        'enviada', 'fecha_envio', 'canales', 'destinatarios', 'activa'
    ];

    protected $casts = [
        'enviada' => 'boolean',
        'activa' => 'boolean',
        'fecha_envio' => 'datetime',
        'canales' => 'array',
        'destinatarios' => 'array',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function marcarComoEnviada()
    {
        $this->update([
            'enviada' => true,
            'fecha_envio' => now()
        ]);
    }
}
