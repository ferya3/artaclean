<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * "دستگاه مناسب برای ..." — factory, warehouse, hospital, mall, hotel,
     * parking, airport, car wash. Each one carries the area band and the
     * surface notes the selector uses to score products.
     */
    public function up(): void
    {
        Schema::create('environments', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->json('name');
            $table->json('short_description')->nullable();
            $table->json('description')->nullable();
            $table->json('challenges')->nullable();

            $table->string('icon')->nullable();
            $table->string('image')->nullable();

            $table->unsignedInteger('typical_area_min')->nullable();
            $table->unsignedInteger('typical_area_max')->nullable();

            $table->json('seo_title')->nullable();
            $table->json('seo_description')->nullable();

            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('environments');
    }
};
