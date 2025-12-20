<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Add transaction_id if missing (some environments might not have run older migration)
            if (!Schema::hasColumn('orders', 'transaction_id')) {
                $table->string('transaction_id')->nullable()->unique()->after('payment_method');
            }

            // Add payment_status if missing
            if (!Schema::hasColumn('orders', 'payment_status')) {
                $table->enum('payment_status', ['pending', 'paid', 'expired', 'failed'])->default('pending')->after('transaction_id');
            }

            // Add snap_token used by Midtrans
            if (!Schema::hasColumn('orders', 'snap_token')) {
                $table->string('snap_token')->nullable()->after('payment_status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $cols = [];
            foreach (['snap_token','payment_status','transaction_id'] as $c) {
                if (Schema::hasColumn('orders', $c)) {
                    $cols[] = $c;
                }
            }

            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
