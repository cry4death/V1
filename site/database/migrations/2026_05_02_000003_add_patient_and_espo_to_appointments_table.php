<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->foreignId('patient_id')
                ->nullable()
                ->after('id')
                ->constrained('patients')
                ->nullOnDelete();
            $table->string('espo_entity_id')->nullable()->after('patient_id');
            $table->string('espo_entity_type', 64)->nullable()->after('espo_entity_id');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign(['patient_id']);
            $table->dropColumn(['patient_id', 'espo_entity_id', 'espo_entity_type']);
        });
    }
};
