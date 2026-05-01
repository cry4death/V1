<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            $table->string('patient_name');
            $table->string('phone', 20);
            $table->string('email')->nullable();
            $table->enum('type', ['appointment', 'feedback'])->default('appointment');
            $table->text('message')->nullable();
            $table->enum('status', ['new', 'processing', 'completed'])->default('new');
            $table->text('admin_comment')->nullable();
            $table->dateTime('preferred_date')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('type');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
