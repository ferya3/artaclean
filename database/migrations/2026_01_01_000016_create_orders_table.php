<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 20)->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('dealer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('quote_id')->nullable()->constrained()->nullOnDelete();

            $table->enum('status', ['draft', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'])
                ->default('draft');
            $table->enum('payment_status', ['unpaid', 'partial', 'paid', 'refunded'])->default('unpaid');

            $table->decimal('subtotal', 15, 0)->default(0);
            $table->decimal('discount', 15, 0)->default(0);
            $table->decimal('tax', 15, 0)->default(0);
            $table->decimal('shipping', 15, 0)->default(0);
            $table->decimal('total', 15, 0)->default(0);
            $table->string('currency', 3)->default('IRR');

            $table->string('customer_name');
            $table->string('customer_phone', 30);
            $table->string('customer_email')->nullable();
            $table->json('billing_address')->nullable();
            $table->json('shipping_address')->nullable();

            $table->text('notes')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();

            // Snapshotted so an order stays readable after a product is renamed.
            $table->string('name');
            $table->string('sku')->nullable();
            $table->decimal('unit_price', 15, 0)->default(0);
            $table->unsignedSmallInteger('quantity')->default(1);
            $table->decimal('total', 15, 0)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
