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
        Schema::create('school_periods', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->foreignId('education_monitor_id')->constrained('education_monitors')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('education_services_office_id')->nullable()->constrained('education_services_offices')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('name')->index();
            $table->string('academic_period')->index();
            $table->string('students_gender')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['school_id', 'academic_period']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_periods');
    }
};
