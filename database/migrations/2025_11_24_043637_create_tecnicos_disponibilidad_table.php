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
        Schema::create('tecnicos_disponibilidad', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tecnico_id')->constrained('usuarios');
            $table->boolean('disponible')->default(true);
            $table->integer('tickets_asignados')->default(0);
            $table->integer('capacidad_maxima')->default(10);
            $table->json('especialidades')->nullable(); // ['redes', 'hardware', 'software']
            $table->integer('nivel_prioridad')->default(1); // 1=junior, 2=senior, 3=experto
            $table->timestamp('ultima_asignacion')->nullable();
            $table->timestamps();
            
            $table->index('disponible');
            $table->index('tickets_asignados');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tecnicos_disponibilidad');
    }
};
