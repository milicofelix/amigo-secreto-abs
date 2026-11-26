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
        Schema::table('rifa_nomes', function (Blueprint $table) {
            $table->string('comprador_nome')->nullable()->after('user_id');
            $table->string('comprador_whatsapp')->nullable()->after('comprador_nome');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rifa_nomes', function (Blueprint $table) {
            $table->dropColumn(['comprador_nome', 'comprador_whatsapp']);
        });
    }
};
