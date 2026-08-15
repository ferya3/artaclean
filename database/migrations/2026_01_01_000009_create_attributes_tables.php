<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attributes', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->json('name');
            $table->enum('type', ['select', 'multiselect', 'number', 'boolean'])->default('select');
            $table->string('unit')->nullable();
            $table->boolean('is_filterable')->default(true);
            $table->boolean('is_comparable')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attribute_id')->constrained()->cascadeOnDelete();
            $table->string('slug');
            $table->json('value');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['attribute_id', 'slug']);
        });

        Schema::create('product_attribute_values', function (Blueprint $table) {
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attribute_value_id')->constrained()->cascadeOnDelete();
            $table->decimal('numeric_value', 12, 2)->nullable();

            $table->primary(['product_id', 'attribute_value_id'], 'pav_primary');
        });

        // Which categories expose which attribute as a facet.
        Schema::create('attribute_category', function (Blueprint $table) {
            $table->foreignId('attribute_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->primary(['attribute_id', 'category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attribute_category');
        Schema::dropIfExists('product_attribute_values');
        Schema::dropIfExists('attribute_values');
        Schema::dropIfExists('attributes');
    }
};
