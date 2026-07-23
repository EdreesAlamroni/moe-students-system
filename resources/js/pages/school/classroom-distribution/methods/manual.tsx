import React from 'react';

import { Head, router, usePage } from '@inertiajs/react';

import type { ManualPageProps as PageProps } from '@/types/classroom-distribution';

import MainContainer from '@/components/ui/structure/main-container';
import { FormLayout } from '@/components/ui/structure/form-layout';

import { UserPlusIcon } from 'lucide-react';

import GradeLevelSelector from '@/components/features/school/classroom-distribution/grade-level-selector';
import ManualDistributionSection from '@/components/features/school/classroom-distribution/manual-distribution-section';
import StatusAlerts from '@/components/features/school/classroom-distribution/status-alerts';

import { index } from '@/routes/school/classroom-distribution';

export default function Manual({
    gradeLevels,
    selectedGradeLevelId,
    gradeLevel,
    classrooms,
    unassignedStudents,
    isDistributionCompleted,
    method,
    can,
}: PageProps) {
    const { currentAcademicYear } = usePage().props;

    const [loadingStudents, setLoadingStudents] = React.useState(false);

    const isAcademicYearActive = !!currentAcademicYear?.is_active;
    const formsDisabled =
        !isAcademicYearActive ||
        isDistributionCompleted ||
        !can.distribute ||
        selectedGradeLevelId === null;

    const handleGradeChange = (value: string): void => {
        router.get(
            method.route,
            { grade_level_id: value },
            {
                preserveScroll: true,
                onStart: () => setLoadingStudents(true),
                onFinish: () => setLoadingStudents(false),
            },
        );
    };

    return (
        <>
            <Head title={method.name} />

            <MainContainer>
                <section>
                    <header className="flex items-center gap-3 border-b pb-4">
                        <UserPlusIcon className="h-4 w-4 shrink-0" />
                        <div className="flex flex-col gap-1">
                            <h1 className="text-sm font-medium text-foreground">
                                التوزيع اليدوي للطلاب
                            </h1>
                            <p className="text-xs text-muted-foreground">
                                عيّن الطلاب غير الموزّعين في الصف الدراسي المحدد
                                على الفصول الدراسية يدوياً حسب اختيارك.
                            </p>
                        </div>
                    </header>
                </section>

                <StatusAlerts
                    isDistributionCompleted={isDistributionCompleted}
                    canDistribute={can.distribute}
                    isAcademicYearActive={isAcademicYearActive}
                />

                <FormLayout>
                    <GradeLevelSelector
                        gradeLevels={gradeLevels}
                        selectedGradeLevelId={selectedGradeLevelId}
                        onGradeChange={handleGradeChange}
                    />

                    {selectedGradeLevelId !== null && gradeLevel !== null && (
                        <ManualDistributionSection
                            key={selectedGradeLevelId}
                            method={method}
                            classrooms={classrooms}
                            unassignedStudents={unassignedStudents}
                            selectedGradeLevelId={selectedGradeLevelId}
                            formsDisabled={formsDisabled}
                            loadingStudents={loadingStudents}
                        />
                    )}
                </FormLayout>
            </MainContainer>
        </>
    );
}

Manual.layout = (props: PageProps) => ({
    breadcrumbs: [
        {
            title: 'توزيع الطلاب على الفصول الدراسية',
            href: index.url(),
        },
        {
            title: props.method.name,
            href: props.method.route,
        },
    ],
});
