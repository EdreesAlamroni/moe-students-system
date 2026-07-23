@php
    use App\Enums\AccommodationForm;
    use App\Enums\AccommodationType;
    use App\Enums\FamilyIncome;
    use App\Enums\HealthLevel;
    use App\Enums\StudentFamilySituationReason;
    use App\Enums\StudentLivingSituation;
    use App\Models\Student;
    use App\Models\StudentPsychosocialCard;

    /** @var Student $student */
    /** @var StudentPsychosocialCard|null $psychosocialCard */
    /** @var array<int, array{label: string, behavior: string, has_problem: bool, notes: string|null}> $behavioralProblems */

    $printValue = static function (mixed $value): string {
        if ($value === null || $value === '') {
            return ' ';
        }

        return (string) $value;
    };

    $printCheckbox = static function (bool $checked): string {
        return $checked ? '✓' : '';
    };
@endphp

<x-print-layout size="A4" :landscape="false" margin="0" title="البطاقة الإجتماعية والنفسية">
    <x-slot name="content">
        {{-- Page 1: Identity & family contacts --}}
        <x-print.report-sheet padding="8mm">
            @include('print.school.students.partials.psychosocial-card-header', [
                'title' => 'بطاقة الطالب الاجتماعية والنفسية',
                'academicYearName' => $academicYearName,
                'school' => $school,
            ])

            <section class="print-section">
                <h2 class="print-section-title">البيانات الأولية</h2>

                <div class="print-field-grid">
                    <div class="print-field col-span-2">
                        <span class="print-field-label">اسم الطالب الرباعي:</span>
                        <span class="print-field-value">{{ $printValue($student->fullName) }}</span>
                    </div>

                    <div class="print-field">
                        <span class="print-field-label">تاريخ الميلاد:</span>
                        <span class="print-field-value font-mono">{{ $printValue($student->date_of_birth?->toDateString()) }}</span>
                    </div>

                    <div class="print-field">
                        <span class="print-field-label">الجنسية:</span>
                        <span class="print-field-value">{{ $printValue($student->nationality?->name) }}</span>
                    </div>

                    <div class="print-field">
                        <span class="print-field-label">الجنس:</span>
                        <span class="print-field-value">{{ $printValue($student->labelOfGender) }}</span>
                    </div>

                    <div class="print-field">
                        <span class="print-field-label">العام الدراسي:</span>
                        <span class="print-field-value font-mono">{{ $printValue($psychosocialCard?->academicYear?->name) }}</span>
                    </div>

                    <div class="print-field">
                        <span class="print-field-label">الصف الدراسي:</span>
                        <span class="print-field-value">{{ $printValue($student->enrollment?->gradeLevel?->name) }}</span>
                    </div>

                    <div class="print-field">
                        <span class="print-field-label">الفصل:</span>
                        <span class="print-field-value">{{ $printValue($student->enrollment?->classroom?->name) }}</span>
                    </div>
                </div>
            </section>

            <section class="print-section">
                <h2 class="print-section-title">بيانات ولي الأمر</h2>

                <div class="print-field-grid">
                    <div class="print-field">
                        <span class="print-field-label">اسم ولي الأمر:</span>
                        <span class="print-field-value">{{ $printValue($psychosocialCard?->guardian_name) }}</span>
                    </div>

                    <div class="print-field">
                        <span class="print-field-label">تاريخ الميلاد:</span>
                        <span class="print-field-value font-mono">{{ $printValue($psychosocialCard?->guardian_date_of_birth) }}</span>
                    </div>

                    <div class="print-field">
                        <span class="print-field-label">الجنسية:</span>
                        <span class="print-field-value">{{ $printValue($psychosocialCard?->guardianNationality?->name) }}</span>
                    </div>

                    <div class="print-field">
                        <span class="print-field-label">صلة القرابة:</span>
                        <span class="print-field-value">{{ $printValue($psychosocialCard?->guardian_relationship) }}</span>
                    </div>

                    <div class="print-field">
                        <span class="print-field-label">رقم الهاتف:</span>
                        <span class="print-field-value font-mono">{{ $printValue($psychosocialCard?->guardian_phone_number) }}</span>
                    </div>

                    <div class="print-field">
                        <span class="print-field-label">المستوى التعليمي:</span>
                        <span class="print-field-value">{{ $printValue($psychosocialCard?->guardian_education_level) }}</span>
                    </div>

                    <div class="print-field">
                        <span class="print-field-label">الوظيفة:</span>
                        <span class="print-field-value">{{ $printValue($psychosocialCard?->guardian_job_title) }}</span>
                    </div>

                    <div class="print-field">
                        <span class="print-field-label">مكان العمل:</span>
                        <span class="print-field-value">{{ $printValue($psychosocialCard?->guardian_work_place) }}</span>
                    </div>
                </div>
            </section>

            <section class="print-section">
                <h2 class="print-section-title">بيانات عن الأم</h2>

                <div class="print-field-grid">
                    <div class="print-field col-span-2">
                        <span class="print-field-label">اسم الأم:</span>
                        <span class="print-field-value">{{ $printValue($student->mother_name) }}</span>
                    </div>

                    <div class="print-field">
                        <span class="print-field-label">تاريخ الميلاد:</span>
                        <span class="print-field-value font-mono">{{ $printValue($psychosocialCard?->mother_date_of_birth) }}</span>
                    </div>

                    <div class="print-field">
                        <span class="print-field-label">الجنسية:</span>
                        <span class="print-field-value">{{ $printValue($psychosocialCard?->motherNationality?->name) }}</span>
                    </div>

                    <div class="print-field">
                        <span class="print-field-label">رقم الهاتف:</span>
                        <span class="print-field-value font-mono">{{ $printValue($psychosocialCard?->mother_phone_number) }}</span>
                    </div>

                    <div class="print-field">
                        <span class="print-field-label">المستوى التعليمي:</span>
                        <span class="print-field-value">{{ $printValue($psychosocialCard?->mother_education_level) }}</span>
                    </div>

                    <div class="print-field">
                        <span class="print-field-label">المهنة:</span>
                        <span class="print-field-value">{{ $printValue($psychosocialCard?->mother_profession) }}</span>
                    </div>

                    <div class="print-field col-span-2">
                        <span class="print-field-label">مكان العمل:</span>
                        <span class="print-field-value">{{ $printValue($psychosocialCard?->mother_work_place) }}</span>
                    </div>
                </div>
            </section>
        </x-print.report-sheet>

        {{-- Page 2: Social, health & housing --}}
        <x-print.report-sheet padding="8mm">
            @include('print.school.students.partials.psychosocial-card-header', [
                'title' => 'بطاقة الطالب الاجتماعية والنفسية',
                'academicYearName' => $academicYearName,
                'school' => $school,
            ])

            <section class="print-section">
                <h2 class="print-section-title">بيانات اجتماعية عن الطالب</h2>

                <div class="print-field-grid">
                    <div class="print-field">
                        <span class="print-field-label">عدد أفراد الأسرة:</span>
                        <span class="print-field-value font-mono">{{ $printValue($psychosocialCard?->number_of_family_members) }}</span>
                    </div>

                    <div class="print-field">
                        <span class="print-field-label">ترتيبه الأسري:</span>
                        <span class="print-field-value font-mono">{{ $printValue($psychosocialCard?->student_family_order) }}</span>
                    </div>

                    <div class="print-field">
                        <span class="print-field-label">عدد الأخوة:</span>
                        <span class="print-field-value font-mono">{{ $printValue($psychosocialCard?->number_of_siblings) }}</span>
                    </div>
                </div>

                <div class="mt-2 space-y-2 text-xs">
                    <div class="print-keep-together">
                        <span class="font-medium">معيشة الطالب:</span>
                        <div class="print-checkbox-group mt-1">
                            @foreach (StudentLivingSituation::cases() as $livingSituation)
                                <span class="print-checkbox-item">
                                    <span class="print-checkbox-box">{{ $printCheckbox($psychosocialCard?->student_living_situation === $livingSituation) }}</span>
                                    <span>{{ $livingSituation->label() }}</span>
                                </span>
                            @endforeach
                        </div>
                    </div>

                    <div class="print-keep-together">
                        <span class="font-medium">السبب:</span>
                        <div class="print-checkbox-group mt-1">
                            @foreach (StudentFamilySituationReason::cases() as $reason)
                                <span class="print-checkbox-item">
                                    <span class="print-checkbox-box">{{ $printCheckbox($psychosocialCard?->family_situation_reason === $reason) }}</span>
                                    <span>{{ $reason->label() }}</span>
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="print-field-grid mt-2">
                    <div class="print-field">
                        <span class="print-field-label">المنطقة:</span>
                        <span class="print-field-value">{{ $printValue($psychosocialCard?->residential_area) }}</span>
                    </div>

                    <div class="print-field">
                        <span class="print-field-label">الشارع:</span>
                        <span class="print-field-value">{{ $printValue($psychosocialCard?->residential_street) }}</span>
                    </div>

                    <div class="print-field col-span-2">
                        <span class="print-field-label">أقرب نقطة دالة:</span>
                        <span class="print-field-value">{{ $printValue($psychosocialCard?->nearest_landmark) }}</span>
                    </div>

                    <div class="print-field col-span-2">
                        <span class="print-field-label">النشاطات التي شارك فيها الطالب سابقاً:</span>
                        <span class="print-field-value">{{ $printValue($psychosocialCard?->previous_activities) }}</span>
                    </div>

                    <div class="print-field col-span-2">
                        <span class="print-field-label">المواهب التي يتمتع بها الطالب:</span>
                        <span class="print-field-value">{{ $printValue($psychosocialCard?->talents) }}</span>
                    </div>
                </div>
            </section>

            <section class="print-section">
                <h2 class="print-section-title">الحالة الصحية للطالب</h2>

                <div class="space-y-2 text-xs">
                    <div class="print-field">
                        <span class="print-field-label">1. الأمراض التي سبق الإصابة بها:</span>
                        <span class="print-field-value">{{ $printValue($psychosocialCard?->previous_diseases) }}</span>
                    </div>

                    <div class="print-field">
                        <span class="print-field-label">2. نوع الإعاقة الجسدية إن وجدت:</span>
                        <span class="print-field-value">{{ $printValue($psychosocialCard?->physical_disability_type) }}</span>
                    </div>

                    <div class="print-keep-together">
                        <span class="font-medium">3. النظر:</span>
                        <div class="print-checkbox-group mt-1">
                            @foreach (HealthLevel::cases() as $level)
                                <span class="print-checkbox-item">
                                    <span class="print-checkbox-box">{{ $printCheckbox($psychosocialCard?->vision_level === $level) }}</span>
                                    <span>{{ $level->label() }}</span>
                                </span>
                            @endforeach
                        </div>
                    </div>

                    <div class="print-keep-together">
                        <span class="font-medium">4. السمع:</span>
                        <div class="print-checkbox-group mt-1">
                            @foreach (HealthLevel::cases() as $level)
                                <span class="print-checkbox-item">
                                    <span class="print-checkbox-box">{{ $printCheckbox($psychosocialCard?->hearing_level === $level) }}</span>
                                    <span>{{ $level->label() }}</span>
                                </span>
                            @endforeach
                        </div>
                    </div>

                    <p class="text-[10px] leading-relaxed text-gray-700">
                        ملاحظة: في حالة وجود أمراض داخلية يجب إرفاق تقرير طبي رسمي.
                    </p>
                </div>
            </section>

            <section class="print-section">
                <h2 class="print-section-title">الحالة الاقتصادية ونوع السكن للأسرة</h2>

                <div class="space-y-2 text-xs">
                    <div class="print-keep-together">
                        <span class="font-medium">دخل الأسرة:</span>
                        <div class="print-checkbox-group mt-1">
                            @foreach (FamilyIncome::cases() as $income)
                                <span class="print-checkbox-item">
                                    <span class="print-checkbox-box">{{ $printCheckbox($psychosocialCard?->family_income === $income) }}</span>
                                    <span>{{ $income->label() }}</span>
                                </span>
                            @endforeach
                        </div>
                    </div>

                    <div class="print-keep-together">
                        <span class="font-medium">نوع السكن:</span>
                        <div class="print-checkbox-group mt-1">
                            @foreach (AccommodationType::cases() as $type)
                                <span class="print-checkbox-item">
                                    <span class="print-checkbox-box">{{ $printCheckbox($psychosocialCard?->accommodation_type === $type) }}</span>
                                    <span>{{ $type->label() }}</span>
                                </span>
                            @endforeach
                        </div>
                    </div>

                    <div class="print-keep-together">
                        <span class="font-medium">مواصفات السكن:</span>
                        <div class="print-checkbox-group mt-1">
                            @foreach (AccommodationForm::cases() as $form)
                                <span class="print-checkbox-item">
                                    <span class="print-checkbox-box">{{ $printCheckbox($psychosocialCard?->accommodation_form === $form) }}</span>
                                    <span>{{ $form->label() }}</span>
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>
        </x-print.report-sheet>

        {{-- Page 3: Behavioral assessment --}}
        <x-print.report-sheet padding="8mm">
            @include('print.school.students.partials.psychosocial-card-header', [
                'title' => 'الجانب النفسي',
                'academicYearName' => $academicYearName,
                'school' => $school,
            ])

            <p class="print-keep-together mt-3 text-xs leading-relaxed text-black">
                المشاكل (الاضطرابات) السلوكية التي يعاني منها الطالب من وجهة نظر ولي الأمر خلال تواجده في البيت
            </p>

            <section class="print-table-section">
                <table class="print-report print-report-compact">
                    <thead>
                        <tr class="print-columns">
                            <th scope="col" class="w-[38%]">السلوك</th>
                            <th scope="col" class="w-[8%] text-center">نعم</th>
                            <th scope="col" class="w-[8%] text-center">لا</th>
                            <th scope="col">ملاحظات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($behavioralProblems as $problem)
                            <tr>
                                <td>{{ $problem['label'] }}</td>
                                <td class="text-center">
                                    <span class="print-checkbox-box">{{ $printCheckbox($problem['has_problem']) }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="print-checkbox-box">{{ $printCheckbox(! $problem['has_problem']) }}</span>
                                </td>
                                <td>{{ $printValue($problem['notes']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </section>
        </x-print.report-sheet>

        {{-- Page 4: Contact, representative & declaration --}}
        <x-print.report-sheet padding="8mm">
            @include('print.school.students.partials.psychosocial-card-header', [
                'title' => 'الجانب النفسي (تابع)',
                'academicYearName' => $academicYearName,
                'school' => $school,
            ])

            <section class="print-keep-together mt-3 space-y-3 text-xs">
                <div class="print-field">
                    <span class="print-field-label">كيفية الاتصال بولي الأمر:</span>
                    <span class="print-field-value font-mono">{{ $printValue($psychosocialCard?->guardian_phone_number) }}</span>
                </div>

                <div>
                    <h3 class="mb-2 font-bold">1. في حالة تعذر ولي الأمر عن زيارة المدرسة فمن ينوب عنه؟</h3>

                    <div class="print-field-grid">
                        <div class="print-field">
                            <span class="print-field-label">الاسم:</span>
                            <span class="print-field-value">{{ $printValue($psychosocialCard?->guardian_representative_name) }}</span>
                        </div>

                        <div class="print-field">
                            <span class="print-field-label">صلة القرابة:</span>
                            <span class="print-field-value">{{ $printValue($psychosocialCard?->guardian_representative_relationship) }}</span>
                        </div>

                        <div class="print-field">
                            <span class="print-field-label">رقم البطاقة الشخصية:</span>
                            <span class="print-field-value font-mono">{{ $printValue($psychosocialCard?->guardian_representative_id_card_number) }}</span>
                        </div>

                        <div class="print-field">
                            <span class="print-field-label">الهاتف:</span>
                            <span class="print-field-value font-mono">{{ $printValue($psychosocialCard?->guardian_representative_phone_number) }}</span>
                        </div>

                        <div class="print-field col-span-2">
                            <span class="print-field-label">جهة العمل:</span>
                            <span class="print-field-value">{{ $printValue($psychosocialCard?->guardian_representative_work_place) }}</span>
                        </div>
                    </div>
                </div>

                <div class="print-notes-list">
                    <p>
                        <span class="font-bold">2.</span>
                        يجب على ولي الأمر زيارة المدرسة لمتابعة مستوى ابنه/ابنته الدراسي والسلوكي، وذلك في الأوقات المحددة بعد الساعة 10:00 صباحاً للفترة الصباحية، وبعد الساعة 2:00 مساءً للفترة المسائية.
                    </p>

                    <p>
                        <span class="font-bold">3.</span>
                        في حالة تغيّر أي بيانات واردة في هذه البطاقة، يرجى إبلاغ المرشد الاجتماعي بذلك.
                    </p>

                    <p>
                        <span class="font-bold">4.</span>
                        أقرّ أنا ولي الأمر بأنني قد قمت بتعبئة هذه البيانات بنفسي وأتحمل مسؤولية صحتها.
                    </p>
                </div>

                <div class="print-field mt-4 max-w-sm">
                    <span class="print-field-label">توقيع ولي الأمر:</span>
                    <span class="print-field-value">&nbsp;</span>
                </div>
            </section>

            <x-print.report-footer :printed-by="auth('school')->user()->name" />
        </x-print.report-sheet>
    </x-slot>
</x-print-layout>
