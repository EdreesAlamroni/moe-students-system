import React from "react";

import type { EducationMonitorDashboardSummary } from "@/types";

import { Card, CardHeader } from "@/components/ui/structure/card";
import { Skeleton } from "@/components/ui/structure/skeleton";

import { StatCardsSection } from "@/components/ui/display/stat-card";

import {
    BuildingIcon,
    FlagIcon,
    GraduationCapIcon,
    MarsIcon,
    PresentationIcon,
    SchoolIcon,
    UsersIcon,
    VenusIcon,
} from "lucide-react";

type SummaryStatsProps = {
    summary?: EducationMonitorDashboardSummary;
};

export default function SummaryStats({ summary }: SummaryStatsProps) {
    if (!summary) {
        return (
            <section
                className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4"
                role="status"
                aria-label="جارٍ تحميل الإحصائيات"
            >
                {Array.from({ length: 8 }, (_, index) => (
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
            aria-label="إحصائيات المُراقبة التعليمية"
            columns={4}
            items={[
                { label: "إجمالي الطلاب", value: summary.students, icon: UsersIcon },
                { label: "الطلاب الذكور", value: summary.males, icon: MarsIcon },
                { label: "الطالبات الإناث", value: summary.females, icon: VenusIcon },
                { label: "الجنسيات", value: summary.nationalities, icon: FlagIcon },
                { label: "مكاتب الخدمات التعليمية", value: summary.education_services_offices, icon: BuildingIcon },
                { label: "المدارس", value: summary.schools, icon: SchoolIcon },
                { label: "الصفوف الدراسية", value: summary.grade_levels, icon: GraduationCapIcon },
                { label: "الفصول الدراسية", value: summary.classrooms, icon: PresentationIcon },
            ]}
        />
    );
}
