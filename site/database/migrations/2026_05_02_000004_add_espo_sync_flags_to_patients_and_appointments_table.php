<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->timestamp('espo_synced_at')->nullable()->after('espo_contact_id');
            $table->enum('espo_sync_status', ['pending', 'synced', 'failed'])
                ->default('pending')
                ->after('espo_synced_at');
            $table->text('espo_sync_error')->nullable()->after('espo_sync_status');
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->timestamp('espo_synced_at')->nullable()->after('espo_entity_type');
            $table->enum('espo_sync_status', ['pending', 'synced', 'failed', 'skipped'])
                ->default('pending')
                ->after('espo_synced_at');
            $table->text('espo_sync_error')->nullable()->after('espo_sync_status');
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn(['espo_synced_at', 'espo_sync_status', 'espo_sync_error']);
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn(['espo_synced_at', 'espo_sync_status', 'espo_sync_error']);
        });
    }
};
