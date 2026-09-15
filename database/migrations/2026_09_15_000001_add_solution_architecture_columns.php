<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The catalogue could already answer "which scrubber?", but not the
     * question a buyer actually arrives with: "the floor of my factory is
     * 5,000 m² and there is oil on it."
     *
     * Answering that needs two facts the products never carried — what they
     * clean off (soil) and what they clean it off (surface) — plus the
     * grouping the navigation needs, the article kinds the knowledge hub
     * separates, the services that are half the sale in this trade, and which
     * machines a spare part actually fits.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // ['dust', 'oil', ...] — see App\Enums\SoilType.
            $table->json('soil_types')->nullable()->after('operator_type');
            // ['concrete', 'carpet', ...] — see App\Enums\SurfaceType.
            $table->json('surface_types')->nullable()->after('soil_types');
        });

        Schema::table('categories', function (Blueprint $table) {
            // Groups the mega menu: floor care, high pressure, specialist, consumables.
            $table->string('nav_group', 32)->nullable()->after('sort_order');
        });

        Schema::table('blogs', function (Blueprint $table) {
            // guide / cleaning / comparison / case_study / video / article.
            $table->string('type', 24)->default('article')->after('category_id');
            $table->index('type');
        });

        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->json('name');
            $table->json('short_description')->nullable();
            $table->json('description')->nullable();

            // The three or four promises the service page leads with.
            $table->json('bullets')->nullable();

            $table->string('icon', 40)->nullable();
            $table->json('seo_title')->nullable();
            $table->json('seo_description')->nullable();

            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });

        /*
         * Spare parts and accessories fit machines. Self-referencing, because
         * in this catalogue a brush and a scrubber are both products: the part
         * is the owner, the machine is what it fits.
         */
        Schema::create('product_compatibility', function (Blueprint $table) {
            $table->foreignId('part_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('machine_id')->constrained('products')->cascadeOnDelete();
            $table->primary(['part_id', 'machine_id']);
            $table->index('machine_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_compatibility');
        Schema::dropIfExists('services');

        Schema::table('blogs', function (Blueprint $table) {
            $table->dropIndex(['type']);
            $table->dropColumn('type');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('nav_group');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['soil_types', 'surface_types']);
        });
    }
};
