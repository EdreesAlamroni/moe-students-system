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

        'exam_enrollment_statuses' => [
            'registered' => 'مُسجل',
            'deferred' => 'مُتعثر',
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
