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
        Schema::create('class_schedules', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('classroom_id')->constrained('classrooms')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('class_period_id')->constrained('class_periods')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnUpdate()->nullOnDelete();
            $table->unsignedTinyInteger('day_of_week')->index();
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->unique(['classroom_id', 'class_period_id', 'day_of_week'], 'class_schedules_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_schedules');
    }
};
