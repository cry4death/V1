<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('directions', function (Blueprint $table) {
            if (! Schema::hasColumn('directions', 'image')) {
                $table->string('image')->nullable()->after('icon');
            }
            if (! Schema::hasColumn('directions', 'details')) {
                $table->json('details')->nullable()->after('description');
            }
        });
    }

    public function down(): void
    {
        Schema::table('directions', function (Blueprint $table) {
            $table->dropColumn(['image', 'details']);
        });
    }
};
