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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->foreignId('education_monitor_id')->nullable()->constrained('education_monitors')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('school_id')->nullable()->constrained('schools')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('nationality_id')->constrained('nationalities')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('number')->unique()->nullable();
            $table->string('registration_status')->index();
            $table->string('exam_enrollment_status')->index()->nullable();
            $table->string('first_name');
            $table->string('father_name');
            $table->string('grandfather_name');
            $table->string('surname');
            $table->string('mother_name');
            $table->string('gender')->index();
            $table->date('date_of_birth');
            $table->string('national_id')->unique()->nullable();
            $table->string('family_registration_number')->index()->nullable();
            $table->string('passport_number')->unique()->nullable();
            $table->timestamps();
            $table->softDeletes();

            if (Schema::getConnection()->getDriverName() !== 'sqlite') {
                $table->fullText(['first_name', 'father_name', 'grandfather_name', 'surname'], 'student_full_name_fulltext');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
