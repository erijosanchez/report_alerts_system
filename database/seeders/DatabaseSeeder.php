<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;


class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Prioridades
        DB::table('prioridades')->insert([
            ['nombre' => 'Baja', 'nivel' => 1, 'tiempo_respuesta_horas' => 48, 'tiempo_resolucion_horas' => 120, 'color' => '#28a745', 'descripcion' => 'Consultas generales'],
            ['nombre' => 'Media', 'nivel' => 2, 'tiempo_respuesta_horas' => 24, 'tiempo_resolucion_horas' => 72, 'color' => '#ffc107', 'descripcion' => 'Problemas con workaround'],
            ['nombre' => 'Alta', 'nivel' => 3, 'tiempo_respuesta_horas' => 8, 'tiempo_resolucion_horas' => 24, 'color' => '#fd7e14', 'descripcion' => 'Afecta operación normal'],
            ['nombre' => 'Crítica', 'nivel' => 4, 'tiempo_respuesta_horas' => 2, 'tiempo_resolucion_horas' => 8, 'color' => '#dc3545', 'descripcion' => 'Impide operación'],
        ]);

        // Categorías
        DB::table('categorias')->insert([
            ['nombre' => 'Soporte Técnico', 'descripcion' => 'Problemas técnicos', 'color' => '#007bff', 'icono' => 'wrench'],
            ['nombre' => 'Consulta General', 'descripcion' => 'Consultas', 'color' => '#6c757d', 'icono' => 'question'],
            ['nombre' => 'Reparación', 'descripcion' => 'Reparaciones', 'color' => '#dc3545', 'icono' => 'tools'],
            ['nombre' => 'Mantenimiento', 'descripcion' => 'Mantenimiento', 'color' => '#ffc107', 'icono' => 'cog'],
            ['nombre' => 'Garantía', 'descripcion' => 'Garantías', 'color' => '#17a2b8', 'icono' => 'shield'],
            ['nombre' => 'Otros', 'descripcion' => 'Otros', 'color' => '#6610f2', 'icono' => 'ellipsis'],
        ]);

        // Usuarios
        DB::table('usuarios')->insert([
            [
                'uuid' => Str::uuid(),
                'nombre' => 'Admin',
                'apellido' => 'Sistema',
                'email' => 'admin@trimax.com',
                'password' => Hash::make('admin123'),
                'telefono' => '+51999999999',
                'rol' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'uuid' => Str::uuid(),
                'nombre' => 'Carlos',
                'apellido' => 'Técnico',
                'email' => 'tecnico@trimax.com',
                'password' => Hash::make('tecnico123'),
                'telefono' => '+51987654321',
                'rol' => 'tecnico',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'uuid' => Str::uuid(),
                'nombre' => 'María',
                'apellido' => 'Cliente',
                'email' => 'cliente@example.com',
                'password' => Hash::make('cliente123'),
                'telefono' => '+51912345678',
                'rol' => 'cliente',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
