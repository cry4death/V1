<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dateTime('start_at')->nullable()->after('preferred_date');
            $table->dateTime('end_at')->nullable()->after('start_at');
            $table->text('cancellation_reason')->nullable()->after('end_at');
            $table->timestamp('cancelled_at')->nullable()->after('cancellation_reason');
            $table->foreignId('rescheduled_from_id')
                ->nullable()
                ->after('cancelled_at')
                ->constrained('appointments')
                ->nullOnDelete();

            $table->index(['doctor_id', 'start_at']);
            $table->index(['patient_id', 'start_at']);
        });

        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE appointments MODIFY COLUMN status ENUM('new','processing','completed','cancelled','rescheduled') NOT NULL DEFAULT 'new'");
        }
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign(['rescheduled_from_id']);
            $table->dropIndex(['doctor_id', 'start_at']);
            $table->dropIndex(['patient_id', 'start_at']);
            $table->dropColumn([
                'start_at',
                'end_at',
                'cancellation_reason',
                'cancelled_at',
                'rescheduled_from_id',
            ]);
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE appointments MODIFY COLUMN status ENUM('new','processing','completed') NOT NULL DEFAULT 'new'");
        }
    }
};
