import React from "react";

import type { Enum, School } from "@/types";

import Field from "@/components/ui/controls/field";
import { Label } from "@/components/ui/controls/label";
import { Checkbox } from "@/components/ui/controls/checkbox";
import { EmptyOptionsInput } from "@/components/ui/controls/empty-options-input";
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/components/ui/controls/select";
import InputError from "@/components/ui/controls/input-error";

export type SchoolWithPeriods = Pick<School, "id" | "name"> & {
    periods: Array<{
        id: number;
        name: string;
        academic_period: Enum;
    }>;
};

type UseSchoolPeriodAssignmentOptions = {
    schools: SchoolWithPeriods[];
    initialSchoolId?: number | null;
    initialPeriodIds?: number[];
};

export function useSchoolPeriodAssignment({
    schools,
    initialSchoolId = null,
    initialPeriodIds = [],
}: UseSchoolPeriodAssignmentOptions) {
    const [selectedSchoolId, setSelectedSchoolId] = React.useState(
        () => initialSchoolId?.toString() ?? "",
    );
    const [selectedPeriodIds, setSelectedPeriodIds] = React.useState<number[]>(
        () => initialPeriodIds,
    );

    const selectedSchool = React.useMemo(
        () => schools.find((school) => school.id.toString() === selectedSchoolId),
        [schools, selectedSchoolId],
    );

    const handleSchoolChange = (value: string): void => {
        setSelectedSchoolId(value);

        const school = schools.find((item) => item.id.toString() === value);

        if (school?.periods.length === 1) {
            setSelectedPeriodIds([school.periods[0].id]);

            return;
        }

        setSelectedPeriodIds([]);
    };

    const togglePeriod = (periodId: number, checked: boolean): void => {
        setSelectedPeriodIds((current) => {
            if (checked) {
                return current.includes(periodId)
                    ? current
                    : [...current, periodId];
            }

            return current.filter((id) => id !== periodId);
        });
    };

    return {
        selectedSchoolId,
        selectedSchool,
        selectedPeriodIds,
        handleSchoolChange,
        togglePeriod,
    };
}

type SchoolUserPeriodFieldsetProps = {
    schools: SchoolWithPeriods[];
    selectedSchoolId: string;
    selectedPeriodIds: number[];
    onSchoolChange: (value: string) => void;
    onPeriodToggle: (periodId: number, checked: boolean) => void;
    errors: Record<string, string | undefined>;
    enabled?: boolean;
    disabledPlaceholder?: string;
    className?: string;
};

export default function SchoolUserPeriodFieldset({
    schools,
    selectedSchoolId,
    selectedPeriodIds,
    onSchoolChange,
    onPeriodToggle,
    errors,
    enabled = true,
    disabledPlaceholder = "لا توجد مدارس متاحة للاختيار",
    className,
}: SchoolUserPeriodFieldsetProps) {
    const selectedSchool = schools.find(
        (school) => school.id.toString() === selectedSchoolId,
    );
    const hasDualPeriods = (selectedSchool?.periods.length ?? 0) > 1;

    return (
        <>
            <Field className={className}>
                <Label
                    htmlFor="school_id"
                    hasError={!!errors.school_id}
                    required
                >
                    المدرسة
                </Label>

                {enabled && schools.length > 0 ? (
                    <Select
                        name="school_id"
                        value={selectedSchoolId}
                        onValueChange={onSchoolChange}
                    >
                        <SelectTrigger
                            id="school_id"
                            hasError={!!errors.school_id}
                        >
                            <SelectValue placeholder="اختر المدرسة" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectGroup>
                                {schools.map((school) => (
                                    <SelectItem
                                        key={school.id}
                                        value={school.id.toString()}
                                    >
                                        {school.name}
                                    </SelectItem>
                                ))}
                            </SelectGroup>
                        </SelectContent>
                    </Select>
                ) : (
                    <EmptyOptionsInput
                        id="school_id"
                        placeholder={enabled ? disabledPlaceholder : "يرجى اختيار المُراقبة أولاً"}
                        hasError={!!errors.school_id}
                    />
                )}

                <InputError message={errors.school_id} />
            </Field>

            {selectedSchool?.periods.length === 1 && (
                <input
                    type="hidden"
                    name="school_period_ids[]"
                    value={selectedSchool.periods[0].id}
                />
            )}

            {hasDualPeriods && (
                <Field className="col-span-full">
                    <Label hasError={!!errors.school_period_ids} required>
                        الفترات الدراسية
                    </Label>

                    <p className="mb-2 text-sm text-muted-foreground">
                        حدد الفترة أو الفترات التي يمكن للمُستخدم الوصول إلى بياناتها في لوحة تحكم المدرسة.
                    </p>

                    <div className="space-y-3">
                        {selectedSchool?.periods.map((period) => {
                            const inputId = `school_period_${period.id}`;

                            return (
                                <div
                                    key={period.id}
                                    className="flex items-center gap-x-3"
                                >
                                    <Checkbox
                                        id={inputId}
                                        checked={selectedPeriodIds.includes(period.id)}
                                        onCheckedChange={(checked) => {
                                            onPeriodToggle(period.id, checked === true);
                                        }}
                                    />

                                    <Label
                                        htmlFor={inputId}
                                        style={{ fontWeight: "500" }}
                                    >
                                        {period.academic_period.name}
                                    </Label>
                                </div>
                            );
                        })}
                    </div>

                    {selectedPeriodIds.map((periodId) => (
                        <input
                            key={periodId}
                            type="hidden"
                            name="school_period_ids[]"
                            value={periodId}
                        />
                    ))}

                    <InputError message={errors.school_period_ids} />
                </Field>
            )}
        </>
    );
}
