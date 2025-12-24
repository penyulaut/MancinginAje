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
        Schema::create('user_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('label')->nullable();
            $table->text('address_line')->nullable();
            $table->unsignedBigInteger('province_id')->nullable();
            $table->string('province_name')->nullable();
            $table->unsignedBigInteger('city_id')->nullable();
            $table->string('city_name')->nullable();
            $table->unsignedBigInteger('district_id')->nullable();
            $table->string('district_name')->nullable();
            $table->string('postal_code')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_addresses');
    }
};
