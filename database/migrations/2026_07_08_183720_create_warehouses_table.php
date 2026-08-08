<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->string('number')->unique();
            $table->string('name');
            $table->string('address')->nullable();
            $table->decimal('latitude', 11, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        DB::statement('DROP SEQUENCE IF EXISTS entity_number_wh');
        DB::statement('CREATE SEQUENCE entity_number_wh START WITH 1 INCREMENT BY 1');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouses');

        DB::statement('DROP SEQUENCE IF EXISTS entity_number_wh');
    }
};
