import React from "react";

import { Form } from "@inertiajs/react";

import { academicRecordYearLabel } from "@/lib/academic-record-label";

import type { AcademicYear, Enum, GradeLevel, GroupedAcademicRecord, Student } from "@/types";

import { CardDescription, CardFooter } from "@/components/ui/structure/card";
import { Separator } from "@/components/ui/structure/separator";

import Field from "@/components/ui/controls/field";
import { Label } from "@/components/ui/controls/label";
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/controls/select";
import InputError from "@/components/ui/controls/input-error";

import RequiredFieldsNote from "@/components/ui/display/required-fields-note";

import { CreateButton } from "@/components/ui/actions/submit-button";

import ValidationErrors from "@/components/ui/alerts/validation-errors";

import { PenLineIcon } from "lucide-react";

import { store } from "@/routes/school/students/academic-record";

interface AcademicRecordEntryFormProps {
    student: Student;
    record: GroupedAcademicRecord;
    currentGradeLevel: GradeLevel;
    selectableAcademicYears: AcademicYear[];
    academicRecordStatuses: Enum[];
    academicRecordRatings: Enum[];
}

export default function AcademicRecordEntryForm({
    student,
    record,
    currentGradeLevel,
    selectableAcademicYears,
    academicRecordStatuses,
    academicRecordRatings,
}: AcademicRecordEntryFormProps) {
    const [status, setStatus] = React.useState<string>("");

    const attemptNumber = record.attempts.length + 1;
    const isPassed = status === "passed";

    return (
        <>
            {record.attempts.length > 0 && <Separator />}

            <div className="rounded-none border border-dashed border-primary/30 p-4 md:p-5">
                <div className="flex items-center gap-2 mb-5">
                    <PenLineIcon className="size-4 shrink-0 text-primary" />
                    <p className="text-sm font-medium text-foreground">
                        {academicRecordYearLabel(attemptNumber)}
                    </p>
                </div>

                <Form
                    key={`academic-record-form-${record.grade_level.id}-${record.attempts.length}`}
                    {...store.form({ student: student })}
                    disableWhileProcessing
                    resetOnSuccess
                    options={{
                        preserveScroll: true,
                        preserveState: "errors",
                    }}
                >
                    {({ processing, errors }) => (
                        <div className="flex flex-col gap-6">
                            <ValidationErrors errors={errors} />

                            <input type="hidden" name="grade_level_id" value={currentGradeLevel.id} />

                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <Field>
                                    <Label
                                        htmlFor="academic_year_id"
                                        hasError={!!errors.academic_year_id}
                                        required
                                    >
                                        السنة الدراسية
                                    </Label>

                                    <Select
                                        key={`academic-year-select-${record.grade_level.id}-${record.attempts.length}`}
                                        name="academic_year_id"
                                    >
                                        <SelectTrigger
                                            hasError={!!errors.academic_year_id}
                                        >
                                            <SelectValue
                                                placeholder={
                                                    selectableAcademicYears.length > 0
                                                        ? "اختر السنة الدراسية"
                                                        : "لا توجد سنوات دراسية متاحة"
                                                }
                                            />
                                        </SelectTrigger>
                                        {selectableAcademicYears.length > 0 && (
                                            <SelectContent className="font-mono">
                                                <SelectGroup>
                                                    {selectableAcademicYears.map((year) => (
                                                        <SelectItem
                                                            key={year.id}
                                                            value={year.id.toString()}
                                                        >
                                                            {year.name}
                                                        </SelectItem>
                                                    ))}
                                                </SelectGroup>
                                            </SelectContent>
                                        )}
                                    </Select>

                                    <InputError message={errors.academic_year_id} />
                                </Field>

                                <Field>
                                    <Label
                                        htmlFor="status"
                                        hasError={!!errors.status}
                                        required
                                    >
                                        حالة الطالب
                                    </Label>

                                    <Select
                                        key={`status-select-${record.grade_level.id}-${record.attempts.length}`}
                                        name="status"
                                        value={status}
                                        onValueChange={setStatus}
                                    >
                                        <SelectTrigger
                                            hasError={!!errors.status}
                                        >
                                            <SelectValue placeholder="اختر حالة الطالب" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectGroup>
                                                {academicRecordStatuses.map((status) => (
                                                    <SelectItem
                                                        key={status.id}
                                                        value={status.id}
                                                    >
                                                        {status.name}
                                                    </SelectItem>
                                                ))}
                                            </SelectGroup>
                                        </SelectContent>
                                    </Select>

                                    <InputError message={errors.status} />
                                </Field>

                                {isPassed && (
                                    <Field>
                                        <Label
                                            htmlFor="rating"
                                            hasError={!!errors.rating}
                                            required
                                        >
                                            التقدير
                                        </Label>
                                        <Select
                                            key={`rating-select-${record.grade_level.id}-${record.attempts.length}`}
                                            name="rating"
                                        >
                                            <SelectTrigger hasError={!!errors.rating}>
                                                <SelectValue placeholder="اختر التقدير" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectGroup>
                                                    {academicRecordRatings.map((status) => (
                                                        <SelectItem
                                                            key={status.id}
                                                            value={status.id}
                                                        >
                                                            {status.name}
                                                        </SelectItem>
                                                    ))}
                                                </SelectGroup>
                                            </SelectContent>
                                        </Select>

                                        <InputError message={errors.rating} />
                                    </Field>
                                )}
                            </div>

                            <CardDescription className="text-xs text-muted-foreground">
                                <RequiredFieldsNote />
                            </CardDescription>

                            <CardFooter className="px-0 pb-0">
                                <CreateButton
                                    processing={processing}
                                    title="حفظ"
                                    disabled={selectableAcademicYears.length === 0}
                                />
                            </CardFooter>
                        </div>
                    )}
                </Form>
            </div>
        </>
    );
}
