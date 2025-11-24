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
        Schema::create('sedes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('codigo', 20)->unique();
            $table->string('direccion');
            $table->string('ciudad', 100);
            $table->string('ip_principal');
            $table->string('ip_backup')->nullable();
            $table->json('servicios_monitorear')->nullable(); // ['web', 'erp', 'pos']
            $table->integer('intervalo_monitoreo')->default(5); // minutos
            $table->boolean('activa')->default(true);
            $table->timestamp('ultima_comprobacion')->nullable();
            $table->string('estado_conexion')->default('desconocido'); // online, offline, degradado
            $table->timestamps();
            
            $table->index('codigo');
            $table->index('estado_conexion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sedes');
    }
};
