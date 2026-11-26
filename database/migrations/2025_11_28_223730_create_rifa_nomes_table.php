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
        Schema::create('rifa_nomes', function (Blueprint $table) {
            $table->id();
             $table->foreignId('rifa_id')->constrained()->onDelete('cascade');
            $table->string('nome'); // igual aos nomes da cartela da imagem
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamp('comprado_em')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rifa_nomes');
    }
};
