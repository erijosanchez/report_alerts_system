<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('monitoreo_logs', function (Blueprint $table) {
            $table->string('estado_anterior', 20)->nullable()->after('resultado');
            $table->string('estado_nuevo', 20)->nullable()->after('estado_anterior');
            $table->boolean('cambio_estado')->default(false)->after('estado_nuevo');
            $table->string('ticket_generado', 10)->nullable()->after('cambio_estado');
            $table->integer('tickets_cerrados')->nullable()->after('ticket_generado');
        });
    }

    public function down()
    {
        Schema::table('monitoreo_logs', function (Blueprint $table) {
            $table->dropColumn([
                'estado_anterior',
                'estado_nuevo', 
                'cambio_estado',
                'ticket_generado',
                'tickets_cerrados'
            ]);
        });
    }
};