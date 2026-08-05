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
        Schema::create('book_distributions', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('education_monitor_id')->constrained('education_monitors')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('school_period_id')->constrained('school_periods')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('grade_level_id')->constrained('grade_levels')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnUpdate()->restrictOnDelete();
            $table->timestamp('distributed_at');
            $table->timestamps();

            $table->unique(['school_period_id', 'grade_level_id', 'academic_year_id'], 'book_distributions_school_period_grade_ay_uq');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('book_distributions');
    }
};
