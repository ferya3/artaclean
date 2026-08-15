<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->json('alt')->nullable();
            $table->boolean('is_360_frame')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['product_id', 'is_360_frame', 'sort_order']);
        });

        Schema::create('product_videos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->json('title')->nullable();
            $table->enum('provider', ['aparat', 'youtube', 'file'])->default('aparat');
            $table->string('url');
            $table->string('thumbnail')->nullable();
            $table->unsignedSmallInteger('duration_seconds')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['product_id', 'sort_order']);
        });

        Schema::create('product_specs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('group')->default('general');
            $table->json('label');
            $table->json('value');
            $table->string('unit')->nullable();
            $table->boolean('is_highlighted')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['product_id', 'group', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_specs');
        Schema::dropIfExists('product_videos');
        Schema::dropIfExists('product_images');
    }
};
