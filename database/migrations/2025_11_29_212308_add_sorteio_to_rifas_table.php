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
        Schema::table('rifas', function (Blueprint $table) {
             $table->string('ganhador_numero')->nullable();
            $table->string('ganhador_nome')->nullable();
            $table->string('ganhador_whatsapp')->nullable();
            $table->timestamp('sorteado_em')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rifas', function (Blueprint $table) {
            $table->dropColumn('ganhador_numero');
            $table->dropColumn('ganhador_nome');
            $table->dropColumn('ganhador_whatsapp');
            $table->dropColumn('sorteado_em');
        });
    }
};
