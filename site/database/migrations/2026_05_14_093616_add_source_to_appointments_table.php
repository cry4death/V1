<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            // 'web' — запись через сайт, 'mobile' — через мобильное приложение
            $table->enum('source', ['web', 'mobile'])
                ->default('web')
                ->after('type');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn('source');
        });
    }
};
