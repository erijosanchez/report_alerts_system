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
        Schema::table('tickets', function (Blueprint $table) {
            $table->boolean('generado_automaticamente')->default(false);
            $table->string('origen_automatico')->nullable(); // 'monitoreo', 'escalamiento', 'api'
            $table->foreignId('sede_id')->nullable()->constrained('sedes');
            $table->boolean('asignacion_automatica')->default(true);
            $table->integer('intentos_escalamiento')->default(0);
            $table->timestamp('fecha_proximo_escalamiento')->nullable();
            $table->json('metadata_automatizacion')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn([
                'generado_automaticamente',
                'origen_automatico',
                'sede_id',
                'asignacion_automatica',
                'intentos_escalamiento',
                'fecha_proximo_escalamiento',
                'metadata_automatizacion'
            ]);
        });
    }
};
