<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Polymorphic so a FAQ can hang off a product, a category, an environment,
     * or nothing at all (the site-wide FAQ page). Product-scoped rows feed the
     * FAQPage JSON-LD on the product page.
     */
    public function up(): void
    {
        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->nullableMorphs('faqable');
            $table->json('question');
            $table->json('answer');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('in_schema')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('faqs');
    }
};
