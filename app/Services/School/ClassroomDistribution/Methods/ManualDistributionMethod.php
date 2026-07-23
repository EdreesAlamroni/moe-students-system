<?php

namespace App\Services\School\ClassroomDistribution\Methods;

use App\Http\Requests\School\ClassroomDistribution\DistributionMethodRequest;
use App\Http\Resources\School\StudentCollection;
use App\Models\AcademicYear;
use App\Models\ClassroomDistributionCompletion;
use App\Models\GradeLevel;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Services\School\ClassroomDistribution\Contracts\DistributionMethodContract;
use App\Services\School\ClassroomDistribution\Shared\ClassroomDistributionHelper;
use App\Support\ResourcePayloadBuilder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ManualDistributionMethod implements DistributionMethodContract
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
        $unassignedStudents = [];

        if (! is_null($selectedGradeLevelId)) {
            $classrooms = ClassroomDistributionHelper::getClassroomsForGrade($selectedGradeLevelId);

            $unassignedStudents = ClassroomDistributionHelper::getStudentsWithoutClassroom($selectedGradeLevelId);

            $unassignedStudents = ResourcePayloadBuilder::make(
                StudentCollection::make($unassignedStudents)
            );
        }

        $isDistributionCompleted = ClassroomDistributionCompletion::isCompleteForCurrentSchoolAndYear();

        return [
            'gradeLevels' => $gradeLevels,
            'selectedGradeLevelId' => $selectedGradeLevelId,
            'gradeLevel' => $gradeLevel,
            'classrooms' => $classrooms,
            'unassignedStudents' => $unassignedStudents,
            'isDistributionCompleted' => $isDistributionCompleted,
        ];
    }

    public function apply(DistributionMethodRequest $request): void
    {
        $gradeLevelId = intval($request->validated('grade_level_id'));
        $classroomId = intval($request->validated('classroom_id'));

        $studentIds = array_map('intval', $request->validated('student_ids'));

        /** @var EloquentCollection<int, Student> $students */
        $students = ClassroomDistributionHelper::getSelectedStudentsWithoutClassroom($gradeLevelId, $studentIds);

        if ($students->count() !== count($studentIds)) {
            throw ValidationException::withMessages([
                'student_ids' => [
                    __('alerts.messages.classroom-distribution-invalid-students'),
                ],
            ]);
        }

        DB::transaction(function () use ($students, $gradeLevelId, $classroomId): void {
            $students->each(function (Student $student) use ($gradeLevelId, $classroomId): void {
                StudentEnrollment::query()
                    ->where('academic_year_id', '=', AcademicYear::currentId())
                    ->where('grade_level_id', '=', $gradeLevelId)
                    ->where('student_id', '=', $student->id)
                    ->whereNull('classroom_id')
                    ->update(['classroom_id' => $classroomId]);
            });
        });
    }
}
