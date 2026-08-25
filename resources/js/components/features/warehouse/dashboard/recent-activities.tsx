import React from "react";

import { cn } from "@/lib/utils";

import type { WarehouseRecentActivityItem } from "@/types";

import { Skeleton } from "@/components/ui/structure/skeleton";
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/components/ui/display/table";

import DashboardSectionCard from "@/components/shared/dashboard/dashboard-section-card";

import { HistoryIcon } from "lucide-react";

type RecentActivitiesProps = {
    items?: WarehouseRecentActivityItem[];
    className?: string;
};

export default function RecentActivities({ items, className }: RecentActivitiesProps) {
    return (
        <DashboardSectionCard
            title="أحدث نشاطات توزيع الكُتب"
            description="آخر عمليات تأكيد توزيع الكُتب على الصفوف الدراسية"
            icon={HistoryIcon}
            reloadProps={["recentActivities"]}
            isLoading={!items}
            isEmpty={items?.length === 0}
            emptyText="لا توجد نشاطات توزيع كُتب مسجّلة حتى الآن."
            skeleton={<RecentActivitiesSkeleton />}
            className={cn(
                "gap-0 [&_[data-slot='card-content']]:px-0 [&]:not-has-[[data-slot=pagination-content]]:pb-0",
                className,
            )}
        >
            <div className="overflow-x-auto">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead className="w-24 font-mono">#</TableHead>
                            <TableHead>المدرسة</TableHead>
                            <TableHead>الصف الدراسي</TableHead>
                            <TableHead>المُراقبة</TableHead>
                            <TableHead>تاريخ التوزيع</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {items?.map((item, index) => (
                            <TableRow key={item.id}>
                                <TableCell className="font-mono tabular-nums">{index + 1}</TableCell>
                                <TableCell>{item.school}</TableCell>
                                <TableCell>{item.grade_level}</TableCell>
                                <TableCell>{item.monitor}</TableCell>
                                <TableCell className="font-mono tabular-nums">
                                    {item.distributed_at ?? "—"}
                                </TableCell>
                            </TableRow>
                        ))}
                    </TableBody>
                </Table>
            </div>
        </DashboardSectionCard>
    );
}

function RecentActivitiesSkeleton() {
    return (
        <div className="flex flex-col gap-3 py-2" role="status" aria-label="جارٍ تحميل النشاطات">
            {Array.from({ length: 5 }, (_, index) => (
                <div key={index} className="grid grid-cols-4 gap-3">
                    <Skeleton className="h-4 w-full" />
                    <Skeleton className="h-4 w-full" />
                    <Skeleton className="h-4 w-full" />
                    <Skeleton className="h-4 w-20" />
                </div>
            ))}
        </div>
    );
}
