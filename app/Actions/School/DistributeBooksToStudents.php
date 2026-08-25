<?php

namespace App\Actions\School;

use App\Models\AcademicYear;
use App\Models\BookDistribution;
use App\Models\BookDistributionItem;
use App\Models\Student;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DistributeBooksToStudents
{
    /**
     * Mark the selected students as having received their books for the current academic year.
     *
     * Requires warehouse confirmation for the grade level. Records only students who are
     * enrolled in the school and have not already received books this year.
     */
    public function execute(int $schoolPeriodId, int $gradeLevelId, array $studentIds, ?int $classroomId = null): int
    {
        $currentAcademicYearId = AcademicYear::currentId();

        if (is_null($currentAcademicYearId)) {
            throw ValidationException::withMessages([
                '_' => [__('alerts.messages.academic-year-not-found')],
            ]);
        }

        $distribution = BookDistribution::query()
            ->where('academic_year_id', '=', $currentAcademicYearId)
            ->where('school_period_id', '=', $schoolPeriodId)
            ->where('grade_level_id', '=', $gradeLevelId)
            ->first();

        if (is_null($distribution)) {
            throw ValidationException::withMessages([
                'grade_level_id' => [__('alerts.messages.book-distribution-grade-level-not-confirmed')],
            ]);
        }

        return DB::transaction(function () use ($distribution, $gradeLevelId, $schoolPeriodId, $studentIds, $currentAcademicYearId, $classroomId): int {
            $eligibleStudentIds = Student::query()
                ->whereIn('id', $studentIds)
                ->where('school_period_id', '=', $schoolPeriodId)
                ->whereHas('enrollment', function (Builder $query) use ($gradeLevelId, $schoolPeriodId, $classroomId): void {
                    $query
                        ->where('grade_level_id', '=', $gradeLevelId)
                        ->where('school_period_id', '=', $schoolPeriodId)
                        ->when(filled($classroomId), function (Builder $query) use ($classroomId): void {
                            $query->where('classroom_id', '=', $classroomId);
                        });
                })
                ->whereDoesntHave('bookDistributionItem')
                ->pluck('id');

            if ($eligibleStudentIds->isEmpty()) {
                return 0;
            }

            $now = now();

            $rows = $eligibleStudentIds->map(function (int $studentId) use ($distribution, $currentAcademicYearId, $schoolPeriodId, $now): array {
                return [
                    'uuid' => Str::uuid()->toString(),
                    'book_distribution_id' => $distribution->id,
                    'academic_year_id' => $currentAcademicYearId,
                    'school_period_id' => $schoolPeriodId,
                    'student_id' => $studentId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            })->all();

            BookDistributionItem::query()->insertOrIgnore($rows);

            return count($rows);
        });
    }
}
