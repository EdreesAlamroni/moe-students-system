import React from 'react';

import { Form } from '@inertiajs/react';

import { cn } from '@/lib/utils';

import type { School, GradeLevel, Enum } from '@/types';

import Field from '@/components/ui/controls/field';
import { Label } from '@/components/ui/controls/label';
import { MultiSelect } from '@/components/ui/controls/multi-select';
import InputError from '@/components/ui/controls/input-error';

import {
    Alert,
    AlertDescription,
    AlertTitle,
} from '@/components/ui/alerts/alert';
import ValidationErrors from '@/components/ui/alerts/validation-errors';
import EmptyState from '@/components/ui/display/empty-state';

import {
    Dialog,
    DialogBody,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogFormLayout,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/overlay/dialog';

import { Button } from '@/components/ui/actions/button';
import { ConfirmButton } from '@/components/ui/actions/submit-button';

import { AlertTriangleIcon, InfoIcon, RotateCcwIcon } from 'lucide-react';

import { reset } from '@/routes/education-monitor/schools/classroom-distribution';

type ResetScope = 'all' | 'selected';

type ResetScopeOption = Enum & { description: string };

type ClassroomDistributionResetProps = {
    has_distribution_data: boolean;
    eligible_grade_levels: GradeLevel[];
    scopes: ResetScopeOption[];
};

type ResetClassroomDistributionActionProps = {
    school: School;
    classroomDistributionReset: ClassroomDistributionResetProps;
    canReset: boolean;
};

export default function ResetClassroomDistributionAction({
    school,
    classroomDistributionReset,
    canReset,
}: ResetClassroomDistributionActionProps) {
    const [open, setOpen] = React.useState(false);
    const [scope, setScope] = React.useState<ResetScope>('all');
    const [selectedGradeLevelIds, setSelectedGradeLevelIds] = React.useState<
        string[]
    >([]);

    const { has_distribution_data, eligible_grade_levels, scopes } =
        classroomDistributionReset;

    if (!canReset) {
        return null;
    }

    const canSubmit =
        has_distribution_data &&
        (scope === 'all' || selectedGradeLevelIds.length > 0);

    function handleOpenChange(nextOpen: boolean): void {
        setOpen(nextOpen);

        if (!nextOpen) {
            setScope('all');
            setSelectedGradeLevelIds([]);
        }
    }

    function handleScopeSelect(scopeValue: ResetScope): void {
        setScope(scopeValue);

        if (scopeValue === 'all') {
            setSelectedGradeLevelIds([]);
        }
    }

    return (
        <>
            <Button
                type="button"
                variant="outline"
                disabled={!has_distribution_data}
                onClick={() => setOpen(true)}
            >
                <RotateCcwIcon />
                <span>إعادة تعيين توزيع الفصول الدراسية</span>
            </Button>

            <Dialog open={open} onOpenChange={handleOpenChange}>
                <DialogContent className="sm:max-w-lg">
                    {!has_distribution_data ? (
                        <EmptyResetDialogContent />
                    ) : (
                        <Form
                            {...reset.form({ school: school })}
                            disableWhileProcessing
                            options={{
                                preserveScroll: true,
                                preserveState: 'errors',
                            }}
                            onSuccess={() => handleOpenChange(false)}
                            onError={() => setOpen(true)}
                        >
                            {({ processing, errors }) => (
                                <ResetForm
                                    processing={processing}
                                    errors={errors}
                                    scope={scope}
                                    onScopeSelect={handleScopeSelect}
                                    selectedGradeLevelIds={selectedGradeLevelIds}
                                    onGradeLevelIdsChange={setSelectedGradeLevelIds}
                                    scopes={scopes}
                                    eligibleGradeLevels={eligible_grade_levels}
                                    canSubmit={canSubmit}
                                />
                            )}
                        </Form>
                    )}
                </DialogContent>
            </Dialog>
        </>
    );
}

function EmptyResetDialogContent() {
    return (
        <DialogFormLayout>
            <DialogHeader>
                <DialogTitle>إعادة تعيين توزيع الفصول الدراسية</DialogTitle>
                <DialogDescription>
                    لا يمكن تنفيذ إعادة التعيين حالياً لعدم توفر بيانات توزيع
                    قابلة للإعادة.
                </DialogDescription>
            </DialogHeader>

            <DialogBody>
                <EmptyState text="لا توجد توزيعات فصول دراسية متاحة لإعادة التعيين لهذه المدرسة في السنة الدراسية الحالية." />

                <Alert variant="info">
                    <InfoIcon />
                    <AlertTitle>متى يصبح الإجراء متاحاً؟</AlertTitle>
                    <AlertDescription>
                        تتطلب عملية إعادة التعيين وجود طلاب موزعين على فصول
                        دراسية أو إتمام عملية توزيع الفصول للسنة الدراسية
                        الحالية. يُرجى تنفيذ توزيع الفصول من لوحة تحكم المدرسة
                        أولًا.
                    </AlertDescription>
                </Alert>
            </DialogBody>

            <DialogFooter>
                <DialogClose asChild>
                    <Button type="button" variant="outline">
                        إغلاق
                    </Button>
                </DialogClose>
            </DialogFooter>
        </DialogFormLayout>
    );
}

interface ResetFormProps {
    processing: boolean;
    errors: Record<string, string>;
    scope: ResetScope;
    onScopeSelect: (scope: ResetScope) => void;
    selectedGradeLevelIds: string[];
    onGradeLevelIdsChange: (values: string[]) => void;
    scopes: ResetScopeOption[];
    eligibleGradeLevels: GradeLevel[];
    canSubmit: boolean;
}

function ResetForm({
    processing,
    errors,
    scope,
    onScopeSelect,
    selectedGradeLevelIds,
    onGradeLevelIdsChange,
    scopes,
    eligibleGradeLevels,
    canSubmit,
}: ResetFormProps) {
    const isSelectedScope = scope === 'selected';
    const hasEligibleGradeLevels = eligibleGradeLevels.length > 0;

    return (
        <DialogFormLayout>
            <DialogHeader>
                <DialogTitle>إعادة تعيين توزيع الفصول الدراسية</DialogTitle>
                <DialogDescription>
                    سيؤدي هذا الإجراء إلى إزالة توزيعات الفصول الدراسية، وإعادة
                    فتح عملية التوزيع. لا يمكن التراجع عن هذا الإجراء.
                </DialogDescription>
            </DialogHeader>

            <DialogBody>
                <ValidationErrors errors={errors} />

                <input type="hidden" name="scope" value={scope} />

                {isSelectedScope &&
                    selectedGradeLevelIds.map((gradeLevelId) => (
                        <input
                            key={gradeLevelId}
                            type="hidden"
                            name="grade_level_ids[]"
                            value={gradeLevelId}
                        />
                    ))}

                <Field>
                    <Label required>نطاق إعادة التعيين</Label>
                    <div className="grid grid-cols-1 gap-3">
                        {scopes.map((scopeOption) => {
                            const scopeValue = scopeOption.id as ResetScope;

                            return (
                                <ScopeOption
                                    key={scopeOption.id}
                                    value={scopeValue}
                                    label={scopeOption.name}
                                    description={scopeOption.description}
                                    checked={scope === scopeOption.id}
                                    onSelect={() => onScopeSelect(scopeValue)}
                                    disabled={
                                        scopeValue === 'selected' &&
                                        !hasEligibleGradeLevels
                                    }
                                />
                            );
                        })}
                    </div>
                    <InputError message={errors.scope} />
                </Field>

                {isSelectedScope && (
                    <Field>
                        <Label
                            htmlFor="grade_level_ids"
                            hasError={!!errors.grade_level_ids}
                            required
                        >
                            الصفوف الدراسية
                        </Label>

                        {hasEligibleGradeLevels ? (
                            <>
                                <div className="w-full min-w-0">
                                    <MultiSelect
                                        options={eligibleGradeLevels}
                                        onValueChange={onGradeLevelIdsChange}
                                        placeholder="اختر الصفوف الدراسية"
                                        defaultValue={selectedGradeLevelIds}
                                        singleLine={false}
                                        maxCount={2}
                                        minWidth="0"
                                        className="w-full max-w-full"
                                        popoverClassName="w-[var(--radix-popover-trigger-width)]"
                                    />
                                </div>

                                {selectedGradeLevelIds.length > 0 && (
                                    <p className="mt-2 text-xs text-muted-foreground">
                                        تم اختيار{' '}
                                        <span className="font-mono tabular-nums">
                                            {selectedGradeLevelIds.length}
                                        </span>{' '}
                                        صف/صفوف دراسية.
                                    </p>
                                )}
                            </>
                        ) : (
                            <Alert>
                                <AlertDescription>
                                    لا توجد صفوف دراسية متاحة لإعادة تعيين توزيع
                                    الفصول الدراسية.
                                </AlertDescription>
                            </Alert>
                        )}
                        <InputError message={errors.grade_level_ids} />
                        <InputError message={errors['grade_level_ids.0']} />
                    </Field>
                )}

                <Alert variant="warning">
                    <AlertTriangleIcon />
                    <AlertDescription>
                        سيتم حذف سجلات التوزيع ومسح تعيينات الفصول الدراسية من
                        الطلاب مع الإبقاء على بيانات التسجيل والطلاب.
                    </AlertDescription>
                </Alert>
            </DialogBody>

            <DialogFooter>
                <DialogClose asChild>
                    <Button
                        type="button"
                        variant="outline"
                        disabled={processing}
                    >
                        إلغاء
                    </Button>
                </DialogClose>

                <ConfirmButton
                    type="submit"
                    title="تأكيد إعادة التعيين"
                    processing={processing}
                    disabled={
                        !canSubmit ||
                        (isSelectedScope && !hasEligibleGradeLevels)
                    }
                />
            </DialogFooter>
        </DialogFormLayout>
    );
}

interface ScopeOptionProps {
    value: ResetScope;
    label: string;
    description: string;
    checked: boolean;
    onSelect: () => void;
    disabled?: boolean;
}

function ScopeOption({
    value,
    label,
    description,
    checked,
    onSelect,
    disabled = false,
}: ScopeOptionProps) {
    return (
        <button
            type="button"
            role="radio"
            aria-checked={checked}
            disabled={disabled}
            onClick={onSelect}
            className={cn(
                'flex w-full items-start gap-3 rounded-none border p-4 text-start transition-colors',
                checked
                    ? 'border-primary bg-primary/5'
                    : 'border-border hover:bg-muted/50',
                disabled && 'cursor-not-allowed opacity-50',
            )}
        >
            <span
                className={cn(
                    'mt-0.5 flex h-4 w-4 shrink-0 items-center justify-center rounded-full border',
                    checked ? 'border-primary' : 'border-muted-foreground/40',
                )}
            >
                {checked && (
                    <span className="h-2 w-2 rounded-full bg-primary" />
                )}
            </span>
            <span className="space-y-1">
                <span className="block text-sm font-medium">{label}</span>
                <span className="block text-xs leading-relaxed text-muted-foreground">
                    {description}
                </span>
            </span>
            <input
                type="radio"
                className="sr-only"
                value={value}
                checked={checked}
                readOnly
            />
        </button>
    );
}
