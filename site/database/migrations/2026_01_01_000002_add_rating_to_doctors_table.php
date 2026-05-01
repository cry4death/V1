<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            if (! Schema::hasColumn('doctors', 'experience_since')) {
                $table->unsignedSmallInteger('experience_since')->nullable()->after('experience_years');
            }
            if (! Schema::hasColumn('doctors', 'rating')) {
                $table->decimal('rating', 3, 2)->default(5.00)->after('description');
            }
        });
    }

    public function down(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->dropColumn(['experience_since', 'rating']);
        });
    }
};
