<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('specialization_id')->constrained()->restrictOnDelete();
            $table->string('last_name');
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('slug')->unique();
            $table->enum('category', ['highest', 'first', 'second']);
            $table->unsignedInteger('experience_years')->default(0);
            $table->enum('patient_age', ['adults', 'children', 'both'])->default('both');
            $table->string('photo')->nullable();
            $table->text('description')->nullable();
            $table->json('education')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('specialization_id');
            $table->index('status');
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctors');
    }
};
