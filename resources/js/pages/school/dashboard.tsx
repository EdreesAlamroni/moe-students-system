import { Head } from '@inertiajs/react';

import type {
    ClassroomOccupancyItem,
    DashboardSummary,
    GradeLevelDistributionItem,
    NationalityDistributionItem,
} from '@/types';

import MainContainer from '@/components/ui/structure/main-container';

import SummaryStats from '@/components/features/school/dashboard/summary-stats';
import QuickInsights from '@/components/features/school/dashboard/quick-insights';
import GenderDistributionChart from '@/components/features/school/dashboard/gender-distribution-chart';
import GradeLevelDistributionChart from '@/components/features/school/dashboard/grade-level-distribution-chart';
import NationalityDistributionChart from '@/components/features/school/dashboard/nationality-distribution-chart';
import ClassroomOccupancyChart from '@/components/features/school/dashboard/classroom-occupancy-chart';

import { dashboard } from '@/routes/school';

type PageProps = {
    summary?: DashboardSummary;
    gradeLevelDistribution?: GradeLevelDistributionItem[];
    classroomOccupancy?: ClassroomOccupancyItem[];
    nationalityDistribution?: NationalityDistributionItem[];
};

export default function Dashboard({
    summary,
    gradeLevelDistribution,
    classroomOccupancy,
    nationalityDistribution,
}: PageProps) {
    return (
        <>
            <Head title="الرئيسية" />

            <MainContainer showAcademicYearNotice>
                <SummaryStats
                    summary={summary}
                />

                <QuickInsights
                    summary={summary}
                    gradeLevels={gradeLevelDistribution}
                    classrooms={classroomOccupancy}
                />

                <section
                    aria-label="الرسوم البيانية"
                    className="grid grid-cols-1 gap-6 xl:grid-cols-5"
                >
                    <GenderDistributionChart
                        summary={summary}
                        className="xl:col-span-2"
                    />

                    <GradeLevelDistributionChart
                        items={gradeLevelDistribution}
                        className="xl:col-span-3"
                    />

                    <NationalityDistributionChart
                        items={nationalityDistribution}
                        className="xl:col-span-2"
                    />

                    <ClassroomOccupancyChart
                        items={classroomOccupancy}
                        className="xl:col-span-3"
                    />
                </section>
            </MainContainer>
        </>
    );
}

Dashboard.layout = {
    breadcrumbs: [
        {
            title: 'الرئيسية',
            href: dashboard.url(),
        },
    ],
};
