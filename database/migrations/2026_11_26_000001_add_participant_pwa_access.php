<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->string('pwa_access_hash', 64)->nullable()->unique();
        });

        Schema::create('participant_push_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('participant_id')->constrained()->cascadeOnDelete();
            $table->string('endpoint_hash', 64)->unique();
            $table->text('subscription');
            $table->foreignId('last_notified_draw_id')->nullable()->constrained('as_draws')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('participant_push_subscriptions');
        Schema::table('participants', function (Blueprint $table) {
            $table->dropUnique(['pwa_access_hash']);
            $table->dropColumn('pwa_access_hash');
        });
    }
};
