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
        Schema::create('education_services_offices', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->foreignId('education_monitor_id')->constrained('education_monitors')->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('number')->unique();
            $table->string('name');
            $table->string('phone_number')->nullable()->unique();
            $table->string('whatsapp_phone_number')->nullable()->unique();
            $table->string('address')->nullable();
            $table->decimal('latitude', 11, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        DB::statement('DROP SEQUENCE IF EXISTS entity_number_eso');
        DB::statement('CREATE SEQUENCE entity_number_eso START WITH 1 INCREMENT BY 1');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('education_services_offices');

        DB::statement('DROP SEQUENCE IF EXISTS entity_number_eso');
    }
};
