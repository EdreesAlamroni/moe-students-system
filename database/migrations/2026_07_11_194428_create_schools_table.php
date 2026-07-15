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
        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->foreignId('education_monitor_id')->constrained('education_monitors')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('education_services_office_id')->nullable()->constrained('education_services_offices')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('serial_number')->unique();
            $table->string('type')->index();
            $table->string('educational_company_name')->nullable();
            $table->string('branch_type')->nullable();
            $table->string('building_type')->nullable();
            $table->string('name')->index();
            $table->string('academic_period')->index();
            $table->string('students_gender')->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schools');
    }
};
