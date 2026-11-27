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
            $table->text('email')->nullable()->change();
        });
    }

    public function down(): void
    {
        if (DB::table('participants')->whereRaw('length(email) > 255')->exists()) {
            throw new RuntimeException('Há e-mails que excedem o tamanho anterior da coluna.');
        }

        Schema::table('participants', function (Blueprint $table) {
            $table->string('email', 255)->nullable()->change();
        });
    }
};
