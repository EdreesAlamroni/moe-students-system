import { Head } from '@inertiajs/react';

import type {
    WarehouseAcademicYearTrendItem,
    WarehouseDashboardSummary,
    WarehouseEducationMonitorDistributionItem,
    WarehouseRecentActivityItem,
    WarehouseSchoolDistributionItem,
} from '@/types';

import MainContainer from '@/components/ui/structure/main-container';

import SummaryStats from '@/components/features/warehouse/dashboard/summary-stats';
import QuickInsights from '@/components/features/warehouse/dashboard/quick-insights';
import DistributionStatusChart from '@/components/features/warehouse/dashboard/distribution-status-chart';
import EducationMonitorProgressChart from '@/components/features/warehouse/dashboard/education-monitor-progress-chart';
import EducationMonitorStudentsChart from '@/components/features/warehouse/dashboard/education-monitor-students-chart';
import SchoolStudentsChart from '@/components/features/warehouse/dashboard/school-students-chart';
import SchoolProgressChart from '@/components/features/warehouse/dashboard/school-progress-chart';
// import AcademicYearTrendsChart from '@/components/features/warehouse/dashboard/academic-year-trends-chart';
import RecentActivities from '@/components/features/warehouse/dashboard/recent-activities';

import { dashboard } from '@/routes/warehouse';

type PageProps = {
    summary?: WarehouseDashboardSummary;
    educationMonitorDistribution?: WarehouseEducationMonitorDistributionItem[];
    schoolDistribution?: WarehouseSchoolDistributionItem[];
    academicYearTrends?: WarehouseAcademicYearTrendItem[];
    recentActivities?: WarehouseRecentActivityItem[];
};

export default function Dashboard({
    summary,
    educationMonitorDistribution,
    schoolDistribution,
    // academicYearTrends,
    recentActivities,
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
                />

                <section
                    aria-label="الرسوم البيانية"
                    className="grid grid-cols-1 gap-6 xl:grid-cols-5"
                >
                    <DistributionStatusChart
                        summary={summary}
                        className="xl:col-span-2"
                    />

                    <EducationMonitorStudentsChart
                        items={educationMonitorDistribution}
                        className="xl:col-span-3"
                    />

                    <EducationMonitorProgressChart
                        items={educationMonitorDistribution}
                        className="xl:col-span-5"
                    />

                    <SchoolStudentsChart
                        items={schoolDistribution}
                        className="xl:col-span-2"
                    />

                    <SchoolProgressChart
                        items={schoolDistribution}
                        className="xl:col-span-3"
                    />

                    {/* <AcademicYearTrendsChart
                        items={academicYearTrends}
                        className="xl:col-span-5"
                    /> */}

                    <RecentActivities
                        items={recentActivities}
                        className="xl:col-span-5"
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
