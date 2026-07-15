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
        Schema::create('student_psychosocial_cards', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('student_enrollment_id')->constrained('student_enrollments')->cascadeOnUpdate()->cascadeOnDelete();

            $table->string('guardian_name')->nullable();
            $table->string('guardian_date_of_birth')->nullable();
            $table->foreignId('guardian_nationality_id')->nullable()->constrained('nationalities')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('guardian_relationship')->nullable();
            $table->string('guardian_phone_number')->nullable();
            $table->string('guardian_education_level')->nullable();
            $table->string('guardian_job_title')->nullable();
            $table->string('guardian_work_place')->nullable();

            $table->string('mother_date_of_birth')->nullable();
            $table->foreignId('mother_nationality_id')->nullable()->constrained('nationalities')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('mother_phone_number')->nullable();
            $table->string('mother_education_level')->nullable();
            $table->string('mother_profession')->nullable();
            $table->string('mother_work_place')->nullable();

            $table->unsignedInteger('number_of_family_members')->nullable();
            $table->unsignedInteger('student_family_order')->nullable();
            $table->unsignedInteger('number_of_siblings')->nullable();

            $table->string('student_living_situation')->nullable();
            $table->string('family_situation_reason')->nullable();
            $table->string('residential_area')->nullable();
            $table->string('residential_street')->nullable();
            $table->string('nearest_landmark')->nullable();
            $table->text('previous_activities')->nullable();
            $table->text('talents')->nullable();

            $table->text('previous_diseases')->nullable();
            $table->text('physical_disability_type')->nullable();
            $table->string('vision_level')->nullable();
            $table->string('hearing_level')->nullable();

            $table->string('family_income')->nullable();
            $table->string('accommodation_type')->nullable();
            $table->string('accommodation_form')->nullable();

            $table->json('behavioral_problems')->nullable();

            $table->string('guardian_representative_name')->nullable();
            $table->string('guardian_representative_relationship')->nullable();
            $table->string('guardian_representative_id_card_number')->nullable();
            $table->string('guardian_representative_phone_number')->nullable();
            $table->string('guardian_representative_work_place')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['academic_year_id', 'student_id', 'student_enrollment_id'], 'psychosocial_cards_academic_year_student_enrollment_ids_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_psychosocial_cards');
    }
};
