<?php

use App\Models\AmigoSecreto\Participant;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->text('invite_code')->nullable();
            $table->string('email_lookup_hash', 64)->nullable()->index();
            $table->string('invite_recovery_hash', 64)->nullable()->unique();
            $table->timestamp('invite_recovery_expires_at')->nullable();
        });

        foreach (Participant::whereNotNull('email')->select('id', 'email')->lazyById() as $participant) {
            if (filter_var($participant->email, FILTER_VALIDATE_EMAIL)) {
                DB::table('participants')->where('id', $participant->id)
                    ->update(['email_lookup_hash' => Participant::emailLookupHash($participant->email)]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->dropIndex(['email_lookup_hash']);
            $table->dropUnique(['invite_recovery_hash']);
            $table->dropColumn(['invite_code', 'email_lookup_hash', 'invite_recovery_hash', 'invite_recovery_expires_at']);
        });
    }
};
