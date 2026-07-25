import React from "react";

import type { SchoolTypeDistribution } from "@/types";

import { Card, CardHeader } from "@/components/ui/structure/card";
import { Skeleton } from "@/components/ui/structure/skeleton";

import { StatCardsSection } from "@/components/ui/display/stat-card";

import formatNumber from "@/components/shared/dashboard/format-number";
import InsightCard from "@/components/shared/dashboard/insight-card";

import { ScaleIcon, SchoolIcon, UsersIcon } from "lucide-react";

type SchoolTypeDistributionSectionProps = {
    data?: SchoolTypeDistribution;
};

export default function SchoolTypeDistributionSection({ data }: SchoolTypeDistributionSectionProps) {
    return (
        <section aria-label="أنواع المدارس" className="flex flex-col gap-6">
            <SchoolTypeStats data={data} />
            <SchoolTypeRatios data={data} />
        </section>
    );
}

function SchoolTypeStats({ data }: { data?: SchoolTypeDistribution }) {
    if (!data) {
        return (
            <section
                className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4"
                role="status"
                aria-label="جارٍ تحميل إحصائيات أنواع المدارس"
            >
                {Array.from({ length: 4 }, (_, index) => (
                    <Card key={index} size="sm">
                        <CardHeader className="gap-3">
                            <Skeleton className="h-4 w-24" />
                            <Skeleton className="h-6 w-16" />
                        </CardHeader>
                    </Card>
                ))}
            </section>
        );
    }

    return (
        <StatCardsSection
            aria-label="إحصائيات أنواع المدارس"
            columns={4}
            items={[
                { label: "المدارس العامة", value: data.public_schools, icon: SchoolIcon },
                { label: "المدارس الخاصة", value: data.private_schools, icon: SchoolIcon },
                { label: "طلاب المدارس العامة", value: data.public_students, icon: UsersIcon },
                { label: "طلاب المدارس الخاصة", value: data.private_students, icon: UsersIcon },
            ]}
        />
    );
}

function SchoolTypeRatios({ data }: { data?: SchoolTypeDistribution }) {
    const totalSchools = data ? data.public_schools + data.private_schools : 0;
    const totalStudents = data ? data.public_students + data.private_students : 0;

    const publicSchoolsPercentage = data && totalSchools > 0
        ? Math.round((data.public_schools / totalSchools) * 100)
        : 0;

    const publicStudentsPercentage = data && totalStudents > 0
        ? Math.round((data.public_students / totalStudents) * 100)
        : 0;

    return (
        <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
            <InsightCard
                label="نسبة المدارس العامة إلى الخاصة"
                icon={ScaleIcon}
                isLoading={!data}
                value={
                    data && totalSchools > 0 ? (
                        <span className="font-mono tabular-nums">
                            ٪{publicSchoolsPercentage} / ٪{100 - publicSchoolsPercentage}
                        </span>
                    ) : (
                        "—"
                    )
                }
                detail={
                    data && totalSchools > 0
                        ? `${formatNumber(data.public_schools)} عامة مقابل ${formatNumber(data.private_schools)} خاصة`
                        : "لا توجد مدارس مسجلة حالياً"
                }
                extra={
                    data && totalSchools > 0 ? (
                        <div
                            className="flex h-1.5 w-full overflow-hidden bg-muted"
                            role="img"
                            aria-label={`العامة ٪${publicSchoolsPercentage} والخاصة ٪${100 - publicSchoolsPercentage}`}
                        >
                            <span className="bg-chart-2" style={{ width: `${publicSchoolsPercentage}%` }} />
                            <span className="bg-chart-1" style={{ width: `${100 - publicSchoolsPercentage}%` }} />
                        </div>
                    ) : undefined
                }
            />

            <InsightCard
                label="نسبة الطلاب في المدارس العامة إلى الخاصة"
                icon={UsersIcon}
                isLoading={!data}
                value={
                    data && totalStudents > 0 ? (
                        <span className="font-mono tabular-nums">
                            ٪{publicStudentsPercentage} / ٪{100 - publicStudentsPercentage}
                        </span>
                    ) : (
                        "—"
                    )
                }
                detail={
                    data && totalStudents > 0
                        ? `${formatNumber(data.public_students)} في العامة مقابل ${formatNumber(data.private_students)} في الخاصة`
                        : "لا يوجد طلاب مسندون إلى المدارس"
                }
                extra={
                    data && totalStudents > 0 ? (
                        <div
                            className="flex h-1.5 w-full overflow-hidden bg-muted"
                            role="img"
                            aria-label={`طلاب العامة ٪${publicStudentsPercentage} وطلاب الخاصة ٪${100 - publicStudentsPercentage}`}
                        >
                            <span className="bg-chart-2" style={{ width: `${publicStudentsPercentage}%` }} />
                            <span className="bg-chart-1" style={{ width: `${100 - publicStudentsPercentage}%` }} />
                        </div>
                    ) : undefined
                }
            />
        </div>
    );
}
