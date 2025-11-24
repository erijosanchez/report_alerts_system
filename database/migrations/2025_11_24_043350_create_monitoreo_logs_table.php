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
        Schema::create('monitoreo_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sede_id')->constrained('sedes')->onDelete('cascade');
            $table->string('tipo_chequeo'); // ping, http, servicio
            $table->string('resultado'); // success, failed, timeout
            $table->integer('tiempo_respuesta_ms')->nullable();
            $table->text('detalles')->nullable();
            $table->timestamp('fecha_chequeo');
            $table->timestamps();
            
            $table->index(['sede_id', 'fecha_chequeo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monitoreo_logs');
    }
};
