<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('numero_ticket', 20)->unique();
            $table->string('titulo', 200);
            $table->text('descripcion');
            $table->foreignId('usuario_id')->constrained('usuarios');
            $table->foreignId('tecnico_id')->nullable()->constrained('usuarios');
            $table->foreignId('categoria_id')->constrained('categorias');
            $table->foreignId('prioridad_id')->constrained('prioridades');
            $table->enum('estado', ['abierto', 'en_proceso', 'pendiente', 'resuelto', 'cerrado', 'cancelado'])->default('abierto');
            $table->timestamp('fecha_apertura')->useCurrent();
            $table->timestamp('fecha_asignacion')->nullable();
            $table->timestamp('fecha_primera_respuesta')->nullable();
            $table->timestamp('fecha_resolucion')->nullable();
            $table->timestamp('fecha_cierre')->nullable();
            $table->timestamp('fecha_limite_respuesta')->nullable();
            $table->timestamp('fecha_limite_resolucion')->nullable();
            $table->integer('tiempo_respuesta_minutos')->nullable();
            $table->integer('tiempo_resolucion_minutos')->nullable();
            $table->boolean('sla_respuesta_cumplido')->nullable();
            $table->boolean('sla_resolucion_cumplido')->nullable();
            $table->integer('numero_escalamientos')->default(0);
            $table->integer('calificacion')->nullable();
            $table->text('comentario_satisfaccion')->nullable();
            $table->timestamps();
            
            $table->index('numero_ticket');
            $table->index('estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
