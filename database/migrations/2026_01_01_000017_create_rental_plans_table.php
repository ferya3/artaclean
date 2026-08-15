<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rental_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();

            $table->enum('period', ['daily', 'weekly', 'monthly']);
            $table->decimal('price', 15, 0)->nullable();
            $table->decimal('deposit', 15, 0)->nullable();
            $table->unsignedSmallInteger('min_duration')->default(1);
            $table->boolean('operator_included')->default(false);
            $table->boolean('price_visible')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['product_id', 'period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rental_plans');
    }
};
