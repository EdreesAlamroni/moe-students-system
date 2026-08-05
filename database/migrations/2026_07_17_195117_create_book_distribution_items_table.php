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
        Schema::create('book_distribution_items', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->foreignId('book_distribution_id')->constrained('book_distributions')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('school_period_id')->constrained('school_periods')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnUpdate()->restrictOnDelete();
            $table->timestamps();

            $table->unique(['student_id', 'academic_year_id'], 'book_distribution_items_student_ay_uq');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('book_distribution_items');
    }
};
