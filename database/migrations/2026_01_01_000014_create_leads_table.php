<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The CRM spine. Every quote request, catalog download, calculator run and
     * contact form lands here so the sales team works one pipeline.
     */
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 20)->unique();

            $table->string('name');
            $table->string('phone', 30);
            $table->string('email')->nullable();
            $table->string('city')->nullable();
            $table->string('company')->nullable();
            $table->string('business_type')->nullable();

            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('environment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('dealer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();

            $table->enum('source', [
                'quote', 'contact', 'calculator', 'catalog', 'whatsapp', 'rental', 'phone', 'other',
            ])->default('contact');

            $table->enum('status', [
                'new', 'contacted', 'qualified', 'proposal', 'negotiation', 'won', 'lost',
            ])->default('new');

            $table->unsignedTinyInteger('score')->default(0);
            $table->decimal('estimated_value', 15, 0)->nullable();

            $table->text('message')->nullable();
            $table->text('internal_notes')->nullable();
            $table->json('meta')->nullable();

            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->string('landing_page')->nullable();
            $table->ipAddress('ip_address')->nullable();

            $table->timestamp('last_contacted_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['source', 'created_at']);
            $table->index('assigned_to');
            $table->index('phone');
        });

        Schema::create('lead_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('type', ['note', 'call', 'email', 'meeting', 'status_change', 'quote_sent']);
            $table->text('body')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['lead_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_activities');
        Schema::dropIfExists('leads');
    }
};
