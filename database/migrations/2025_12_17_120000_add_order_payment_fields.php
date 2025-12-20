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
        // Add columns only if they do not already exist to avoid duplicate column errors
        if (!Schema::hasColumn('orders', 'customer_name') || !Schema::hasColumn('orders', 'customer_email')) {
            Schema::table('orders', function (Blueprint $table) {
                if (!Schema::hasColumn('orders', 'customer_name')) {
                    $table->string('customer_name')->nullable()->after('status');
                }
                if (!Schema::hasColumn('orders', 'customer_email')) {
                    $table->string('customer_email')->nullable()->after('customer_name');
                }
                if (!Schema::hasColumn('orders', 'customer_phone')) {
                    $table->string('customer_phone')->nullable()->after('customer_email');
                }
                if (!Schema::hasColumn('orders', 'shipping_address')) {
                    $table->text('shipping_address')->nullable()->after('customer_phone');
                }
                if (!Schema::hasColumn('orders', 'payment_method')) {
                    $table->string('payment_method')->nullable()->after('shipping_address');
                }
                if (!Schema::hasColumn('orders', 'payment_status')) {
                    $table->string('payment_status')->nullable()->after('payment_method');
                }
                if (!Schema::hasColumn('orders', 'snap_token')) {
                    $table->string('snap_token')->nullable()->after('payment_status');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop columns only if they exist
        Schema::table('orders', function (Blueprint $table) {
            $cols = [];
            foreach (['customer_name','customer_email','customer_phone','shipping_address','payment_method','payment_status','snap_token'] as $c) {
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
