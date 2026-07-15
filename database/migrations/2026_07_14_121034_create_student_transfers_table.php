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
        Schema::create('student_transfers', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->foreignId('left_academic_year_id')->nullable()->constrained('academic_years')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('joined_academic_year_id')->nullable()->constrained('academic_years')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('from_school_id')->nullable()->constrained('schools')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('to_school_id')->nullable()->constrained('schools')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamp('left_school_at')->nullable();
            $table->timestamp('joined_school_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_transfers');
    }
};
