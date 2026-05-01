<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('filename');
            $table->string('original_name');
            $table->string('path', 500);
            $table->unsignedBigInteger('size')->default(0);
            $table->string('mime_type', 100);
            $table->string('folder', 100)->default('other');
            $table->timestamps();

            $table->index('folder');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
