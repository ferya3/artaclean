<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotes', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 20)->unique();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('dealer_id')->nullable()->constrained()->nullOnDelete();

            $table->enum('type', ['purchase', 'rental'])->default('purchase');
            $table->unsignedSmallInteger('quantity')->default(1);

            // Rental requests: daily / weekly / monthly + how many periods.
            $table->enum('rental_period', ['daily', 'weekly', 'monthly'])->nullable();
            $table->unsignedSmallInteger('rental_duration')->nullable();
            $table->date('rental_starts_at')->nullable();

            $table->enum('status', ['pending', 'in_review', 'sent', 'accepted', 'rejected', 'expired'])
                ->default('pending');

            $table->decimal('quoted_price', 15, 0)->nullable();
            $table->decimal('discount', 15, 0)->default(0);
            $table->string('currency', 3)->default('IRR');
            $table->date('valid_until')->nullable();

            $table->text('customer_note')->nullable();
            $table->text('response_note')->nullable();

            $table->foreignId('responded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['product_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotes');
    }
};
