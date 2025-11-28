<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->timestamp('sla_vencimiento')->nullable()->after('fecha_cierre');
            $table->boolean('sla_cumplido')->nullable()->after('sla_vencimiento');
        });
    }

    public function down()
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn(['sla_vencimiento', 'sla_cumplido']);
        });
    }
};