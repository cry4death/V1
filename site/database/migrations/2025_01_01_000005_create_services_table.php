<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('direction_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->decimal('price', 10, 2)->default(0);
            $table->text('description')->nullable();
            $table->text('indications')->nullable();
            $table->text('preparation')->nullable();
            $table->enum('status', ['active', 'hidden'])->default('active');
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('direction_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
