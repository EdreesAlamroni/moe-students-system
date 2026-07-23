import { Head, router, usePage } from '@inertiajs/react';

import type { RandomPageProps as PageProps } from '@/types/classroom-distribution';

import MainContainer from '@/components/ui/structure/main-container';
import { FormLayout } from '@/components/ui/structure/form-layout';

import { ShuffleIcon } from 'lucide-react';

import GradeLevelSelector from '@/components/features/school/classroom-distribution/grade-level-selector';
import RandomDistributionSection from '@/components/features/school/classroom-distribution/random-distribution-section';
import StatusAlerts from '@/components/features/school/classroom-distribution/status-alerts';

import { index } from '@/routes/school/classroom-distribution';

export default function Random({
    gradeLevels,
    selectedGradeLevelId,
    gradeLevel,
    classrooms,
    pendingStudentCount,
    isDistributionCompleted,
    method,
    can,
}: PageProps) {
    const { currentAcademicYear } = usePage().props;

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
            { preserveScroll: true },
        );
    };

    return (
        <>
            <Head title={method.name} />

            <MainContainer>
                <section>
                    <header className="flex items-center gap-3 border-b pb-4">
                        <ShuffleIcon className="h-4 w-4 shrink-0" />
                        <div className="flex flex-col gap-1">
                            <h1 className="text-sm font-medium text-foreground">
                                التوزيع العشوائي للطلاب
                            </h1>
                            <p className="text-xs text-muted-foreground">
                                وزّع الطلاب غير الموزّعين في الصف الدراسي المحدد
                                عشوائياً على الفصول الدراسية المختارة.
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
                        <RandomDistributionSection
                            key={`${selectedGradeLevelId}-${pendingStudentCount}`}
                            method={method}
                            classrooms={classrooms}
                            gradeLevel={gradeLevel}
                            selectedGradeLevelId={selectedGradeLevelId}
                            pendingInGradeCount={pendingStudentCount}
                            formsDisabled={formsDisabled}
                        />
                    )}
                </FormLayout>
            </MainContainer>
        </>
    );
}

Random.layout = (props: PageProps) => ({
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
