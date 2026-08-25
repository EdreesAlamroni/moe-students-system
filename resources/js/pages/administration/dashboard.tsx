import { Head } from '@inertiajs/react';

import type {
    AdministrationDashboardSummary,
    EducationMonitorDistributionItem,
    GradeLevelDistributionItem,
    NationalityDistributionItem,
    SchoolDistributionItem,
} from '@/types';

import MainContainer from '@/components/ui/structure/main-container';

import SummaryStats from '@/components/features/administration/dashboard/summary-stats';
import QuickInsights from '@/components/features/administration/dashboard/quick-insights';
import GenderDistributionChart from '@/components/features/administration/dashboard/gender-distribution-chart';
import GradeLevelDistributionChart from '@/components/features/administration/dashboard/grade-level-distribution-chart';
import EducationMonitorSchoolsChart from '@/components/features/administration/dashboard/education-monitor-schools-chart';
import EducationMonitorStudentsChart from '@/components/features/administration/dashboard/education-monitor-students-chart';
import NationalityDistributionChart from '@/components/features/administration/dashboard/nationality-distribution-chart';
import SchoolStudentsChart from '@/components/features/administration/dashboard/school-students-chart';
import SchoolClassroomsChart from '@/components/features/administration/dashboard/school-classrooms-chart';

import { dashboard } from '@/routes/administration';

type PageProps = {
    summary?: AdministrationDashboardSummary;
    educationMonitorDistribution?: EducationMonitorDistributionItem[];
    schoolDistribution?: SchoolDistributionItem[];
    gradeLevelDistribution?: GradeLevelDistributionItem[];
    nationalityDistribution?: NationalityDistributionItem[];
};

export default function Dashboard({
    summary,
    educationMonitorDistribution,
    schoolDistribution,
    gradeLevelDistribution,
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
                    monitors={educationMonitorDistribution}
                    schools={schoolDistribution}
                    gradeLevels={gradeLevelDistribution}
                />

                <section
                    aria-label="الرسوم البيانية"
                    className="grid grid-cols-1 gap-6 xl:grid-cols-4"
                >
                    <GenderDistributionChart
                        summary={summary}
                        className="xl:col-span-2"
                    />

                    <NationalityDistributionChart
                        items={nationalityDistribution}
                        className="xl:col-span-2"
                    />

                    <EducationMonitorSchoolsChart
                        items={educationMonitorDistribution?.slice(0, 5)}
                        className="xl:col-span-2"
                    />

                    <EducationMonitorStudentsChart
                        items={educationMonitorDistribution?.slice(0, 5)}
                        className="xl:col-span-2"
                    />

                    <GradeLevelDistributionChart
                        items={gradeLevelDistribution}
                        className="xl:col-span-full"
                    />

                    <SchoolStudentsChart
                        items={schoolDistribution}
                        className="xl:col-span-full"
                    />

                    <SchoolClassroomsChart
                        items={schoolDistribution}
                        className="xl:col-span-full"
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
