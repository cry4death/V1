<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('promotion_categories')->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->enum('status', ['draft', 'active', 'completed'])->default('draft');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('image')->nullable();
            $table->text('short_description')->nullable();
            $table->longText('full_description')->nullable();
            $table->json('items')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index(['start_date', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
