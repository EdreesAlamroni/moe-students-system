import { Head } from '@inertiajs/react';

import type {
    EducationServicesOfficeDashboardSummary,
    EducationServicesOfficeSchoolDistributionItem,
    GradeLevelDistributionItem,
    NationalityDistributionItem,
    SchoolTypeDistribution,
} from '@/types';

import MainContainer from '@/components/ui/structure/main-container';

import SchoolTypeDistributionSection from '@/components/shared/dashboard/school-type-distribution';

import SummaryStats from '@/components/features/education-services-office/dashboard/summary-stats';
import QuickInsights from '@/components/features/education-services-office/dashboard/quick-insights';
import GenderDistributionChart from '@/components/features/education-services-office/dashboard/gender-distribution-chart';
import NationalityDistributionChart from '@/components/features/education-services-office/dashboard/nationality-distribution-chart';
import GradeLevelDistributionChart from '@/components/features/education-services-office/dashboard/grade-level-distribution-chart';
import SchoolStudentsChart from '@/components/features/education-services-office/dashboard/school-students-chart';
import SchoolClassroomsChart from '@/components/features/education-services-office/dashboard/school-classrooms-chart';

import { dashboard } from '@/routes/education-services-office';

type PageProps = {
    summary?: EducationServicesOfficeDashboardSummary;
    schoolDistribution?: EducationServicesOfficeSchoolDistributionItem[];
    gradeLevelDistribution?: GradeLevelDistributionItem[];
    nationalityDistribution?: NationalityDistributionItem[];
    schoolTypeDistribution?: SchoolTypeDistribution;
};

export default function Dashboard({
    summary,
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
