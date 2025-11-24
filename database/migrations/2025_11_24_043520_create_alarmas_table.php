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
        Schema::create('alarmas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('tickets')->onDelete('cascade');
            $table->string('tipo_alarma'); // sla_vencido, escalamiento, caida_sistema
            $table->enum('nivel', ['info', 'warning', 'critical'])->default('warning');
            $table->string('titulo');
            $table->text('mensaje');
            $table->boolean('enviada')->default(false);
            $table->timestamp('fecha_envio')->nullable();
            $table->json('canales')->nullable(); // ['email', 'whatsapp', 'sms']
            $table->json('destinatarios')->nullable();
            $table->boolean('activa')->default(true);
            $table->timestamps();
            
            $table->index(['ticket_id', 'tipo_alarma']);
            $table->index('enviada');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alarmas');
    }
};
