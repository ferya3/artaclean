<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The specs a buyer actually filters and compares on live as real columns
     * so the facet queries stay index-backed. Everything else (accessories,
     * certifications, hose diameter ...) goes into product_specs.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('brand_id')->nullable()->constrained()->nullOnDelete();

            $table->string('slug')->unique();
            $table->string('sku')->unique()->nullable();
            $table->string('model_code')->nullable();

            $table->json('name');
            $table->json('short_description')->nullable();
            $table->json('description')->nullable();
            $table->json('highlights')->nullable();

            $table->string('cover_image')->nullable();
            $table->string('view_360_path')->nullable();

            // --- Pricing: hidden by default, see config/site.php -------------
            $table->decimal('price', 15, 0)->nullable();
            $table->decimal('compare_at_price', 15, 0)->nullable();
            $table->string('currency', 3)->default('IRR');
            $table->boolean('price_visible')->default(false);

            // --- Filterable / comparable technical specs ---------------------
            $table->unsignedInteger('power_watt')->nullable();
            $table->unsignedSmallInteger('voltage')->nullable();
            $table->enum('power_source', ['electric', 'battery', 'petrol', 'diesel', 'lpg', 'pneumatic', 'manual'])
                ->default('electric');
            $table->unsignedSmallInteger('tank_capacity_l')->nullable();
            $table->unsignedSmallInteger('recovery_tank_l')->nullable();
            $table->unsignedSmallInteger('suction_mbar')->nullable();
            $table->unsignedSmallInteger('airflow_lpm')->nullable();
            $table->unsignedSmallInteger('cleaning_width_mm')->nullable();
            $table->unsignedInteger('productivity_sqm_h')->nullable();
            $table->unsignedSmallInteger('weight_kg')->nullable();
            $table->unsignedSmallInteger('noise_db')->nullable();
            $table->unsignedSmallInteger('battery_runtime_min')->nullable();
            $table->unsignedSmallInteger('water_pressure_bar')->nullable();
            $table->unsignedSmallInteger('steam_temperature_c')->nullable();
            $table->enum('operator_type', ['walk_behind', 'ride_on', 'handheld', 'stationary', 'towed'])->nullable();

            // --- Merchandising ------------------------------------------------
            $table->enum('stock_status', ['in_stock', 'on_order', 'out_of_stock', 'discontinued'])->default('in_stock');
            $table->unsignedSmallInteger('warranty_months')->default(12);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_rentable')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->unsignedInteger('views_count')->default(0);
            $table->unsignedInteger('quotes_count')->default(0);

            // --- SEO ------------------------------------------------------------
            $table->json('seo_title')->nullable();
            $table->json('seo_description')->nullable();
            $table->string('seo_keywords')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'category_id', 'sort_order']);
            $table->index(['is_active', 'is_featured']);
            $table->index(['is_active', 'is_rentable']);
            $table->index('brand_id');
            $table->index('productivity_sqm_h');
            $table->index('cleaning_width_mm');
            $table->index('tank_capacity_l');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
