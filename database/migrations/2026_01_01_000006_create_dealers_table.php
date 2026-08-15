<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dealers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->string('slug')->unique();
            $table->string('name');
            $table->string('company_name')->nullable();
            $table->string('province')->nullable();
            $table->string('city')->nullable();
            $table->text('address')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email')->nullable();
            $table->string('logo')->nullable();
            $table->text('description')->nullable();

            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            $table->enum('tier', ['bronze', 'silver', 'gold', 'platinum'])->default('bronze');
            $table->decimal('commission_rate', 5, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'province']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('dealer_id')->nullable()->after('is_active')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('dealer_id');
        });

        Schema::dropIfExists('dealers');
    }
};
