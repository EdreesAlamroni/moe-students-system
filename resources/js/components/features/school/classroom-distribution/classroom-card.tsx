import { cn } from '@/lib/utils';

import type { ClassroomRow } from '@/types/classroom-distribution';

import { Checkbox } from '@/components/ui/controls/checkbox';
import { Label } from '@/components/ui/controls/label';
import { Separator } from '@/components/ui/structure/separator';

interface ClassroomCardProps {
    classroom: ClassroomRow;
    selected: boolean;
    disabled: boolean;
    showShortageHint: boolean;
    onToggle: (id: number, checked: boolean) => void;
}

export default function ClassroomCard({
    classroom,
    selected,
    disabled,
    showShortageHint,
    onToggle,
}: ClassroomCardProps) {
    const isFull = classroom.remaining_capacity === 0;
    const isDisabled = disabled || isFull;

    const studentsCount = classroom.students_count ?? 0;
    const occupancyPercentage =
        classroom.capacity > 0
            ? Math.min(
                100,
                Math.round((studentsCount / classroom.capacity) * 100),
            )
            : 0;

    return (
        <div
            className={cn(
                'flex flex-col gap-4 rounded-none border p-4 text-sm transition-colors',
                isDisabled && 'pointer-events-none opacity-55',
                selected && 'border-primary/50 bg-primary/5',
                !selected && 'border-border bg-card',
                showShortageHint && 'border-amber-700/40 ring-1 ring-amber-700/25',
            )}
        >
            <div className="flex items-start gap-3">
                <Checkbox
                    id={`random-classroom-${classroom.id}`}
                    checked={selected}
                    onCheckedChange={(value) =>
                        onToggle(classroom.id, value === true)
                    }
                    disabled={isDisabled}
                    className="mt-0.5"
                />

                <div className="flex min-w-0 flex-1 items-start justify-between gap-2">
                    <Label
                        htmlFor={`random-classroom-${classroom.id}`}
                        className={cn(
                            'min-w-0',
                            !isDisabled && 'cursor-pointer',
                            isDisabled && 'text-muted-foreground',
                        )}
                    >
                        الفصل الدراسي:{' '}
                        <span className="font-mono">{classroom.name}</span>
                    </Label>

                    {isFull && (
                        <span className="shrink-0 text-[11px] font-medium text-destructive">
                            مكتمل
                        </span>
                    )}
                </div>
            </div>

            <div className="space-y-2">
                <div
                    className="h-1.5 w-full overflow-hidden rounded-none bg-muted"
                    aria-hidden
                >
                    <div
                        className={cn(
                            'h-full rounded-none transition-all',
                            isFull && 'bg-primary',
                        )}
                        style={{ width: `${occupancyPercentage}%` }}
                    />
                </div>

                <dl className="flex items-center justify-between gap-2 text-xs text-muted-foreground">
                    <div className="flex items-center gap-1">
                        <dt>السعة</dt>
                        <dd className="font-mono tabular-nums text-foreground">
                            {classroom.capacity}
                        </dd>
                    </div>
                    <div className="flex items-center gap-1">
                        <dt>المشغول</dt>
                        <dd className="font-mono tabular-nums text-foreground">
                            {studentsCount}
                        </dd>
                    </div>
                    <div className="flex items-center gap-1">
                        <dt>المتبقي</dt>
                        <dd
                            className={cn(
                                'font-mono tabular-nums',
                                isFull
                                    ? 'font-medium text-destructive'
                                    : 'text-foreground',
                            )}
                        >
                            {classroom.remaining_capacity}
                        </dd>
                    </div>
                </dl>
            </div>

            {showShortageHint && (
                <>
                    <Separator />
                    <p className="text-[11px] leading-relaxed text-amber-700">
                        قد يُعاد توزيع طلاب إضافيين على هذا الفصل عند تجاوز
                        السعة الإجمالية للفصول المحددة.
                    </p>
                </>
            )}
        </div>
    );
}
