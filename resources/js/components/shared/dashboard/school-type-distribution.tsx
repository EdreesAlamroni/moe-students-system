import React from "react";

import {
    emptyStates,
    schoolTypeComparison,
    schoolTypeStudentComparison,
    studentsDistributedAcross,
    studentsInclusive,
} from "@/lib/arabic-labels";

import type { LargestSchoolOfType, SchoolTypeDistribution } from "@/types";

import { Card, CardHeader } from "@/components/ui/structure/card";
import { Skeleton } from "@/components/ui/structure/skeleton";

import { StatCardsSection } from "@/components/ui/display/stat-card";

import formatNumber from "@/components/shared/dashboard/format-number";
import InsightCard from "@/components/shared/dashboard/insight-card";

import { CrownIcon, ScaleIcon, SchoolIcon, UsersIcon } from "lucide-react";

type SchoolTypeDistributionSectionProps = {
    data?: SchoolTypeDistribution;
};

export default function SchoolTypeDistributionSection({ data }: SchoolTypeDistributionSectionProps) {
    return (
        <section aria-label="أنواع المدارس" className="flex flex-col gap-6">
            <SchoolTypeStats data={data} />
            <SchoolTypeInsights data={data} />
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
                { label: "طلبة المدارس العامة", value: data.public_students, icon: UsersIcon },
                { label: "طلبة المدارس الخاصة", value: data.private_students, icon: UsersIcon },
            ]}
        />
    );
}

function SchoolTypeInsights({ data }: { data?: SchoolTypeDistribution }) {
    return (
        <div className="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
            <RatioInsight
                label="نسبة المدارس العامة إلى الخاصة"
                icon={ScaleIcon}
                data={data}
                publicCount={data?.public_schools ?? 0}
                privateCount={data?.private_schools ?? 0}
                detail={(publicCount, privateCount) =>
                    schoolTypeComparison(publicCount, privateCount)
                }
                emptyText="لا توجد مدارس مسجلة حالياً"
            />

            <RatioInsight
                label="نسبة الطلبة في المدارس العامة إلى الخاصة"
                icon={UsersIcon}
                data={data}
                publicCount={data?.public_students ?? 0}
                privateCount={data?.private_students ?? 0}
                detail={(publicCount, privateCount) =>
                    schoolTypeStudentComparison(publicCount, privateCount)
                }
                emptyText={emptyStates.noRegisteredStudentsInSchoolType()}
            />

            <AverageStudentsInsight
                label="متوسط عدد الطلبة لكل مدرسة عامة"
                data={data}
                students={data?.public_students ?? 0}
                schools={data?.public_schools ?? 0}
                emptyText="لا توجد مدارس عامة مسجلة حالياً"
            />

            <AverageStudentsInsight
                label="متوسط عدد الطلبة لكل مدرسة خاصة"
                data={data}
                students={data?.private_students ?? 0}
                schools={data?.private_schools ?? 0}
                emptyText="لا توجد مدارس خاصة مسجلة حالياً"
            />

            <LargestSchoolInsight
                label="أعلى المدرسة عامة كثافة"
                data={data}
                school={data?.largest_public_school ?? null}
                emptyText={emptyStates.noRegisteredStudentsInPublicSchools()}
            />

            <LargestSchoolInsight
                label="أعلى المدرسة خاصة كثافة"
                data={data}
                school={data?.largest_private_school ?? null}
                emptyText={emptyStates.noRegisteredStudentsInPrivateSchools()}
            />
        </div>
    );
}

type RatioInsightProps = {
    label: string;
    icon: typeof ScaleIcon;
    data?: SchoolTypeDistribution;
    publicCount: number;
    privateCount: number;
    detail: (publicCount: number, privateCount: number) => string;
    emptyText: string;
};

function RatioInsight({ label, icon, data, publicCount, privateCount, detail, emptyText }: RatioInsightProps) {
    const total = publicCount + privateCount;
    const publicPercentage = total > 0 ? Math.round((publicCount / total) * 100) : 0;

    return (
        <InsightCard
            label={label}
            icon={icon}
            isLoading={!data}
            value={
                data && total > 0 ? (
                    <span className="font-mono tabular-nums">
                        ٪{publicPercentage} / ٪{100 - publicPercentage}
                    </span>
                ) : (
                    <span className="font-mono">-</span>
                )
            }
            detail={data && total > 0 ? detail(publicCount, privateCount) : emptyText}
            extra={
                data && total > 0 ? (
                    <div
                        className="flex h-1.5 w-full overflow-hidden bg-muted"
                        role="img"
                        aria-label={`العامة ٪${publicPercentage} والخاصة ٪${100 - publicPercentage}`}
                    >
                        <span className="bg-chart-2" style={{ width: `${publicPercentage}%` }} />
                        <span className="bg-chart-1" style={{ width: `${100 - publicPercentage}%` }} />
                    </div>
                ) : undefined
            }
        />
    );
}

type AverageStudentsInsightProps = {
    label: string;
    data?: SchoolTypeDistribution;
    students: number;
    schools: number;
    emptyText: string;
};

function AverageStudentsInsight({ label, data, students, schools, emptyText }: AverageStudentsInsightProps) {
    const average = schools > 0 ? Math.round(students / schools) : 0;

    return (
        <InsightCard
            label={label}
            icon={UsersIcon}
            isLoading={!data}
            value={
                data && schools > 0 ? (
                    <span className="font-mono tabular-nums">{formatNumber(average)}</span>
                ) : (
                    <span className="font-mono">-</span>
                )
            }
            detail={
                data && schools > 0
                    ? studentsDistributedAcross(students, schools, "schools")
                    : emptyText
            }
        />
    );
}

type LargestSchoolInsightProps = {
    label: string;
    data?: SchoolTypeDistribution;
    school: LargestSchoolOfType | null;
    emptyText: string;
};

function LargestSchoolInsight({ label, data, school, emptyText }: LargestSchoolInsightProps) {
    return (
        <InsightCard
            label={label}
            icon={CrownIcon}
            isLoading={!data}
            value={school?.name ?? "—"}
            detail={
                school
                    ? `يدرس بها ${studentsInclusive(school.students)}`
                    : emptyText
            }
        />
    );
}
