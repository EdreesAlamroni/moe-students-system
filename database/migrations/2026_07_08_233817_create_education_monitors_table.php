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
        Schema::create('education_monitors', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->foreignId('municipal_id')->unique()->constrained('municipals')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->cascadeOnUpdate()->nullOnDelete();
            $table->string('name');
            $table->string('phone_number')->nullable()->unique();
            $table->string('whatsapp_phone_number')->nullable()->unique();
            $table->string('address')->nullable();
            $table->decimal('latitude', 11, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('education_monitors');
    }
};
