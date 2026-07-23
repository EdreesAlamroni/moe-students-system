<?php

namespace App\Http\Controllers\School;

use App\Enums\AccommodationForm;
use App\Enums\AccommodationType;
use App\Enums\FamilyIncome;
use App\Enums\HealthLevel;
use App\Enums\StudentBehavioralProblem;
use App\Enums\StudentFamilySituationReason;
use App\Enums\StudentLivingSituation;
use App\Http\Controllers\Controller;
use App\Http\Requests\School\Student\UpdatePsychosocialCardRequest;
use App\Http\Resources\School\StudentPsychosocialCardResource;
use App\Http\Resources\School\StudentResource;
use App\Models\AcademicYear;
use App\Models\Nationality;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\StudentPsychosocialCard;
use App\Support\ModelAbilityMap;
use App\Support\ResourcePayloadBuilder;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class StudentPsychosocialCardController extends Controller
{
    public function show(Student $student): Response|RedirectResponse
    {
        Gate::authorize('viewPsychosocialCard', $student);

        $student->load([
            'nationality',
            'psychosocialCard' => function (HasOne $relation) {
                $relation->with(['guardianNationality', 'motherNationality']);
            },
        ]);

        return Inertia::render('school/students/psychosocial-cards/show', [
            'student' => ResourcePayloadBuilder::make(
                StudentResource::make($student),
            ),
            'psychosocialCard' => ResourcePayloadBuilder::make(
                StudentPsychosocialCardResource::make($student->psychosocialCard),
            ),
            ...ModelAbilityMap::make($student, ['updatePsychosocialCard', 'printPsychosocialCard']),
        ]);
    }

    public function edit(Student $student): Response|RedirectResponse
    {
        Gate::authorize('updatePsychosocialCard', $student);

        $student->load([
            'enrollment.gradeLevel',
            'nationality',
        ]);

        $currentAcademicYearId = AcademicYear::currentId();

        $currentPsychosocialCard = $this->getCurrentPsychosocialCard($student->id, $currentAcademicYearId);
        $previousPsychosocialCard = $this->getPreviousPsychosocialCard($student->id, $currentAcademicYearId);

        $isFromPreviousYear = $currentPsychosocialCard === null && $previousPsychosocialCard !== null;
        $cardData = $currentPsychosocialCard ?? $previousPsychosocialCard;

        return Inertia::render('school/students/psychosocial-cards/edit', [
            'student' => ResourcePayloadBuilder::make(
                StudentResource::make($student),
            ),
            'psychosocialCard' => ResourcePayloadBuilder::make(
                StudentPsychosocialCardResource::make($student->psychosocialCard),
            ),
            'isFromPreviousYear' => $isFromPreviousYear,
            'previousAcademicYearName' => $isFromPreviousYear && $previousPsychosocialCard->academicYear
                ? $previousPsychosocialCard->academicYear->name
                : null,
            'studentLivingSituations' => StudentLivingSituation::optionsArray(),
            'familySituationReasons' => StudentFamilySituationReason::optionsArray(),
            'healthLevels' => HealthLevel::optionsArray(),
            'familyIncomes' => FamilyIncome::optionsArray(),
            'accommodationTypes' => AccommodationType::optionsArray(),
            'accommodationForms' => AccommodationForm::optionsArray(),
            'behavioralProblems' => StudentBehavioralProblem::optionsArray(),
            'nationalities' => Nationality::list(),
        ]);
    }

    public function update(UpdatePsychosocialCardRequest $request, Student $student): RedirectResponse
    {
        Gate::authorize('updatePsychosocialCard', $student);

        /** @var StudentEnrollment|null $enrollment */
        $enrollment = $student->enrollment;

        if (! $enrollment) {
            flash_error('student-enrollment-not-found');

            return Redirect::route('school.students.psychosocial-card.show', ['student' => $student]);
        }

        DB::transaction(function () use ($student, $enrollment, $request): void {
            StudentPsychosocialCard::query()->updateOrCreate([
                'student_id' => $student->id,
                'academic_year_id' => AcademicYear::currentId(),
                'student_enrollment_id' => $enrollment->id,
            ], $request->validatedAttributes());
        });

        flash_success('update');

        return Redirect::route('school.students.psychosocial-card.show', ['student' => $student]);
    }

    public function print(Student $student): View
    {
        Gate::authorize('printPsychosocialCard', $student);

        $student->load([
            'nationality',
            'enrollment.gradeLevel',
            'enrollment.classroom',
            'psychosocialCard' => function (HasOne $relation) {
                $relation->with(['guardianNationality', 'motherNationality', 'academicYear']);
            },
        ]);

        return view('print.school.students.psychosocial-card', [
            'student' => $student,
            'psychosocialCard' => $student->psychosocialCard,
            'school' => auth('school')->user()->organization,
            'academicYearName' => AcademicYear::currentName(),
            'behavioralProblems' => $this->prepareBehavioralProblemsForPrint($student->psychosocialCard),
        ]);
    }

    /**
     * @return array<int, array{label: string, behavior: string, has_problem: bool, notes: string|null}>
     */
    private function prepareBehavioralProblemsForPrint(?StudentPsychosocialCard $card): array
    {
        $savedProblems = collect($card?->behavioral_problems ?? [])->keyBy('behavior');

        return collect(StudentBehavioralProblem::cases())
            ->map(function (StudentBehavioralProblem $problem) use ($savedProblems): array {
                /** @var array{behavior: string, has_problem: bool, notes: string|null}|null $saved */
                $saved = $savedProblems->get($problem->value);

                return [
                    'label' => $problem->label(),
                    'behavior' => $problem->value,
                    'has_problem' => $saved ? boolval($saved['has_problem']) : false,
                    'notes' => $saved['notes'] ?? null,
                ];
            })
            ->all();
    }

    private function getCurrentPsychosocialCard(int $studentId, ?int $academicYearId): ?StudentPsychosocialCard
    {
        if (! $academicYearId) {
            return null;
        }

        return StudentPsychosocialCard::query()
            ->where('student_id', '=', $studentId)
            ->where('academic_year_id', '=', $academicYearId)
            ->with(['guardianNationality', 'motherNationality', 'academicYear'])
            ->first();
    }

    private function getPreviousPsychosocialCard(int $studentId, ?int $academicYearId): ?StudentPsychosocialCard
    {
        if (! $academicYearId) {
            return null;
        }

        return StudentPsychosocialCard::query()
            ->where('student_id', '=', $studentId)
            ->where('academic_year_id', '<', $academicYearId)
            ->with(['guardianNationality', 'motherNationality', 'academicYear'])
            ->orderBy('academic_year_id', 'desc')
            ->first();
    }
}
