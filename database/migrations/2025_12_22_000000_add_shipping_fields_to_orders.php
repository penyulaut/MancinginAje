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
            if (! Schema::hasColumn('orders', 'shipping_city')) {
                $table->string('shipping_city')->nullable()->after('shipping_address');
            }
            if (! Schema::hasColumn('orders', 'shipping_province')) {
                $table->string('shipping_province')->nullable()->after('shipping_city');
            }
            if (! Schema::hasColumn('orders', 'shipping_postal_code')) {
                $table->string('shipping_postal_code')->nullable()->after('shipping_province');
            }
            if (! Schema::hasColumn('orders', 'shipping_service')) {
                $table->string('shipping_service')->nullable()->after('shipping_postal_code');
            }
            if (! Schema::hasColumn('orders', 'shipping_cost')) {
                $table->decimal('shipping_cost', 10, 2)->default(0)->after('shipping_service');
            }
            if (! Schema::hasColumn('orders', 'biteship')) {
                $table->text('biteship')->nullable()->after('shipping_cost');
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
            foreach (['shipping_city','shipping_province','shipping_postal_code','shipping_service','shipping_cost','biteship'] as $c) {
                if (Schema::hasColumn('orders', $c)) {
                    $cols[] = $c;
                }
            }
            if (! empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
