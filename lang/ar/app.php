<?php

return [
    'common' => [
        'gender' => [
            'm' => 'ذكر',
            'male' => 'ذكر',
            'f' => 'أنثى',
            'female' => 'أنثى',
        ],
    ],

    'enums' => [
        'gender' => [
            'male' => 'ذكر',
            'female' => 'أنثى',
        ],

        'user_roles' => [
            'employee' => 'موظف',
            'manager' => 'مدير',
        ],

        'user_scopes' => [
            'administration' => 'الإدارة',
            'warehouse' => 'المخزن',
            'education_monitor' => 'المُراقبة',
            'education_services_office' => 'مكتب الخدمات التعليمية',
            'school' => 'المدرسة',

            'create' => [
                'administration' => 'مُستخدم إدارة',
                'warehouse' => 'مُستخدم مخزن',
                'education_monitor' => 'مُستخدم مُراقبة',
                'education_services_office' => 'مُستخدم مكتب',
                'school' => 'مُستخدم مدرسة',
            ],
        ],

        'school_academic_periods' => [
            'morning' => 'صباحية',
            'evening' => 'مسائية',
            'dual_period' => 'فترتين (صباحية و مسائية)',
        ],

        'school_types' => [
            'public' => 'عامة',
            'private' => 'خاصة',

            'plural' => [
                'public' => 'مدارس عامة',
                'private' => 'مدارس خاصة',
            ],
        ],

        'school_branch_types' => [
            'main' => 'رئيسي',
            'sub' => 'فرعي',
        ],

        'school_building_types' => [
            'school' => 'مدرسة',
            'villa' => 'فيلا',
            'flat' => 'شقة',
            'otherwise' => 'أخرى',
        ],

        'school_students_gender' => [
            'boys' => 'بنين',
            'girls' => 'بنات',
            'mixed' => 'مختلط',
        ],

        'school_educational_stages' => [
            'kindergarten' => 'رياض الأطفال',
            'primary_education' => 'التعليم الأساسي',
            'secondary_education' => 'التعليم الثانوي',
        ],

        'grade_levels' => [
            'kg_1' => 'روضة - المستوى الأول',
            'kg_2' => 'روضة - المستوى الثاني',

            'grade_1' => 'الصف الأول إبتدائي',
            'grade_2' => 'الصف الثاني إبتدائي',
            'grade_3' => 'الصف الثالث إبتدائي',
            'grade_4' => 'الصف الرابع إبتدائي',
            'grade_5' => 'الصف الخامس إبتدائي',
            'grade_6' => 'الصف السادس إبتدائي',
            'grade_7' => 'الصف السابع إعدادي',
            'grade_8' => 'الصف الثامن إعدادي',
            'grade_9' => 'الصف التاسع إعدادي',

            'grade_10' => 'الصف الأول ثانوي',
            'grade_11_scientific' => 'الصف الثاني ثانوي - علمي',
            'grade_11_literary' => 'الصف الثاني ثانوي - أدبي',
            'grade_12_scientific' => 'الصف الثالث ثانوي - علمي',
            'grade_12_literary' => 'الصف الثالث ثانوي - أدبي',
        ],

        'student_registration_statuses' => [
            'new' => 'مُستجد',
            'repeater' => 'مُعيد',
            'exceptional_year' => 'سنة استثنائية',
            'complementary' => 'تكميلي',
        ],

        'student_exam_enrollment_statuses' => [
            'registered' => 'مُسجل',
            'deferred' => 'مُتعثر',
        ],

        'academic_record_statuses' => [
            'passed' => 'ناجح',
            'promoted' => 'مُرحل',
            'failed' => 'راسب',
        ],

        'academic_record_ratings' => [
            'excellent' => 'ممتاز',
            'very_good' => 'جيد جداً',
            'good' => 'جيد',
            'satisfactory' => 'مقبول',
        ],

        'student_living_situations' => [
            'with_parents' => 'مع والديه',
            'with_father' => 'مع والده',
            'with_mother' => 'مع والدته',
            'with_relatives' => 'مع الأقارب',
            'foster_family' => 'الأسرة البديلة',
            'other' => 'أخرى',
        ],

        'student_family_situation_reasons' => [
            'parents_separation' => 'انفصال الوالدين',
            'mother_death' => 'وفاة الأم',
            'father_death' => 'وفاة الأب',
        ],

        'health_levels' => [
            'weak' => 'ضعيف',
            'normal' => 'عادي',
            'good' => 'جيد',
            'excellent' => 'ممتاز',
        ],

        'family_incomes' => [
            'weak' => 'ضعيف',
            'average' => 'متوسط',
            'above_average' => 'فوق المتوسط',
        ],

        'accommodation_types' => [
            'owned' => 'ملك',
            'rental' => 'إيجار',
            'displaced' => 'نازح',
            'other' => 'أخرى',
        ],

        'accommodation_forms' => [
            'flat' => 'شقة',
            'regular_house' => 'منزل عادي',
            'villa' => 'فيلا',
            'with_relatives' => 'مع الأقارب',
            'other' => 'أخرى',
        ],

        'student_behavioral_problems' => [
            'shyness' => 'الخجل',
            'introversion_isolation' => 'الانطواء والعزلة',
            'fear' => 'الخوف',
            'lack_of_self_confidence' => 'عدم الثقة بالنفس',
            'lying' => 'الكذب',
            'sleep_disorders' => 'اضطرابات النوم',
            'attention_deficit' => 'تشتت الانتباه',
            'thumb_sucking' => 'مص الإبهام',
            'nail_biting' => 'قضم الأظافر',
            'involuntary_urination' => 'التبول اللاإرادي',
            'distraction' => 'السرحان',
            'lack_of_motivation' => 'ضعف الدافعية للمدرسة (عدم الرغبة)',
            'aggressive_behavior' => 'السلوك العدواني',
            'speech_problems' => 'مشاكل في النطق',
            'hyperactivity' => 'الإفراط الحركي',
            'other' => 'غير ذلك',
        ],

        'days_of_week' => [
            0 => 'الأحد',
            1 => 'الاثنين',
            2 => 'الثلاثاء',
            3 => 'الأربعاء',
            4 => 'الخميس',
            5 => 'الجمعة',
            6 => 'السبت',
        ],

        'classroom_distribution_methods' => [
            'manual' => 'توزيع يدوي',
            'random' => 'توزيع عشوائي',

            'description' => [
                'random' => 'توزيع الطلاب تلقائياً على الفصول الدراسية حسب السعة المتاحة.',
                'manual' => 'اختيار الطلاب وتعيينهم يدوياً في فصل دراسي محدد.',
            ],
        ],

        'classroom_distribution_reset_scopes' => [
            'all' => 'إعادة تعيين جميع الصفوف الدراسية',
            'selected' => 'إعادة تعيين صفوف دراسية محددة',

            'description' => [
                'all' => 'إعادة تعيين توزيع الفصول لجميع الصفوف الدراسية في المدرسة.',
                'selected' => 'إعادة تعيين توزيع الفصول للصفوف الدراسية المحددة فقط.',
            ],
        ],
    ],

    'states' => [
        'user' => [
            'state' => [
                'labels' => [
                    'activated' => 'مفعّل',
                    'deactivated' => 'معطّل',
                ],
                'actions' => [
                    'activated' => 'تفعيل',
                    'deactivated' => 'إلغاء التفعيل',
                ],
            ],
            'request_state' => [
                'labels' => [
                    'pending' => 'قيد المراجعة',
                    'approved' => 'مُعتمد',
                    'rejected' => 'مرفوض',
                ],
                'actions' => [
                    'pending' => 'مراجعة',
                    'approved' => 'اعتماد',
                    'rejected' => 'رفض',
                ],
            ],
        ],
    ],
];
