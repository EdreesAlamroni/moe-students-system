<?php

namespace Database\Seeders;

use App\Enums\GradeLevelEnum;
use App\Models\GradeLevel;
use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $gradeLevelIds = GradeLevel::query()->pluck('id', 'code');

        foreach ($this->definitions() as $grade => $subjects) {
            foreach ($subjects as $subject) {
                Subject::firstOrCreate(
                    ['code' => $subject['code']],
                    [
                        'grade_level_id' => $gradeLevelIds[$grade],
                        ...$this->attributes($subject),
                    ],
                );
            }
        }
    }

    protected function definitions(): array
    {
        return [
            GradeLevelEnum::GRADE_1->value => [
                [
                    'code' => 'ISLAMIC_EDUCATION_G01',
                    'name' => 'التربية الإسلامية'],
                [
                    'code' => 'ARABIC_LANGUAGE_G01',
                    'name' => 'اللغة العربية',
                    'description' => 'اكتساب مهارات القراءة والكتابة الأساسية.',
                ],
                [
                    'code' => 'MATHEMATICS_PART_1_G01',
                    'name' => 'الرياضيات - الجزء الاول',
                    'description' => 'اكتساب مهارات الرياضيات.',
                ],
                [
                    'code' => 'MATHEMATICS_PART_2_G01',
                    'name' => 'الرياضيات - الجزء الثاني',
                    'description' => 'اكتساب مهارات الرياضيات.',
                ],
                [
                    'code' => 'COMPUTER_SCIENCE_G01',
                    'name' => 'الحاسوب', 'needs_lab' => true,
                    'description' => 'اكتساب مهارات الحاسوب.',
                ],
                [
                    'code' => 'ENGLISH_LANGUAGE_G01',
                    'name' => 'اللغة الانجليزية',
                    'description' => 'English for Libya: Primary 1 Pupil’s Book.',
                ],
            ],

            GradeLevelEnum::GRADE_2->value => [
                [
                    'code' => 'ISLAMIC_EDUCATION_G02',
                    'name' => 'التربية الإسلامية'],
                [
                    'code' => 'ARABIC_LANGUAGE_G02',
                    'name' => 'اللغة العربية',
                    'description' => 'اكتساب مهارات القراءة والكتابة الأساسية.',
                ],
                [
                    'code' => 'MATHEMATICS_PART_1_G02',
                    'name' => 'الرياضيات - الجزء الاول',
                    'description' => 'اكتساب مهارات الرياضيات.',
                ],
                [
                    'code' => 'MATHEMATICS_PART_2_G02',
                    'name' => 'الرياضيات - الجزء الثاني',
                    'description' => 'اكتساب مهارات الرياضيات.',
                ],
                [
                    'code' => 'COMPUTER_SCIENCE_G02',
                    'name' => 'الحاسوب', 'needs_lab' => true,
                    'description' => 'اكتساب مهارات الحاسوب.',
                ],
                [
                    'code' => 'ENGLISH_LANGUAGE_G02',
                    'name' => 'اللغة الانجليزية',
                    'description' => 'English for Libya: Primary 2 Pupil’s Book.',
                ],
            ],

            GradeLevelEnum::GRADE_3->value => [
                [
                    'code' => 'ISLAMIC_EDUCATION_G03',
                    'name' => 'التربية الإسلامية'],
                [
                    'code' => 'ARABIC_LANGUAGE_G03',
                    'name' => 'اللغة العربية',
                    'description' => 'اكتساب مهارات القراءة والكتابة الأساسية.',
                ],
                [
                    'code' => 'MATHEMATICS_PART_1_G03',
                    'name' => 'الرياضيات - الجزء الاول',
                    'description' => 'اكتساب مهارات الرياضيات.',
                ],
                [
                    'code' => 'MATHEMATICS_PART_2_G03',
                    'name' => 'الرياضيات - الجزء الثاني',
                    'description' => 'اكتساب مهارات الرياضيات.',
                ],
                [
                    'code' => 'SCIENCE_PART_1_G03',
                    'name' => 'العلوم - الجزء الاول', 'needs_lab' => true,
                    'description' => 'اكتساب المهارات العلوم.',
                ],
                [
                    'code' => 'SCIENCE_PART_2_G03',
                    'name' => 'العلوم - الجزء الثاني', 'needs_lab' => true,
                    'description' => 'اكتساب المهارات العلوم.',
                ],
                [
                    'code' => 'COMPUTER_SCIENCE_G03',
                    'name' => 'الحاسوب', 'needs_lab' => true,
                    'description' => 'اكتساب مهارات الحاسوب.',
                ],
                [
                    'code' => 'ARABIC_CALLIGRAPHY_G03',
                    'name' => 'كراسة الخط العربي', 'included_in_total_score' => false,
                    'description' => 'لتحسين خط النسخ.',
                ],
                [
                    'code' => 'ENGLISH_LANGUAGE_G03',
                    'name' => 'اللغة الانجليزية',
                    'description' => 'English for Libya: Primary 3 Pupil’s Book.',
                ],
            ],

            GradeLevelEnum::GRADE_4->value => [
                [
                    'code' => 'ISLAMIC_EDUCATION_G04',
                    'name' => 'التربية الإسلامية'],
                [
                    'code' => 'ARABIC_LANGUAGE_G04',
                    'name' => 'اللغة العربية',
                    'description' => 'اكتساب مهارات القراءة والكتابة الأساسية.',
                ],
                [
                    'code' => 'MATHEMATICS_PART_1_G04',
                    'name' => 'الرياضيات - الجزء الاول',
                    'description' => 'اكتساب مهارات الرياضيات.',
                ],
                [
                    'code' => 'MATHEMATICS_PART_2_G04',
                    'name' => 'الرياضيات - الجزء الثاني',
                    'description' => 'اكتساب مهارات الرياضيات.',
                ],
                [
                    'code' => 'SCIENCE_PART_1_G04',
                    'name' => 'العلوم - الجزء الاول', 'needs_lab' => true,
                    'description' => 'اكتساب المهارات العلوم.',
                ],
                [
                    'code' => 'SCIENCE_PART_2_G04',
                    'name' => 'العلوم - الجزء الثاني', 'needs_lab' => true,
                    'description' => 'اكتساب المهارات العلوم.',
                ],
                [
                    'code' => 'COMPUTER_SCIENCE_G04',
                    'name' => 'الحاسوب', 'needs_lab' => true,
                    'description' => 'اكتساب مهارات الحاسوب.',
                ],
                [
                    'code' => 'ARABIC_CALLIGRAPHY_G04',
                    'name' => 'كراسة الخط العربي', 'included_in_total_score' => false,
                    'description' => 'لتحسين خط النسخ.',
                ],
                [
                    'code' => 'ENGLISH_LANGUAGE_G04',
                    'name' => 'اللغة الانجليزية',
                    'description' => 'English for Libya: Primary 4 Pupil’s Book.',
                ],
            ],

            GradeLevelEnum::GRADE_5->value => [
                [
                    'code' => 'ISLAMIC_EDUCATION_G05',
                    'name' => 'التربية الإسلامية'],
                [
                    'code' => 'ARABIC_LANGUAGE_G05',
                    'name' => 'اللغة العربية',
                    'description' => 'اكتساب مهارات القراءة والكتابة الأساسية.',
                ],
                [
                    'code' => 'MATHEMATICS_G05',
                    'name' => 'الرياضيات',
                    'description' => 'اكتساب مهارات الرياضيات.',
                ],
                [
                    'code' => 'SCIENCE_PART_1_G05',
                    'name' => 'العلوم - الجزء الاول', 'needs_lab' => true,
                    'description' => 'اكتساب المهارات العلوم.',
                ],
                [
                    'code' => 'SCIENCE_PART_2_G05',
                    'name' => 'العلوم - الجزء الثاني', 'needs_lab' => true,
                    'description' => 'اكتساب المهارات العلوم.',
                ],
                [
                    'code' => 'COMPUTER_SCIENCE_G05',
                    'name' => 'الحاسوب', 'needs_lab' => true,
                    'description' => 'اكتساب مهارات الحاسوب.',
                ],
                [
                    'code' => 'SOCIAL_STUDIES_G05',
                    'name' => 'الاجتماعيات',
                    'description' => 'التاريخ والجغرافيا.',
                ],
                [
                    'code' => 'NATIONAL_EDUCATION_G05',
                    'name' => 'التربية الوطنية', 'included_in_total_score' => false],
                [
                    'code' => 'ARABIC_CALLIGRAPHY_G05',
                    'name' => 'كراسة الخط العربي', 'included_in_total_score' => false,
                    'description' => 'لتحسين خط النسخ والرقعة.',
                ],
                [
                    'code' => 'ENGLISH_LANGUAGE_G05',
                    'name' => 'اللغة الانجليزية',
                    'description' => 'English for Libya: Primary 5 Pupil’s Book.',
                ],
            ],

            GradeLevelEnum::GRADE_6->value => [
                [
                    'code' => 'ISLAMIC_EDUCATION_G06',
                    'name' => 'التربية الإسلامية'],
                [
                    'code' => 'ARABIC_LANGUAGE_G06',
                    'name' => 'اللغة العربية',
                    'description' => 'اكتساب مهارات القراءة والكتابة الأساسية.',
                ],
                [
                    'code' => 'MATHEMATICS_G06',
                    'name' => 'الرياضيات',
                    'description' => 'اكتساب مهارات الرياضيات.',
                ],
                [
                    'code' => 'SCIENCE_PART_1_G06',
                    'name' => 'العلوم - الجزء الاول', 'needs_lab' => true,
                    'description' => 'اكتساب المهارات العلوم.',
                ],
                [
                    'code' => 'SCIENCE_PART_2_G06',
                    'name' => 'العلوم - الجزء الثاني', 'needs_lab' => true,
                    'description' => 'اكتساب المهارات العلوم.',
                ],
                [
                    'code' => 'COMPUTER_SCIENCE_G06',
                    'name' => 'الحاسوب', 'needs_lab' => true,
                    'description' => 'اكتساب مهارات الحاسوب.',
                ],
                [
                    'code' => 'SOCIAL_STUDIES_G06',
                    'name' => 'الاجتماعيات',
                    'description' => 'التاريخ والجغرافيا.',
                ],
                [
                    'code' => 'NATIONAL_EDUCATION_G06',
                    'name' => 'التربية الوطنية', 'included_in_total_score' => false],
                [
                    'code' => 'ART_EDUCATION_G06',
                    'name' => 'التربية الفنية', 'included_in_total_score' => false],
                [
                    'code' => 'ENGLISH_LANGUAGE_G06',
                    'name' => 'اللغة الانجليزية',
                    'description' => 'English for Libya: Primary 6 Pupil’s Book.',
                ],
            ],

            GradeLevelEnum::GRADE_7->value => [
                [
                    'code' => 'ISLAMIC_EDUCATION_G07',
                    'name' => 'التربية الإسلامية'],
                [
                    'code' => 'ARABIC_LANGUAGE_G07',
                    'name' => 'اللغة العربية',
                    'description' => 'اكتساب مهارات القراءة والكتابة الأساسية.',
                ],
                [
                    'code' => 'MATHEMATICS_G07',
                    'name' => 'الرياضيات',
                    'description' => 'اكتساب مهارات الرياضيات.',
                ],
                [
                    'code' => 'SCIENCE_PART_1_G07',
                    'name' => 'العلوم - الجزء الاول', 'needs_lab' => true,
                    'description' => 'اكتساب المهارات العلوم.',
                ],
                [
                    'code' => 'SCIENCE_PART_2_G07',
                    'name' => 'العلوم - الجزء الثاني', 'needs_lab' => true,
                    'description' => 'اكتساب المهارات العلوم.',
                ],
                [
                    'code' => 'COMPUTER_SCIENCE_G07',
                    'name' => 'الحاسوب', 'needs_lab' => true,
                    'description' => 'اكتساب مهارات الحاسوب.',
                ],
                [
                    'code' => 'HISTORY_GEOGRAPHY_G07',
                    'name' => 'التاريخ',
                    'description' => 'تاريخ ليبيا والعالم القديم.',
                ],
                [
                    'code' => 'GEOGRAPHY_G07',
                    'name' => 'الجغرافيا',
                    'description' => 'جغرافيا ليبيا.',
                ],
                [
                    'code' => 'NATIONAL_EDUCATION_G07',
                    'name' => 'التربية الوطنية', 'included_in_total_score' => false],
                [
                    'code' => 'ENGLISH_LANGUAGE_G07',
                    'name' => 'اللغة الانجليزية',
                    'description' => 'English for Libya: Primary 7 Pupil’s Book.',
                ],
            ],

            GradeLevelEnum::GRADE_8->value => [
                [
                    'code' => 'ISLAMIC_EDUCATION_G08',
                    'name' => 'التربية الإسلامية'],
                [
                    'code' => 'ARABIC_LANGUAGE_G08',
                    'name' => 'اللغة العربية',
                    'description' => 'اكتساب مهارات القراءة والكتابة الأساسية.',
                ],
                [
                    'code' => 'MATHEMATICS_G08',
                    'name' => 'الرياضيات',
                    'description' => 'اكتساب مهارات الرياضيات.',
                ],
                [
                    'code' => 'SCIENCE_PART_1_G08',
                    'name' => 'العلوم - الجزء الاول', 'needs_lab' => true,
                    'description' => 'اكتساب المهارات العلوم.',
                ],
                [
                    'code' => 'SCIENCE_PART_2_G08',
                    'name' => 'العلوم - الجزء الثاني', 'needs_lab' => true,
                    'description' => 'اكتساب المهارات العلوم.',
                ],
                [
                    'code' => 'COMPUTER_SCIENCE_G08',
                    'name' => 'الحاسوب', 'needs_lab' => true,
                    'description' => 'اكتساب مهارات الحاسوب.',
                ],
                [
                    'code' => 'HISTORY_GEOGRAPHY_G08',
                    'name' => 'التاريخ',
                    'description' => 'التاريخ الاسلامي.',
                ],
                [
                    'code' => 'GEOGRAPHY_G08',
                    'name' => 'الجغرافيا',
                    'description' => 'جغرافيا الوطن العربي.',
                ],
                [
                    'code' => 'NATIONAL_EDUCATION_G08',
                    'name' => 'التربية الوطنية', 'included_in_total_score' => false],
                [
                    'code' => 'ENGLISH_LANGUAGE_G08',
                    'name' => 'اللغة الانجليزية',
                    'description' => 'English for Libya: Primary 8 Pupil’s Book.',
                ],
            ],

            GradeLevelEnum::GRADE_9->value => [
                [
                    'code' => 'ISLAMIC_EDUCATION_G09',
                    'name' => 'التربية الإسلامية'],
                [
                    'code' => 'ARABIC_LANGUAGE_G09',
                    'name' => 'اللغة العربية',
                    'description' => 'اكتساب مهارات القراءة والكتابة الأساسية.',
                ],
                [
                    'code' => 'MATHEMATICS_G09',
                    'name' => 'الرياضيات',
                    'description' => 'اكتساب مهارات الرياضيات.',
                ],
                [
                    'code' => 'SCIENCE_PART_1_G09',
                    'name' => 'العلوم - الجزء الاول', 'needs_lab' => true,
                    'description' => 'اكتساب المهارات العلوم.',
                ],
                [
                    'code' => 'SCIENCE_PART_2_G09',
                    'name' => 'العلوم - الجزء الثاني', 'needs_lab' => true,
                    'description' => 'اكتساب المهارات العلوم.',
                ],
                [
                    'code' => 'COMPUTER_SCIENCE_G09',
                    'name' => 'الحاسوب', 'needs_lab' => true,
                    'description' => 'اكتساب مهارات الحاسوب.',
                ],
                [
                    'code' => 'HISTORY_GEOGRAPHY_G09',
                    'name' => 'التاريخ',
                    'description' => 'تاريخ ليبيا الحديث والمعاصر.',
                ],
                [
                    'code' => 'GEOGRAPHY_G09',
                    'name' => 'الجغرافيا',
                    'description' => 'جغرافية العالم.',
                ],
                [
                    'code' => 'NATIONAL_EDUCATION_G09',
                    'name' => 'التربية الوطنية', 'included_in_total_score' => false],
                [
                    'code' => 'ENGLISH_LANGUAGE_G09',
                    'name' => 'اللغة الانجليزية',
                    'description' => 'English for Libya: Primary 9 Pupil’s Book.',
                ],
            ],

            GradeLevelEnum::GRADE_10->value => [
                [
                    'code' => 'ISLAMIC_EDUCATION_G10',
                    'name' => 'التربية الإسلامية'],
                [
                    'code' => 'ARABIC_LANGUAGE_GRAMMAR_G10',
                    'name' => 'النحو والصرف والإملاء',
                    'description' => 'تعليم قواعد اللغة العربية.',
                ],
                [
                    'code' => 'ARABIC_LITERATURE_G10',
                    'name' => 'الدراسات الأدبية',
                    'description' => 'دراسة الأدب العربي.',
                ],
                [
                    'code' => 'MATHEMATICS_PART_1_G10',
                    'name' => 'الرياضيات - الجزء الاول',
                    'description' => 'اكتساب مهارات الرياضيات.',
                ],
                [
                    'code' => 'MATHEMATICS_PART_2_G10',
                    'name' => 'الرياضيات - الجزء الثاني',
                    'description' => 'اكتساب مهارات الرياضيات.',
                ],
                [
                    'code' => 'CHEMISTRY_G10',
                    'name' => 'الكيمياء', 'needs_lab' => true,
                    'description' => 'دراسة الكيمياء الأساسية.',
                ],
                [
                    'code' => 'BIOLOGY_G10',
                    'name' => 'الأحياء', 'needs_lab' => true,
                    'description' => 'دراسة الأحياء.',
                ],
                [
                    'code' => 'PHYSICS_G10',
                    'name' => 'الفيزياء', 'needs_lab' => true,
                    'description' => 'دراسة الفيزياء الأساسية.',
                ],
                [
                    'code' => 'INFORMATION_TECHNOLOGY_G10',
                    'name' => 'تقنية المعلومات', 'needs_lab' => true,
                    'description' => 'تعليم أساسيات تقنية المعلومات.',
                ],
                [
                    'code' => 'HISTORY_GEOGRAPHY_G10',
                    'name' => 'التاريخ',
                    'description' => 'تاريخ الوطن العربي في العصر القديم.',
                ],
                [
                    'code' => 'GEOGRAPHY_G10',
                    'name' => 'الجغرافيا',
                    'description' => 'مبادئ الجغرافية العامة.',
                ],
                [
                    'code' => 'SOCIOLOGY_G10',
                    'name' => 'علم الاجتماع',
                    'description' => 'تمهيد علم الاجتماع.',
                ],
                [
                    'code' => 'NATIONAL_EDUCATION_G10',
                    'name' => 'التربية الوطنية', 'included_in_total_score' => false],
                [
                    'code' => 'ENGLISH_LANGUAGE_G10',
                    'name' => 'اللغة الانجليزية',
                    'description' => 'English for Libya- Secondary 1 Course Book.',
                ],
            ],

            GradeLevelEnum::GRADE_11_LITERARY->value => [
                [
                    'code' => 'ISLAMIC_EDUCATION_G11_LITERARY',
                    'name' => 'التربية الإسلامية'],
                [
                    'code' => 'RHETORIC_G11_LITERARY',
                    'name' => 'البلاغة',
                    'description' => 'المعاني و البيان و البديع.',
                ],
                [
                    'code' => 'LITERATURE_AND_TEXTS_G11_LITERARY',
                    'name' => 'الأدب والنصوص',
                    'description' => 'العصر العباسي.',
                ],
                [
                    'code' => 'READING_AND_WRITING_G11_LITERARY',
                    'name' => 'المطالعة والانشاء'],
                [
                    'code' => 'ARABIC_GRAMMAR_AND_SYNTAX_G11_LITERARY',
                    'name' => 'النحو والصرف والإملاء'],
                [
                    'code' => 'PHILOSOPHY_G11_LITERARY',
                    'name' => 'الفلسفة'],
                [
                    'code' => 'HISTORY_OF_THE_ARAB_WORLD_G11_LITERARY',
                    'name' => 'تاريخ الوطن العربي',
                    'description' => 'في العصر الوسيط.',
                ],
                [
                    'code' => 'GEOGRAPHY_OF_THE_ARAB_WORLD_G11_LITERARY',
                    'name' => 'جغرافية الوطن العربي'],
                [
                    'code' => 'SOCIOLOGY_G11_LITERARY',
                    'name' => 'علم الاجتماع'],
                [
                    'code' => 'STATISTICS_G11_LITERARY',
                    'name' => 'مبادئ الإحصاء'],
                [
                    'code' => 'PSYCHOLOGY_AND_EDUCATION_G11_LITERARY',
                    'name' => 'مبادئ التربية و علم النفس'],
                [
                    'code' => 'INFORMATION_TECHNOLOGY_G11_LITERARY',
                    'name' => 'تقنية المعلومات', 'needs_lab' => true],
                [
                    'code' => 'ENGLISH_LANGUAGE_G11_LITERARY',
                    'name' => 'اللغة الانجليزية',
                    'description' => 'English for Libya: Secondary 2 Course Book.',
                ],
            ],

            GradeLevelEnum::GRADE_12_LITERARY->value => [
                [
                    'code' => 'ISLAMIC_EDUCATION_G12_LITERARY',
                    'name' => 'التربية الإسلامية'],
                [
                    'code' => 'LITERATURE_AND_TEXTS_G12_LITERARY',
                    'name' => 'الأدب والنصوص',
                    'description' => 'العصر العباسي.',
                ],
                [
                    'code' => 'READING_AND_WRITING_G12_LITERARY',
                    'name' => 'المطالعة والانشاء'],
                [
                    'code' => 'LITERARY_CRITICISM_G12_LITERARY',
                    'name' => 'النقد الأدبي'],
                [
                    'code' => 'PHILOSOPHY_G12_LITERARY',
                    'name' => 'الفلسفة',
                    'description' => 'القيم (الخير - والجمال) وعلم المنطق.',
                ],
                [
                    'code' => 'ENVIRONMENTAL_GEOGRAPHY_G12_LITERARY',
                    'name' => 'جغرافية البيئة'],
                [
                    'code' => 'SOCIOLOGY_AND_APPLICATIONS_G12_LITERARY',
                    'name' => 'علم الاجتماع وتطبيقاته'],
                [
                    'code' => 'STATISTICS_G12_LITERARY',
                    'name' => 'الإحصاء'],
                [
                    'code' => 'PSYCHOLOGY_G12_LITERARY',
                    'name' => 'علم النفس'],
                [
                    'code' => 'INFORMATION_TECHNOLOGY_G12_LITERARY',
                    'name' => 'تقنية المعلومات', 'needs_lab' => true],
                [
                    'code' => 'ENGLISH_LANGUAGE_G12_LITERARY',
                    'name' => 'اللغة الانجليزية',
                    'description' => 'English for Libya: Secondary 3 Literary Course Book.',
                ],
            ],

            GradeLevelEnum::GRADE_11_SCIENTIFIC->value => [
                [
                    'code' => 'ISLAMIC_EDUCATION_G11_SCIENTIFIC',
                    'name' => 'التربية الإسلامية'],
                [
                    'code' => 'LINGUISTIC_STUDIES_G11_SCIENTIFIC',
                    'name' => 'الدراسات اللغوية',
                    'description' => 'النحو والصرف والإملاء',
                ],
                [
                    'code' => 'LITERARY_STUDIES_G11_SCIENTIFIC',
                    'name' => 'الدراسات الادبية',
                    'description' => 'البلاغة والشعر والنثر',
                ],
                [
                    'code' => 'MATHEMATICS_G11_SCIENTIFIC',
                    'name' => 'الرياضيات'],
                [
                    'code' => 'INFORMATION_TECHNOLOGY_G11_SCIENTIFIC',
                    'name' => 'تقنية المعلومات', 'needs_lab' => true],
                [
                    'code' => 'BIOLOGY_G11_SCIENTIFIC',
                    'name' => 'الأحياء', 'needs_lab' => true],
                [
                    'code' => 'CHEMISTRY_G11_SCIENTIFIC',
                    'name' => 'الكيمياء', 'needs_lab' => true],
                [
                    'code' => 'PHYSICS_G11_SCIENTIFIC',
                    'name' => 'الفيزياء', 'needs_lab' => true],
                [
                    'code' => 'STATISTICS_G11_SCIENTIFIC',
                    'name' => 'الإحصاء'],
                [
                    'code' => 'ENGLISH_LANGUAGE_G11_SCIENTIFIC',
                    'name' => 'اللغة الإنجليزية',
                    'description' => 'English for Libya: Secondary 2 Science Course Book.',
                ],
            ],

            GradeLevelEnum::GRADE_12_SCIENTIFIC->value => [
                [
                    'code' => 'ISLAMIC_EDUCATION_G12_SCIENTIFIC',
                    'name' => 'التربية الإسلامية'],
                [
                    'code' => 'LINGUISTIC_STUDIES_G12_SCIENTIFIC',
                    'name' => 'الدراسات اللغوية'],
                [
                    'code' => 'LITERARY_STUDIES_G12_SCIENTIFIC',
                    'name' => 'الدراسات الادبية'],
                [
                    'code' => 'MATHEMATICS_G12_SCIENTIFIC',
                    'name' => 'الرياضيات'],
                [
                    'code' => 'INFORMATION_TECHNOLOGY_G12_SCIENTIFIC',
                    'name' => 'تقنية المعلومات', 'needs_lab' => true],
                [
                    'code' => 'BIOLOGY_G12_SCIENTIFIC',
                    'name' => 'الأحياء', 'needs_lab' => true],
                [
                    'code' => 'CHEMISTRY_G12_SCIENTIFIC',
                    'name' => 'الكيمياء', 'needs_lab' => true],
                [
                    'code' => 'PHYSICS_PART_1_G12_SCIENTIFIC',
                    'name' => 'الفيزياء - الجزء الأول', 'needs_lab' => true,
                    'description' => 'الجزء الأول (الكهرباء والمغناطيسية والذرية)',
                ],
                [
                    'code' => 'STATISTICS_PART_2_G12_SCIENTIFIC',
                    'name' => 'الفيزياء - الجزء الثاني', 'needs_lab' => true,
                    'description' => 'الجزء الثاني (الميكانيكا)',
                ],
                [
                    'code' => 'STATISTICS_G12_SCIENTIFIC',
                    'name' => 'أسس الاحصاء'],
                [
                    'code' => 'ENGLISH_LANGUAGE_G12_SCIENTIFIC',
                    'name' => 'اللغة الإنجليزية',
                    'description' => 'English for Libya: Secondary 3 Science Course Book.',
                ],
            ],
        ];
    }

    protected function attributes(array $subject): array
    {
        return [
            'name' => $subject['name'],
            'included_in_total_score' => $subject['included_in_total_score'] ?? true,
            'needs_lab' => $subject['needs_lab'] ?? false,
            'description' => $subject['description'] ?? null,
        ];
    }
}
