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
