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
            ['nombre' => 'Caída de Sistema', 'descripcion' => 'Caídas automáticas', 'color' => '#dc3545', 'icono' => 'exclamation'],
        ]);

        // Usuarios
        $admin = DB::table('usuarios')->insertGetId([
            'uuid' => Str::uuid(),
            'nombre' => 'Admin',
            'apellido' => 'Sistema',
            'email' => 'admin@trimax.com',
            'password' => Hash::make('admin123'),
            'telefono' => '+51999999999',
            'rol' => 'admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $tecnico1 = DB::table('usuarios')->insertGetId([
            'uuid' => Str::uuid(),
            'nombre' => 'Carlos',
            'apellido' => 'Técnico',
            'email' => 'tecnico@trimax.com',
            'password' => Hash::make('tecnico123'),
            'telefono' => '+51987654321',
            'rol' => 'tecnico',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $tecnico2 = DB::table('usuarios')->insertGetId([
            'uuid' => Str::uuid(),
            'nombre' => 'Ana',
            'apellido' => 'Soporte',
            'email' => 'tecnico2@trimax.com',
            'password' => Hash::make('tecnico123'),
            'telefono' => '+51987654322',
            'rol' => 'tecnico',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('usuarios')->insert([
            'uuid' => Str::uuid(),
            'nombre' => 'María',
            'apellido' => 'Cliente',
            'email' => 'cliente@example.com',
            'password' => Hash::make('cliente123'),
            'telefono' => '+51912345678',
            'rol' => 'cliente',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Disponibilidad de técnicos
        DB::table('tecnicos_disponibilidad')->insert([
            [
                'tecnico_id' => $tecnico1,
                'disponible' => true,
                'tickets_asignados' => 0,
                'capacidad_maxima' => 10,
                'especialidades' => json_encode(['redes', 'hardware']),
                'nivel_prioridad' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tecnico_id' => $tecnico2,
                'disponible' => true,
                'tickets_asignados' => 0,
                'capacidad_maxima' => 8,
                'especialidades' => json_encode(['software', 'sistemas']),
                'nivel_prioridad' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Sedes de ejemplo
        DB::table('sedes')->insert([
            [
                'nombre' => 'Sede Principal - Lima',
                'codigo' => 'LIM01',
                'direccion' => 'Av. Principal 123, Lima',
                'ciudad' => 'Lima',
                'ip_principal' => '192.168.1.1',
                'ip_backup' => '192.168.1.2',
                'servicios_monitorear' => json_encode(['web', 'erp', 'pos']),
                'intervalo_monitoreo' => 5,
                'activa' => true,
                'estado_conexion' => 'online',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Sede Arequipa',
                'codigo' => 'AQP01',
                'direccion' => 'Calle Comercio 456, Arequipa',
                'ciudad' => 'Arequipa',
                'ip_principal' => '8.8.8.8',
                'ip_backup' => null,
                'servicios_monitorear' => json_encode(['web', 'pos']),
                'intervalo_monitoreo' => 5,
                'activa' => true,
                'estado_conexion' => 'online',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Sede Cusco',
                'codigo' => 'CUS01',
                'direccion' => 'Plaza de Armas, Cusco',
                'ciudad' => 'Cusco',
                'ip_principal' => '1.1.1.1',
                'ip_backup' => null,
                'servicios_monitorear' => json_encode(['web']),
                'intervalo_monitoreo' => 10,
                'activa' => true,
                'estado_conexion' => 'online',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
