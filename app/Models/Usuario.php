<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Str;

class Usuario extends Authenticatable
{
    use HasFactory;

    protected $table = 'usuarios';

    protected $fillable = [
        'uuid',
        'nombre',
        'apellido',
        'email',
        'password',
        'telefono',
        'rol',
        'estado',
        'foto_perfil'
    ];

    protected $hidden = ['password'];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($usuario) {
            $usuario->uuid = Str::uuid();
        });
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'usuario_id');
    }

    public function ticketsAsignados()
    {
        return $this->hasMany(Ticket::class, 'tecnico_id');
    }

    public function comentarios()
    {
        return $this->hasMany(Comentario::class);
    }

    public function esAdmin()
    {
        return $this->rol === 'admin';
    }

    public function esTecnico()
    {
        return $this->rol === 'tecnico';
    }

    public function esCliente()
    {
        return $this->rol === 'cliente';
    }

    public function esStaff()
    {
        return in_array($this->rol, ['admin', 'tecnico']);
    }
}
