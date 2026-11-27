<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', fn (Blueprint $table) => $table->boolean('is_super_admin')->default(false));

        Schema::create('group_creator_confirmations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('creator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('group_name');
            $table->string('creator_name');
            $table->timestamp('confirmed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_creator_confirmations');
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('is_super_admin'));
    }
};
