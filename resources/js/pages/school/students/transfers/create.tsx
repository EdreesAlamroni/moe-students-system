import { Head } from '@inertiajs/react';

import type { Student } from '@/types';

import AddTransferredStudent from '@/components/shared/students/add-transferred-student';

import { index as studentsIndex } from '@/routes/school/students';
import { create } from '@/routes/school/students/transfers';

type PageProps = {
    students: Student[];
    filter: {
        name?: string;
        passport_number?: string;
        national_id?: string;
        family_registration_number?: string;
    };
};

const pageTitle = 'إضافة طالب مُنتقل';

export default function Create({ students, filter }: PageProps) {
    return (
        <>
            <Head title={pageTitle} />

            <AddTransferredStudent
                students={students}
                filter={filter}
                context="school"
            />
        </>
    );
}

Create.layout = () => ({
    breadcrumbs: [
        {
            title: 'الطلاب',
            href: studentsIndex.url(),
        },
        {
            title: pageTitle,
            href: create.url(),
        },
    ],
});
