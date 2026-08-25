import React from "react";

import type { WarehouseDashboardSummary } from "@/types";

import { Card, CardHeader } from "@/components/ui/structure/card";
import { Skeleton } from "@/components/ui/structure/skeleton";

import { StatCardsSection } from "@/components/ui/display/stat-card";

import {
    BookOpenCheckIcon,
    ClockIcon,
    PercentIcon,
    SchoolIcon,
    TruckIcon,
    UsersIcon,
} from "lucide-react";

type SummaryStatsProps = {
    summary?: WarehouseDashboardSummary;
};

export default function SummaryStats({ summary }: SummaryStatsProps) {
    if (!summary) {
        return (
            <section
                className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3"
                role="status"
                aria-label="جارٍ تحميل الإحصائيات"
            >
                {Array.from({ length: 6 }, (_, index) => (
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
            aria-label="إحصائيات المخزن"
            columns={3}
            items={[
                { label: "عدد المدارس", value: summary.schools, icon: SchoolIcon },
                { label: "إجمالي الطلبة", value: summary.students, icon: UsersIcon },
                { label: "عدد توزيعات الكُتب المدرسية", value: summary.book_distributions, icon: TruckIcon },
                { label: "عدد الطلبة الذين استلموا الكُتب المدرسية", value: summary.students_received, icon: BookOpenCheckIcon },
                { label: "عدد التسليمات المعلّقة", value: summary.students_pending, icon: ClockIcon },
                { label: "نسبة الإنجاز", value: `%${summary.completion_rate}`, icon: PercentIcon },
            ]}
        />
    );
}
