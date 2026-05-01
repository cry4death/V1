<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('article_categories')->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('author')->nullable();
            $table->dateTime('published_at')->nullable();
            $table->unsignedInteger('reading_time')->nullable();
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->string('cover_image')->nullable();
            $table->longText('content')->nullable();
            $table->string('meta_description', 500)->nullable();
            $table->timestamps();

            $table->index('category_id');
            $table->index(['status', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
