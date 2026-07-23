import { Head, Link, usePage } from '@inertiajs/react';

import { cn } from '@/lib/utils';

import type {
    ClassroomDistributionMethod,
    IndexPageProps as PageProps,
} from '@/types/classroom-distribution';

import MainContainer from '@/components/ui/structure/main-container';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/structure/card';
import { FormLayout } from '@/components/ui/structure/form-layout';

import { Icon } from '@/components/ui/display/icon';

import StatusAlerts from '@/components/features/school/classroom-distribution/status-alerts';
import FinalizeSection from '@/components/features/school/classroom-distribution/finalize-section';

import { ArrowLeftIcon, LayoutGridIcon } from 'lucide-react';

import { index } from '@/routes/school/classroom-distribution';

export default function Index({
    methods,
    isDistributionCompleted,
    enrollmentSummary,
    schoolWideUnassignedCount,
    can,
}: PageProps) {
    const { currentAcademicYear } = usePage().props;

    const isAcademicYearActive = !!currentAcademicYear?.is_active;
    const hasEligibleEnrollments = enrollmentSummary.eligibleCount > 0;

    const canSelectMethod =
        isAcademicYearActive &&
        hasEligibleEnrollments &&
        !isDistributionCompleted &&
        can.distribute;

    const showMethods =
        canSelectMethod ||
        !isAcademicYearActive ||
        isDistributionCompleted ||
        (hasEligibleEnrollments && !can.distribute);

    const showFinalize = isAcademicYearActive && can.finalize && !isDistributionCompleted;

    return (
        <>
            <Head title="توزيع الطلاب على الفصول الدراسية" />

            <MainContainer>
                <section>
                    <header className="flex items-center gap-3 border-b pb-4">
                        <LayoutGridIcon className="h-4 w-4 shrink-0" />
                        <div className="flex flex-col gap-1">
                            <h1 className="text-sm font-medium text-foreground">
                                توزيع الطلاب على الفصول الدراسية
                            </h1>
                            <p className="text-xs text-muted-foreground">
                                اختر طريقة التوزيع المناسبة لتعيين الطلاب على
                                الفصول الدراسية، ثم أتمم العملية للسنة الدراسية
                                الحالية.
                            </p>
                        </div>
                    </header>
                </section>

                <StatusAlerts
                    isDistributionCompleted={isDistributionCompleted}
                    canDistribute={can.distribute}
                    isAcademicYearActive={isAcademicYearActive}
                    enrollmentSummary={enrollmentSummary}
                />

                <FormLayout>
                    {showMethods && (
                        <div className="grid gap-4 sm:grid-cols-2">
                            {methods.map((method) => (
                                <MethodCard
                                    key={method.value}
                                    method={method}
                                    interactive={canSelectMethod}
                                />
                            ))}
                        </div>
                    )}

                    {showFinalize && (
                        <FinalizeSection
                            schoolWideUnassignedCount={
                                schoolWideUnassignedCount
                            }
                            enrollmentsWithoutGradeLevelCount={
                                enrollmentSummary.withoutGradeLevelCount
                            }
                            canFinalize={can.finalize}
                            isDistributionCompleted={isDistributionCompleted}
                            academicYearName={currentAcademicYear?.name}
                            isAcademicYearActive={isAcademicYearActive}
                        />
                    )}
                </FormLayout>
            </MainContainer>
        </>
    );
}

type MethodCardProps = {
    method: ClassroomDistributionMethod;
    interactive: boolean;
};

function MethodCard({ method, interactive }: MethodCardProps) {
    const card = (
        <Card
            className={cn(
                'h-full justify-between',
                interactive
                    ? 'transition-all group-hover:ring-primary/40 group-hover:shadow-md'
                    : 'opacity-60',
            )}
        >
            <CardHeader>
                <div className="flex items-start gap-4">
                    <div
                        className={cn(
                            'flex size-11 shrink-0 items-center justify-center transition-colors',
                            interactive
                                ? 'bg-primary/10 text-primary group-hover:bg-primary group-hover:text-primary-foreground'
                                : 'bg-muted text-muted-foreground',
                        )}
                    >
                        <Icon iconNode={method.icon} className="size-5" />
                    </div>
                    <div className="min-w-0 flex-1">
                        <CardTitle>{method.name}</CardTitle>
                        <CardDescription className="mt-1.5">
                            {method.description}
                        </CardDescription>
                    </div>
                </div>
            </CardHeader>

            {interactive && (
                <CardContent>
                    <span className="inline-flex items-center gap-1.5 text-xs font-medium text-primary">
                        <span>الانتقال للصفحة</span>
                        <ArrowLeftIcon className="size-3.5 transition-transform group-hover:-translate-x-1" />
                    </span>
                </CardContent>
            )}
        </Card>
    );

    if (!interactive) {
        return card;
    }

    return (
        <Link href={method.route} className="group block h-full">
            {card}
        </Link>
    );
}

Index.layout = () => ({
    breadcrumbs: [
        {
            title: 'توزيع الطلاب على الفصول الدراسية',
            href: index.url(),
        },
    ],
});
