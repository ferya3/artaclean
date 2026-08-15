<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blogs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();

            $table->string('slug')->unique();
            $table->json('title');
            $table->json('excerpt')->nullable();
            $table->json('body');
            $table->string('cover_image')->nullable();

            $table->json('seo_title')->nullable();
            $table->json('seo_description')->nullable();

            $table->unsignedSmallInteger('reading_minutes')->default(3);
            $table->unsignedInteger('views_count')->default(0);
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['is_published', 'published_at']);
        });

        // "مقاله مرتبط" on the product page.
        Schema::create('blog_product', function (Blueprint $table) {
            $table->foreignId('blog_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->primary(['blog_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_product');
        Schema::dropIfExists('blogs');
    }
};
