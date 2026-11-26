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
        Schema::table('participants', function (Blueprint $table) {
            $table->boolean('shared_gift_idea')->default(false); // Já postou o presente que quer
            $table->boolean('bought_gift')->default(false); // Já comprou o presente do amigo
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->dropColumn('shared_gift_idea');
            $table->dropColumn('bought_gift');
        });
    }
};
