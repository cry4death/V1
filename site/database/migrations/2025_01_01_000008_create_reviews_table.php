<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained()->cascadeOnDelete();
            $table->string('author_name');
            $table->unsignedTinyInteger('rating');
            $table->text('text');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->dateTime('published_at')->nullable();
            $table->timestamps();

            $table->index('doctor_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
