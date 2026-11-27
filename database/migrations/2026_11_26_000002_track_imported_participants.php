<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->foreignId('imported_from_participant_id')->nullable()
                ->constrained('participants')->nullOnDelete();
            $table->unique(['group_id', 'imported_from_participant_id'], 'participants_import_source_unique');
        });
    }

    public function down(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->dropUnique('participants_import_source_unique');
            $table->dropConstrainedForeignId('imported_from_participant_id');
        });
    }
};
