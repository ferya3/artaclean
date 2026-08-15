<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('downloads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();

            $table->enum('type', ['catalog', 'manual', 'datasheet', 'certificate', 'spare_parts', 'software'])
                ->default('catalog');
            $table->json('title');
            $table->json('description')->nullable();

            $table->string('file_path');
            $table->string('mime_type')->nullable();
            $table->unsignedInteger('file_size')->nullable();
            $table->string('language', 5)->default('fa');

            // Gated downloads capture a lead before handing over the PDF.
            $table->boolean('requires_lead')->default(false);
            $table->boolean('is_public')->default(true);
            $table->unsignedInteger('download_count')->default(0);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['product_id', 'type']);
            $table->index(['is_public', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('downloads');
    }
};
