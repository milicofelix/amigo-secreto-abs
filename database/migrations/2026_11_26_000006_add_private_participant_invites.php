<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->timestamp('invite_expires_at')->nullable();
            $table->timestamp('first_revealed_at')->nullable();
            $table->boolean('draw_email_enabled')->default(true);
        });

        DB::table('participants')->whereNotNull('pwa_access_hash')
            ->update(['invite_expires_at' => now()->addDays(30)]);

        DB::table('participants')->whereExists(function ($query) {
            $query->selectRaw('1')->from('secret_reveals')
                ->whereColumn('secret_reveals.participant_id', 'participants.id')
                ->whereNotNull('revealed_at');
        })->update(['first_revealed_at' => DB::raw(
            '(SELECT MIN(revealed_at) FROM secret_reveals WHERE secret_reveals.participant_id = participants.id)'
        )]);
    }

    public function down(): void
    {
        Schema::table('participants', fn (Blueprint $table) => $table->dropColumn([
            'invite_expires_at', 'first_revealed_at', 'draw_email_enabled',
        ]));
    }
};
