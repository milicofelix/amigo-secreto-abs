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
            $table->timestamp('shared_gift_idea_at')->nullable()->after('shared_gift_idea');
            $table->timestamp('bought_gift_at')->nullable()->after('bought_gift');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->dropColumn(['shared_gift_idea_at', 'bought_gift_at']);
        });
    }
};
