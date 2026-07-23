<?php

namespace App\Services\School\ClassroomDistribution\Methods;

use App\Http\Requests\School\ClassroomDistribution\DistributionMethodRequest;
use App\Models\AcademicYear;
use App\Models\ClassroomDistributionCompletion;
use App\Models\GradeLevel;
use App\Models\StudentEnrollment;
use App\Services\School\ClassroomDistribution\Contracts\DistributionMethodContract;
use App\Services\School\ClassroomDistribution\Shared\ClassroomDistributionHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RandomDistributionMethod implements DistributionMethodContract
{
    public function credentials(Request $request): array
    {
        $gradeLevels = GradeLevel::listForCurrentSchool();
        $selectedGradeLevelId = $request->integer('grade_level_id') ?: null;

        $gradeLevel = $gradeLevels->firstWhere('id', '=', $selectedGradeLevelId);

        if (is_null($gradeLevel)) {
            $selectedGradeLevelId = null;
        }

        $classrooms = [];
        $pendingStudentCount = 0;

        if (! is_null($selectedGradeLevelId)) {
            $classrooms = ClassroomDistributionHelper::getClassroomsForGrade($selectedGradeLevelId);

            $pendingStudentCount = ClassroomDistributionHelper::getCountStudentsWithoutClassroom($selectedGradeLevelId);
        }

        $isDistributionCompleted = ClassroomDistributionCompletion::isCompleteForCurrentSchoolAndYear();

        return [
            'gradeLevels' => $gradeLevels,
            'selectedGradeLevelId' => $selectedGradeLevelId,
            'gradeLevel' => $gradeLevel,
            'classrooms' => $classrooms,
            'pendingStudentCount' => $pendingStudentCount,
            'isDistributionCompleted' => $isDistributionCompleted,
        ];
    }

    public function apply(DistributionMethodRequest $request): void
    {
        $gradeLevelId = intval($request->validated('grade_level_id'));

        $classrooms = ClassroomDistributionHelper::getClassroomsForGrade($gradeLevelId, $request->validated('classroom_ids'));

        $students = ClassroomDistributionHelper::getStudentsWithoutClassroom($gradeLevelId);

        if ($students->isEmpty()) {
            throw ValidationException::withMessages([
                'students' => [
                    __('alerts.messages.classroom-distribution-no-students-without-classroom'),
                ],
            ]);
        }

        $slots = ClassroomDistributionHelper::buildCapacitySlots($classrooms);
        $studentIds = $students->pluck('id')->all();
        shuffle($studentIds);

        DB::transaction(function () use ($studentIds, $slots, $classrooms, $gradeLevelId): void {
            $academicYearId = AcademicYear::currentId();

            $classroomIdList = $classrooms->pluck('id')->all();

            foreach ($studentIds as $index => $studentId) {
                if ($index < count($slots)) {
                    $classroomId = $slots[$index];
                } else {
                    $classroomId = $classroomIdList[array_rand($classroomIdList)];
                }

                StudentEnrollment::query()
                    ->where('student_id', '=', $studentId)
                    ->where('academic_year_id', '=', $academicYearId)
                    ->where('grade_level_id', '=', $gradeLevelId)
                    ->whereNull('classroom_id')
                    ->update(['classroom_id' => $classroomId]);
            }
        });
    }
}
