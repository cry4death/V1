<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            // Хранится SHA-256 хеш; клиенту отдаётся plaintext
            $table->string('refresh_token', 64)->nullable()->unique()->after('password');
            $table->timestamp('refresh_token_expires_at')->nullable()->after('refresh_token');
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn(['refresh_token', 'refresh_token_expires_at']);
        });
    }
};
