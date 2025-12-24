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
        Schema::create('shipping_rate_caches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('district_id')->index();
            $table->string('courier', 50)->index();
            $table->integer('weight_bucket')->index();
            $table->json('data')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamps();

            $table->unique(['district_id', 'courier', 'weight_bucket'], 'ship_cache_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_rate_caches');
    }
};
