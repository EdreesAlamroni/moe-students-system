import type { SchoolPeriod } from '@/types';

type SchoolPeriodsSummaryProps = {
    periods: SchoolPeriod[];
    showStudentCounts?: boolean;
};

export function SchoolPeriodsSummary({
    periods,
    showStudentCounts = false,
}: SchoolPeriodsSummaryProps) {
    if (periods.length === 0) {
        return <span className="text-muted-foreground">—</span>;
    }

    return (
        <div className="flex min-w-52 flex-col gap-2">
            {periods.map((period) => (
                <div
                    key={period.uuid}
                    className="flex items-start justify-between gap-4 rounded-md border bg-muted/30 px-3 py-2"
                >
                    <div className="min-w-0">
                        <div className="text-xs font-medium text-muted-foreground">
                            {period.academic_period.name}
                        </div>
                        <div className="mt-0.5 text-sm">{period.name}</div>
                    </div>

                    {showStudentCounts && (
                        <div className="shrink-0 text-left">
                            <div className="font-mono text-sm tabular-nums">
                                {period.students_count ?? 0}
                            </div>
                            <div className="text-[0.6875rem] text-muted-foreground">
                                طالب
                            </div>
                        </div>
                    )}
                </div>
            ))}
        </div>
    );
}
