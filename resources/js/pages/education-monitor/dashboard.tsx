import { Head } from '@inertiajs/react';

import type {
    EducationMonitorDashboardSummary,
    EducationMonitorSchoolDistributionItem,
    EducationServicesOfficeDistributionItem,
    GradeLevelDistributionItem,
    NationalityDistributionItem,
    SchoolTypeDistribution,
} from '@/types';

import MainContainer from '@/components/ui/structure/main-container';

import SummaryStats from '@/components/features/education-monitor/dashboard/summary-stats';
import QuickInsights from '@/components/features/education-monitor/dashboard/quick-insights';
import SchoolTypeDistributionSection from '@/components/features/education-monitor/dashboard/school-type-distribution';
import GenderDistributionChart from '@/components/features/education-monitor/dashboard/gender-distribution-chart';
import NationalityDistributionChart from '@/components/features/education-monitor/dashboard/nationality-distribution-chart';
import GradeLevelDistributionChart from '@/components/features/education-monitor/dashboard/grade-level-distribution-chart';
import SchoolStudentsChart from '@/components/features/education-monitor/dashboard/school-students-chart';
import SchoolClassroomsChart from '@/components/features/education-monitor/dashboard/school-classrooms-chart';

import { dashboard } from '@/routes/education-monitor';

type PageProps = {
    summary?: EducationMonitorDashboardSummary;
    officeDistribution?: EducationServicesOfficeDistributionItem[];
    schoolDistribution?: EducationMonitorSchoolDistributionItem[];
    gradeLevelDistribution?: GradeLevelDistributionItem[];
    nationalityDistribution?: NationalityDistributionItem[];
    schoolTypeDistribution?: SchoolTypeDistribution;
};

export default function Dashboard({
    summary,
    officeDistribution,
    schoolDistribution,
    gradeLevelDistribution,
    nationalityDistribution,
    schoolTypeDistribution,
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
                    offices={officeDistribution}
                    schools={schoolDistribution}
                    gradeLevels={gradeLevelDistribution}
                />

                <SchoolTypeDistributionSection
                    data={schoolTypeDistribution}
                />

                <section
                    aria-label="الرسوم البيانية"
                    className="grid grid-cols-1 gap-6 xl:grid-cols-5"
                >
                    <GenderDistributionChart
                        summary={summary}
                        className="xl:col-span-2"
                    />

                    <NationalityDistributionChart
                        items={nationalityDistribution}
                        className="xl:col-span-3"
                    />

                    <GradeLevelDistributionChart
                        items={gradeLevelDistribution}
                        className="xl:col-span-full"
                    />

                    <SchoolStudentsChart
                        items={schoolDistribution}
                        className="xl:col-span-3"
                    />

                    <SchoolClassroomsChart
                        items={schoolDistribution}
                        className="xl:col-span-2"
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
