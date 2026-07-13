<?php

return [
    'user' => [
        'label' => 'المُستخدمين',
        'values' => [
            'user:role:view' => 'عرض المُستخدمين',
            'user:role:create' => 'إضافة مستخدمين',
            'user:role:update' => 'تعديل المُستخدمين',
            'user:role:delete' => 'حذف المُستخدمين',
            'user:role:state-update' => 'تحديث حالة المُستخدمين',
        ],
    ],

    'municipal' => [
        'label' => 'البلديات',
        'values' => [
            'municipal:role:view' => 'عرض البلديات',
            'municipal:role:create' => 'إضافة بلديات',
            'municipal:role:update' => 'تعديل البلديات',
            'municipal:role:delete' => 'حذف البلديات',
        ],
    ],

    'academic-year' => [
        'label' => 'السنوات الدراسية',
        'values' => [
            'academic-year:role:view' => 'عرض السنوات الدراسية',
            'academic-year:role:create' => 'إضافة سنوات دراسية',
            'academic-year:role:update' => 'تعديل السنوات الدراسية',
            'academic-year:role:delete' => 'حذف السنوات الدراسية',
            'academic-year:role:close' => 'إغلاق السنوات الدراسية',
        ],
    ],

    'grade-level' => [
        'label' => 'الصفوف الدراسية',
        'values' => [
            'grade-level:role:view' => 'عرض الصفوف الدراسية',
            'grade-level:role:create' => 'إضافة صفوف دراسية',
            'grade-level:role:update' => 'تعديل الصفوف الدراسية',
            'grade-level:role:delete' => 'حذف الصفوف الدراسية',
            'grade-level:role:state-update' => 'تحديث حالة الصفوف الدراسية',
        ],
    ],

    'subject-classification' => [
        'label' => 'تصنيف المقررات الدراسية',
        'values' => [
            'subject-classification:role:view' => 'عرض تصنيف المقررات الدراسية',
            'subject-classification:role:create' => 'إضافة تصنيف المقررات الدراسية',
            'subject-classification:role:update' => 'تعديل تصنيف المقررات الدراسية',
            'subject-classification:role:delete' => 'حذف تصنيف المقررات الدراسية',
        ],
    ],

    'subject' => [
        'label' => 'المقررات الدراسية',
        'values' => [
            'subject:role:view' => 'عرض المقررات الدراسية',
            'subject:role:create' => 'إضافة المقررات الدراسية',
            'subject:role:update' => 'تعديل المقررات الدراسية',
            'subject:role:delete' => 'حذف المقررات الدراسية',
            'subject:role:state-update' => 'تحديث حالة المقررات الدراسية',
        ],
    ],

    'class-period' => [
        'label' => 'الحصص الدراسية',
        'values' => [
            'class-period:role:view' => 'عرض الحصص الدراسية',
            'class-period:role:create' => 'إضافة الحصص الدراسية',
            'class-period:role:update' => 'تعديل الحصص الدراسية',
            'class-period:role:delete' => 'حذف الحصص الدراسية',
        ],
    ],

    'warehouse' => [
        'label' => 'المخازن',
        'values' => [
            'warehouse:role:view' => 'عرض المخازن',
            'warehouse:role:create' => 'إضافة المخازن',
            'warehouse:role:update' => 'تعديل المخازن',
            'warehouse:role:delete' => 'حذف المخازن',
        ],
    ],

    'education-monitor' => [
        'label' => 'المُراقبات',
        'values' => [
            'education-monitor:role:view' => 'عرض المُراقبات',
            'education-monitor:role:create' => 'إضافة المُراقبات',
            'education-monitor:role:update' => 'تعديل المُراقبات',
            'education-monitor:role:delete' => 'حذف المُراقبات',
        ],
    ],

    'education-services-office' => [
        'label' => 'مكاتب الخدمات التعليمية',
        'values' => [
            'education-services-office:role:view' => 'عرض مكاتب الخدمات التعليمية',
            'education-services-office:role:create' => 'إضافة مكاتب الخدمات التعليمية',
            'education-services-office:role:update' => 'تعديل مكاتب الخدمات التعليمية',
            'education-services-office:role:delete' => 'حذف مكاتب الخدمات التعليمية',
        ],
    ],

    'school' => [
        'label' => 'المدارس',
        'values' => [
            'school:role:view' => 'عرض المدارس',
            'school:role:create' => 'إضافة المدارس',
            'school:role:update' => 'تعديل المدارس',
            'school:role:delete' => 'حذف المدارس',
            'school:role:add-grade-level' => 'إضافة صفوف دراسية للمدارس',
            'school:role:remove-grade-level' => 'حذف صفوف دراسية من المدارس',
            'school:role:reset-classroom-distribution' => 'إعادة تعيين توزيع الفصول الدراسية',
        ],
    ],

    'student' => [
        'label' => 'الطلاب',
        'values' => [
            'student:role:view' => 'عرض الطلاب',
            'student:role:create' => 'إضافة الطلاب',
            'student:role:update' => 'تعديل الطلاب',
            'student:role:delete' => 'حذف الطلاب',
            'student:role:add-transferred-student' => 'إضافة طلاب مُنتقلين',
            'student:role:transfer-student-out-of-school' => 'نقل طلاب خارج المدرسة',
            'student:role:transfer-student-out-of-monitor' => 'نقل طلاب خارج المُراقبة',

            'student:role:enroll-in-grade-level' => 'تسجيل الطلاب في صف دراسي',
            'student:role:enroll-in-classroom' => 'تسجيل الطلاب في فصل دراسي',

            'student:role:view-psychosocial-card' => 'عرض البطاقة الإجتماعية والنفسية للطلاب',
            'student:role:update-psychosocial-card' => 'تحديث البطاقة الإجتماعية والنفسية للطلاب',
            'student:role:print-psychosocial-card' => 'طباعة البطاقة الإجتماعية والنفسية للطلاب',

            'student:role:view-academic-record' => 'عرض السجل الدراسي للطلاب',
            'student:role:create-academic-record' => 'إنشاء السجل الدراسي للطلاب',
        ],
    ],

    'classroom' => [
        'label' => 'الفصول الدراسية',
        'values' => [
            'classroom:role:view' => 'عرض الفصول الدراسية',
            'classroom:role:create' => 'إضافة الفصول الدراسية',
            'classroom:role:update' => 'تعديل الفصول الدراسية',
            'classroom:role:delete' => 'حذف الفصول الدراسية',
        ],
    ],

    'class-schedule' => [
        'label' => 'الجداول الدراسية',
        'values' => [
            'class-schedule:role:view' => 'عرض الجداول الدراسية',
            'class-schedule:role:create' => 'إضافة الجداول الدراسية',
            'class-schedule:role:update' => 'تعديل الجداول الدراسية',
            'class-schedule:role:delete' => 'حذف الجداول الدراسية',
            'class-schedule:role:print' => 'طباعة الجداول الدراسية',
        ],
    ],

    'classroom-distribution' => [
        'label' => 'توزيع الطلاب على الفصول الدراسية',
        'values' => [
            'classroom-distribution:role:view' => 'عرض توزيع الطلاب على الفصول الدراسية',
            'classroom-distribution:role:distribute' => 'تنفيذ توزيع الطلاب على الفصول الدراسية',
            'classroom-distribution:role:finalize' => 'اعتماد توزيع الطلاب على الفصول الدراسية',
        ],
    ],

    'book-distribution' => [
        'label' => 'توزيع الكُتب المدرسية',
        'values' => [
            'book-distribution:role:view' => 'عرض توزيع الكُتب المدرسية',
            'book-distribution:role:distribute' => 'تنفيذ توزيع الكُتب المدرسية',
            'book-distribution:role:view-statistics' => 'عرض إحصائيات توزيع الكُتب المدرسية',
        ],
    ],

    'school-staff' => [
        'label' => 'الموظفين',
        'values' => [
            'school-staff:role:view' => 'عرض الموظفين',
            'school-staff:role:create' => 'إضافة الموظفين',
            'school-staff:role:update' => 'تعديل الموظفين',
            'school-staff:role:delete' => 'حذف الموظفين',
        ],
    ],

    'report' => [
        'label' => 'التقارير',
        'values' => [
            'report:role:education-monitor:view' => 'عرض تقرير المُراقبات',
            'report:role:education-monitor:print' => 'طباعة تقرير المُراقبات',

            'report:role:education-services-office:view' => 'عرض تقرير مكاتب الخدمات التعليمية',
            'report:role:education-services-office:print' => 'طباعة تقرير مكاتب الخدمات التعليمية',

            'report:role:school:view' => 'عرض تقرير المدارس',
            'report:role:school:print' => 'طباعة تقرير المدارس',

            'report:role:student-count-by-grade-level:view' => 'عرض تقرير إحصائية الطلاب حسب الصفوف الدراسية',
            'report:role:student-count-by-grade-level:print' => 'طباعة تقرير إحصائية الطلاب حسب الصفوف الدراسية',

            'report:role:student-by-grade-level:view' => 'عرض تقرير الطلاب حسب الصفوف الدراسية',
            'report:role:student-by-grade-level:print' => 'طباعة تقرير الطلاب حسب الصفوف الدراسية',

            'report:role:student-by-classroom:view' => 'عرض تقرير الطلاب حسب الفصول الدراسية',
            'report:role:student-by-classroom:print' => 'طباعة تقرير الطلاب حسب الفصول الدراسية',

            'report:role:attendance:view' => 'عرض تقرير الغياب',
            'report:role:attendance:print' => 'طباعة تقرير الغياب',
        ],
    ],
];
