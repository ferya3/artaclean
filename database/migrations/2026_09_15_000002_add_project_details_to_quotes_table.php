<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A request for a price on industrial equipment is not an order — it is
     * the start of a conversation, and the two things a salesperson has to
     * ask on that call are how big the floor is and when the money is
     * available. Asking them on the form saves the call, and sizes the machine
     * before anyone picks up.
     */
    public function up(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->unsignedInteger('site_area_sqm')->nullable()->after('quantity');
            // immediate / quarter / year / researching
            $table->string('purchase_timeline', 24)->nullable()->after('site_area_sqm');
        });
    }

    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropColumn(['site_area_sqm', 'purchase_timeline']);
        });
    }
};
