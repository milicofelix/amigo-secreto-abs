<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['giver_id', 'receiver_id'] as $column) {
            if (DB::table('assignments')->select('draw_id', $column)->groupBy('draw_id', $column)
                ->havingRaw('COUNT(*) > 1')->limit(1)->get()->isNotEmpty()) {
                throw new RuntimeException('Existem pares duplicados no histórico. Revise os dados antes de aplicar a unicidade.');
            }
        }

        Schema::table('assignments', function (Blueprint $table) {
            $table->unique(['draw_id', 'giver_id']);
            $table->unique(['draw_id', 'receiver_id']);
        });
        Schema::table('groups', fn (Blueprint $table) => $table->dateTime('collective_reveal_at')->nullable());
        Schema::table('as_draws', fn (Blueprint $table) => $table->timestamp('cancelled_at')->nullable());

        Schema::create('draw_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('draw_id')->constrained('as_draws')->cascadeOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type', 40);
            $table->json('details')->nullable();
            $table->timestamp('created_at');
        });

        DB::table('draw_events')->insertUsing(
            ['draw_id', 'type', 'created_at'],
            DB::table('as_draws')->where('status', 'completed')
                ->selectRaw("id, 'confirmed', COALESCE(drawn_at, created_at, CURRENT_TIMESTAMP)")
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('draw_events');
        Schema::table('as_draws', fn (Blueprint $table) => $table->dropColumn('cancelled_at'));
        Schema::table('groups', fn (Blueprint $table) => $table->dropColumn('collective_reveal_at'));
        Schema::table('assignments', function (Blueprint $table) {
            $table->dropUnique(['draw_id', 'giver_id']);
            $table->dropUnique(['draw_id', 'receiver_id']);
        });
    }
};
