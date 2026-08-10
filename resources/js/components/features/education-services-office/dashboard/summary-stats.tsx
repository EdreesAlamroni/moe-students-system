import React from "react";

import type { EducationServicesOfficeDashboardSummary } from "@/types";

import { Card, CardHeader } from "@/components/ui/structure/card";
import { Skeleton } from "@/components/ui/structure/skeleton";

import { StatCardsSection } from "@/components/ui/display/stat-card";

import {
    FlagIcon,
    GraduationCapIcon,
    MarsIcon,
    PresentationIcon,
    SchoolIcon,
    UserRoundCheckIcon,
    UsersIcon,
    VenusIcon,
} from "lucide-react";

type SummaryStatsProps = {
    summary?: EducationServicesOfficeDashboardSummary;
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
            aria-label="إحصائيات مكتب الخدمات التعليمية"
            columns={4}
            items={[
                { label: "إجمالي الطلبة", value: summary.students, icon: UsersIcon },
                { label: "عدد الذكور", value: summary.males, icon: MarsIcon },
                { label: "عدد الإناث", value: summary.females, icon: VenusIcon },
                { label: "عدد الجنسيات", value: summary.nationalities, icon: FlagIcon },
                { label: "عدد المدارس", value: summary.schools, icon: SchoolIcon },
                { label: "عدد الصفوف الدراسية", value: summary.grade_levels, icon: GraduationCapIcon },
                { label: "عدد الفصول الدراسية", value: summary.classrooms, icon: PresentationIcon },
                {
                    label: "عدد الطلبة المقيدين بالصفوف",
                    value: summary.students - summary.students_unenrolled_in_grade_level,
                    icon: UserRoundCheckIcon,
                },
            ]}
        />
    );
}
