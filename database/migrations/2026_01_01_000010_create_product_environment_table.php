<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('environment_product', function (Blueprint $table) {
            $table->foreignId('environment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();

            // 1..5 — how well the machine fits this environment. Drives the
            // ordering of "دستگاه مناسب برای بیمارستان" style listings.
            $table->unsignedTinyInteger('suitability')->default(3);

            $table->primary(['environment_id', 'product_id']);
            $table->index(['environment_id', 'suitability']);
        });

        Schema::create('category_environment', function (Blueprint $table) {
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('environment_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('suitability')->default(3);
            $table->primary(['category_id', 'environment_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_environment');
        Schema::dropIfExists('environment_product');
    }
};
