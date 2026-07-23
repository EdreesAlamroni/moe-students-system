import React from 'react'

import { Form, Head, Link } from "@inertiajs/react";

import { decimalInputConstraints } from "@/lib/input-constraints";

import type { Enum, Nationality, Student, StudentPsychosocialCard } from "@/types";

import MainContainer from "@/components/ui/structure/main-container";
import { Card, CardContent, CardFormContent, CardFormFooter, CardHeader, CardTitle } from "@/components/ui/structure/card";
import { FormLayout } from "@/components/ui/structure/form-layout";
import { Separator } from "@/components/ui/structure/separator";

import Heading from "@/components/ui/display/heading";
import { DetailField, DetailFields } from "@/components/ui/display/detail-field";
import { DetailLabel } from "@/components/ui/display/detail-label";
import { DetailValue } from "@/components/ui/display/detail-value";

import Field from "@/components/ui/controls/field";
import { Label } from "@/components/ui/controls/label";
import { Input } from "@/components/ui/controls/input";
import LibyanPhoneNumberInput from "@/components/ui/controls/libyan-phone-number-input";
import { Textarea } from "@/components/ui/controls/textarea";
import { Checkbox } from "@/components/ui/controls/checkbox";
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/controls/select";
import DatePicker from "@/components/ui/controls/date-picker";
import InputError from "@/components/ui/controls/input-error";

import { Alert, AlertDescription, AlertTitle } from "@/components/ui/alerts/alert";
import ValidationErrors from "@/components/ui/alerts/validation-errors";

import { Button } from "@/components/ui/actions/button";
import { UpdateButton } from "@/components/ui/actions/submit-button";

import { StudentClassroomField, StudentGradeLevelField } from "@/components/shared/students/student-enrollment-fields";

import { AlertTriangleIcon, BookUserIcon, InfoIcon, NotepadTextIcon, ReplyIcon } from "lucide-react";

import { index as indexStudents, show as showStudents } from "@/routes/school/students";
import { show, edit, update } from "@/routes/school/students/psychosocial-card";

type PageProps = {
    student: Student;
    psychosocialCard?: StudentPsychosocialCard;
    isFromPreviousYear: boolean;
    previousAcademicYearName?: string;
    studentLivingSituations: Enum[];
    familySituationReasons: Enum[];
    healthLevels: Enum[];
    familyIncomes: Enum[];
    accommodationTypes: Enum[];
    accommodationForms: Enum[];
    behavioralProblems: Enum[];
    nationalities: Nationality[];
};

type BehavioralProblemState = {
    behavior: string;
    has_problem: boolean;
    notes: string;
};

export default function Edit({
    student,
    psychosocialCard,
    isFromPreviousYear,
    previousAcademicYearName,
    studentLivingSituations,
    familySituationReasons,
    healthLevels,
    familyIncomes,
    accommodationTypes,
    accommodationForms,
    behavioralProblems,
    nationalities,
}: PageProps) {
    const getFieldValue = (field: keyof StudentPsychosocialCard): string | number | undefined => {
        if (!psychosocialCard) {
            return undefined;
        }

        const value = psychosocialCard[field];

        if (value === null || value === undefined) {
            return undefined;
        }

        if (typeof value === 'object' && value !== null && 'value' in value) {
            return value.value as string | number | undefined;
        }

        return value as string | number | undefined;
    };

    const getStringValue = (field: keyof StudentPsychosocialCard): string | undefined => {
        const value = getFieldValue(field);

        return value === undefined || value === null ? undefined : String(value);
    };

    const [behavioralProblemsState, setBehavioralProblemsState] = React.useState<BehavioralProblemState[]>(() => {
        if (psychosocialCard?.behavioral_problems && Array.isArray(psychosocialCard.behavioral_problems)) {
            return behavioralProblems.map((problem) => {
                const existing = psychosocialCard.behavioral_problems?.find(
                    (behavioralProblem) => behavioralProblem.behavior === problem.id,
                );

                return {
                    behavior: problem.id,
                    has_problem: existing?.has_problem ?? false,
                    notes: existing?.notes ?? '',
                };
            });
        }

        return behavioralProblems.map((problem) => ({
            behavior: problem.id,
            has_problem: false,
            notes: '',
        }));
    });

    const updateBehavioralProblem = React.useCallback(
        (behavior: string, field: 'has_problem' | 'notes', value: boolean | string): void => {
            setBehavioralProblemsState((prev) => {
                return prev.map((behavioralProblem) => {
                    if (behavioralProblem.behavior === behavior) {
                        return { ...behavioralProblem, [field]: value };
                    }

                    return behavioralProblem;
                });
            });
        },
        [],
    );

    const behavioralProblemsData = React.useMemo(() => {
        return behavioralProblemsState
            .filter((behavioralProblem) => behavioralProblem.has_problem)
            .map((behavioralProblem) => ({
                behavior: behavioralProblem.behavior,
                has_problem: true,
                notes: behavioralProblem.notes.trim() || null,
            }));
    }, [behavioralProblemsState]);

    return (
        <>
            <Head title="تعديل البطاقة الإجتماعية والنفسية" />

            <MainContainer changeAcademicYearNotice>
                <section>
                    <header className="flex items-center gap-3 border-b pb-4">
                        <BookUserIcon className="w-4 h-4 shrink-0" />
                        <h1 className="text-sm font-medium text-foreground">
                            تعديل البطاقة الإجتماعية والنفسية
                        </h1>
                    </header>
                </section>

                <StudentDetailsSection student={student} />

                <Form
                    {...update.form({ student: student })}
                    disableWhileProcessing
                >
                    {({ processing, errors }) => (
                        <FormLayout>
                            <input type="hidden" name="behavioral_problems" value={JSON.stringify(behavioralProblemsData)} />

                            <ValidationErrors errors={errors} />

                            {isFromPreviousYear && previousAcademicYearName && (
                                <Alert variant="destructive">
                                    <AlertTriangleIcon />
                                    <AlertTitle>تنبيه</AlertTitle>
                                    <AlertDescription>
                                        <div>
                                            <span>يتم عرض بيانات من العام الدراسي السابق</span>
                                            <span className="inline-block mx-1 font-mono font-medium">{previousAcademicYearName}</span>
                                            <span>كمرجع فقط. سيتم حفظ البيانات المحدثة للعام الدراسي الحالي.</span>
                                        </div>
                                    </AlertDescription>
                                </Alert>
                            )}

                            <section>
                                <Card>
                                    <CardHeader className="border-b">
                                        <CardTitle>
                                            <BookUserIcon />
                                            <span>تعديل بيانات البطاقة الإجتماعية والنفسية</span>
                                        </CardTitle>
                                    </CardHeader>

                                    <CardFormContent>
                                        {/* Guardian Information */}
                                        <section aria-labelledby="guardian-section" className="space-y-6">
                                            <Heading
                                                variant="small"
                                                title="بيانات ولي الأمر"
                                                description="المعلومات الأساسية لولي أمر الطالب المسؤول عن متابعة شؤونه الدراسية"
                                            />
                                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                                <Field>
                                                    <Label
                                                        htmlFor="guardian_name"
                                                        hasError={!!errors.guardian_name}
                                                    >
                                                        اسم ولي الأمر
                                                    </Label>

                                                    <Input
                                                        id="guardian_name"
                                                        type="text"
                                                        name="guardian_name"
                                                        defaultValue={getStringValue('guardian_name') ?? student.father_full_name}
                                                        hasError={!!errors.guardian_name}
                                                        autoComplete="off"
                                                    />

                                                    <InputError message={errors.guardian_name} />
                                                </Field>

                                                <Field>
                                                    <Label
                                                        htmlFor="guardian_date_of_birth"
                                                        hasError={!!errors.guardian_date_of_birth}
                                                    >
                                                        تاريخ الميلاد
                                                    </Label>

                                                    <DatePicker
                                                        id="guardian_date_of_birth"
                                                        name="guardian_date_of_birth"
                                                        date={getStringValue('guardian_date_of_birth')}
                                                        hasError={!!errors.guardian_date_of_birth}
                                                    />

                                                    <InputError message={errors.guardian_date_of_birth} />
                                                </Field>

                                                <Field>
                                                    <Label
                                                        htmlFor="guardian_nationality_id"
                                                        hasError={!!errors.guardian_nationality_id}
                                                    >
                                                        الجنسية
                                                    </Label>

                                                    <Select
                                                        name="guardian_nationality_id"
                                                        defaultValue={getStringValue('guardian_nationality_id')}
                                                    >
                                                        <SelectTrigger
                                                            id="guardian_nationality_id"
                                                            hasError={!!errors.guardian_nationality_id}
                                                        >
                                                            <SelectValue placeholder="اختر الجنسية" />
                                                        </SelectTrigger>
                                                        <SelectContent>
                                                            <SelectGroup>
                                                                {nationalities.map((nationality) => (
                                                                    <SelectItem
                                                                        key={nationality.id}
                                                                        value={nationality.id.toString()}
                                                                    >
                                                                        {nationality.name}
                                                                    </SelectItem>
                                                                ))}
                                                            </SelectGroup>
                                                        </SelectContent>
                                                    </Select>

                                                    <InputError message={errors.guardian_nationality_id} />
                                                </Field>

                                                <Field>
                                                    <Label
                                                        htmlFor="guardian_relationship"
                                                        hasError={!!errors.guardian_relationship}
                                                    >
                                                        صلة القرابة
                                                    </Label>

                                                    <Input
                                                        id="guardian_relationship"
                                                        type="text"
                                                        name="guardian_relationship"
                                                        defaultValue={getStringValue('guardian_relationship')}
                                                        hasError={!!errors.guardian_relationship}
                                                        autoComplete="off"
                                                        placeholder="مثل: أب، جد، عم، إلخ"
                                                    />

                                                    <InputError message={errors.guardian_relationship} />
                                                </Field>

                                                <Field>
                                                    <Label
                                                        htmlFor="guardian_phone_number"
                                                        hasError={!!errors.guardian_phone_number}
                                                    >
                                                        رقم الهاتف
                                                    </Label>

                                                    <LibyanPhoneNumberInput
                                                        id="guardian_phone_number"
                                                        name="guardian_phone_number"
                                                        defaultValue={getStringValue('guardian_phone_number')}
                                                        hasError={!!errors.guardian_phone_number}
                                                    />

                                                    <InputError message={errors.guardian_phone_number} />
                                                </Field>

                                                <Field>
                                                    <Label
                                                        htmlFor="guardian_education_level"
                                                        hasError={!!errors.guardian_education_level}
                                                    >
                                                        المستوى التعليمي
                                                    </Label>

                                                    <Input
                                                        id="guardian_education_level"
                                                        type="text"
                                                        name="guardian_education_level"
                                                        defaultValue={getStringValue('guardian_education_level')}
                                                        hasError={!!errors.guardian_education_level}
                                                        autoComplete="off"
                                                        placeholder="مثل: جامعي، ثانوي، إلخ"
                                                    />

                                                    <InputError message={errors.guardian_education_level} />
                                                </Field>

                                                <Field>
                                                    <Label
                                                        htmlFor="guardian_job_title"
                                                        hasError={!!errors.guardian_job_title}
                                                    >
                                                        المسمى الوظيفي
                                                    </Label>

                                                    <Input
                                                        id="guardian_job_title"
                                                        type="text"
                                                        name="guardian_job_title"
                                                        defaultValue={getStringValue('guardian_job_title')}
                                                        hasError={!!errors.guardian_job_title}
                                                        autoComplete="off"
                                                        placeholder="مثل: مهندس، طبيب، إلخ"
                                                    />

                                                    <InputError message={errors.guardian_job_title} />
                                                </Field>

                                                <Field>
                                                    <Label
                                                        htmlFor="guardian_work_place"
                                                        hasError={!!errors.guardian_work_place}
                                                    >
                                                        مكان العمل
                                                    </Label>

                                                    <Input
                                                        id="guardian_work_place"
                                                        type="text"
                                                        name="guardian_work_place"
                                                        defaultValue={getStringValue('guardian_work_place')}
                                                        hasError={!!errors.guardian_work_place}
                                                        autoComplete="off"
                                                        placeholder="اسم المؤسسة أو مكان العمل"
                                                    />

                                                    <InputError message={errors.guardian_work_place} />
                                                </Field>
                                            </div>
                                        </section>

                                        <Separator />

                                        {/* Mother Information */}
                                        <section aria-labelledby="mother-section" className="space-y-6">
                                            <Heading
                                                variant="small"
                                                title="بيانات الأم"
                                                description="المعلومات الشخصية والتعليمية والمهنية لأم الطالب"
                                            />

                                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                                <Field>
                                                    <Label
                                                        htmlFor="mother_date_of_birth"
                                                        hasError={!!errors.mother_date_of_birth}
                                                    >
                                                        تاريخ الميلاد
                                                    </Label>

                                                    <DatePicker
                                                        id="mother_date_of_birth"
                                                        name="mother_date_of_birth"
                                                        date={getStringValue('mother_date_of_birth')}
                                                        hasError={!!errors.mother_date_of_birth}
                                                        defaultMonth={getStringValue('mother_date_of_birth') ? new Date(getStringValue('mother_date_of_birth')!) : undefined}
                                                    />

                                                    <InputError message={errors.mother_date_of_birth} />
                                                </Field>

                                                <Field>
                                                    <Label
                                                        htmlFor="mother_nationality_id"
                                                        hasError={!!errors.mother_nationality_id}
                                                    >
                                                        الجنسية
                                                    </Label>

                                                    <Select
                                                        name="mother_nationality_id"
                                                        defaultValue={getStringValue('mother_nationality_id')}
                                                    >
                                                        <SelectTrigger
                                                            id="mother_nationality_id"
                                                            hasError={!!errors.mother_nationality_id}
                                                        >
                                                            <SelectValue placeholder="اختر الجنسية" />
                                                        </SelectTrigger>
                                                        <SelectContent>
                                                            <SelectGroup>
                                                                {nationalities.map((nationality) => (
                                                                    <SelectItem
                                                                        key={nationality.id}
                                                                        value={nationality.id.toString()}
                                                                    >
                                                                        {nationality.name}
                                                                    </SelectItem>
                                                                ))}
                                                            </SelectGroup>
                                                        </SelectContent>
                                                    </Select>

                                                    <InputError message={errors.mother_nationality_id} />
                                                </Field>

                                                <Field>
                                                    <Label
                                                        htmlFor="mother_phone_number"
                                                        hasError={!!errors.mother_phone_number}
                                                    >
                                                        رقم الهاتف
                                                    </Label>

                                                    <LibyanPhoneNumberInput
                                                        id="mother_phone_number"
                                                        name="mother_phone_number"
                                                        defaultValue={getStringValue('mother_phone_number')}
                                                        hasError={!!errors.mother_phone_number}
                                                    />

                                                    <InputError message={errors.mother_phone_number} />
                                                </Field>

                                                <Field>
                                                    <Label
                                                        htmlFor="mother_education_level"
                                                        hasError={!!errors.mother_education_level}
                                                    >
                                                        المستوى التعليمي
                                                    </Label>

                                                    <Input
                                                        id="mother_education_level"
                                                        type="text"
                                                        name="mother_education_level"
                                                        defaultValue={getStringValue('mother_education_level')}
                                                        hasError={!!errors.mother_education_level}
                                                        autoComplete="off"
                                                        placeholder="مثل: جامعي، ثانوي، إلخ"
                                                    />

                                                    <InputError message={errors.mother_education_level} />
                                                </Field>

                                                <Field>
                                                    <Label
                                                        htmlFor="mother_profession"
                                                        hasError={!!errors.mother_profession}
                                                    >
                                                        المهنة
                                                    </Label>

                                                    <Input
                                                        id="mother_profession"
                                                        type="text"
                                                        name="mother_profession"
                                                        defaultValue={getStringValue('mother_profession')}
                                                        hasError={!!errors.mother_profession}
                                                        autoComplete="off"
                                                        placeholder="مثل: معلمة، طبيبة، إلخ"
                                                    />

                                                    <InputError message={errors.mother_profession} />
                                                </Field>

                                                <Field>
                                                    <Label
                                                        htmlFor="mother_work_place"
                                                        hasError={!!errors.mother_work_place}
                                                    >
                                                        مكان العمل
                                                    </Label>

                                                    <Input
                                                        id="mother_work_place"
                                                        type="text"
                                                        name="mother_work_place"
                                                        defaultValue={getStringValue('mother_work_place')}
                                                        hasError={!!errors.mother_work_place}
                                                        autoComplete="off"
                                                        placeholder="اسم المؤسسة أو مكان العمل"
                                                    />

                                                    <InputError message={errors.mother_work_place} />
                                                </Field>
                                            </div>
                                        </section>

                                        <Separator />

                                        {/* Family Structure */}
                                        <section aria-labelledby="family-structure-section" className="space-y-6">
                                            <Heading
                                                variant="small"
                                                title="التركيب الأسري"
                                                description="معلومات عن تكوين الأسرة وترتيب الطالب بين أفرادها"
                                            />
                                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                                <Field>
                                                    <Label
                                                        htmlFor="number_of_family_members"
                                                        hasError={!!errors.number_of_family_members}
                                                    >
                                                        عدد أفراد الأسرة
                                                    </Label>

                                                    <Input
                                                        id="number_of_family_members"
                                                        type="number"
                                                        name="number_of_family_members"
                                                        className="font-mono"
                                                        defaultValue={getFieldValue('number_of_family_members')}
                                                        hasError={!!errors.number_of_family_members}
                                                        autoComplete="off"
                                                        min="0"
                                                        {...decimalInputConstraints({
                                                            allowDecimal: false,
                                                            allowNegative: false,
                                                            min: 0,
                                                        })}
                                                    />

                                                    <InputError message={errors.number_of_family_members} />
                                                </Field>

                                                <Field>
                                                    <Label
                                                        htmlFor="student_family_order"
                                                        hasError={!!errors.student_family_order}
                                                    >
                                                        ترتيب الطالب في الأسرة
                                                    </Label>

                                                    <Input
                                                        id="student_family_order"
                                                        type="number"
                                                        name="student_family_order"
                                                        className="font-mono"
                                                        defaultValue={getFieldValue('student_family_order')}
                                                        hasError={!!errors.student_family_order}
                                                        autoComplete="off"
                                                        min="0"
                                                        {...decimalInputConstraints({
                                                            allowDecimal: false,
                                                            allowNegative: false,
                                                            min: 0,
                                                        })}
                                                    />

                                                    <InputError message={errors.student_family_order} />
                                                </Field>

                                                <Field>
                                                    <Label
                                                        htmlFor="number_of_siblings"
                                                        hasError={!!errors.number_of_siblings}
                                                    >
                                                        عدد الإخوة
                                                    </Label>

                                                    <Input
                                                        id="number_of_siblings"
                                                        type="number"
                                                        name="number_of_siblings"
                                                        className="font-mono"
                                                        defaultValue={getFieldValue('number_of_siblings')}
                                                        hasError={!!errors.number_of_siblings}
                                                        autoComplete="off"
                                                        min="0"
                                                        {...decimalInputConstraints({
                                                            allowDecimal: false,
                                                            allowNegative: false,
                                                            min: 0,
                                                        })}
                                                    />

                                                    <InputError message={errors.number_of_siblings} />
                                                </Field>
                                            </div>
                                        </section>

                                        <Separator />

                                        {/* Living Situation */}
                                        <section aria-labelledby="living-situation-section" className="space-y-6">
                                            <Heading
                                                variant="small"
                                                title="الوضع السكني"
                                                description="تفاصيل مكان السكن والعنوان الكامل والأنشطة والمواهب التي يمارسها الطالب"
                                            />
                                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                                <Field>
                                                    <Label
                                                        htmlFor="student_living_situation"
                                                        hasError={!!errors.student_living_situation}
                                                    >
                                                        معيشة الطالب
                                                    </Label>

                                                    <Select
                                                        name="student_living_situation"
                                                        defaultValue={getStringValue('student_living_situation')}
                                                    >
                                                        <SelectTrigger
                                                            id="student_living_situation"
                                                            hasError={!!errors.student_living_situation}
                                                        >
                                                            <SelectValue placeholder="اختر وضع المعيشة" />
                                                        </SelectTrigger>
                                                        <SelectContent>
                                                            <SelectGroup>
                                                                {studentLivingSituations.map((situation) => (
                                                                    <SelectItem
                                                                        key={situation.id}
                                                                        value={situation.id}
                                                                    >
                                                                        {situation.name}
                                                                    </SelectItem>
                                                                ))}
                                                            </SelectGroup>
                                                        </SelectContent>
                                                    </Select>

                                                    <InputError message={errors.student_living_situation} />
                                                </Field>

                                                <Field>
                                                    <Label
                                                        htmlFor="family_situation_reason"
                                                        hasError={!!errors.family_situation_reason}
                                                    >
                                                        السبب
                                                    </Label>

                                                    <Select
                                                        name="family_situation_reason"
                                                        defaultValue={getStringValue('family_situation_reason')}
                                                    >
                                                        <SelectTrigger
                                                            id="family_situation_reason"
                                                            hasError={!!errors.family_situation_reason}
                                                        >
                                                            <SelectValue placeholder="اختر السبب" />
                                                        </SelectTrigger>
                                                        <SelectContent>
                                                            <SelectGroup>
                                                                {familySituationReasons.map((reason) => (
                                                                    <SelectItem
                                                                        key={reason.id}
                                                                        value={reason.id}
                                                                    >
                                                                        {reason.name}
                                                                    </SelectItem>
                                                                ))}
                                                            </SelectGroup>
                                                        </SelectContent>
                                                    </Select>

                                                    <InputError message={errors.family_situation_reason} />
                                                </Field>

                                                <Field>
                                                    <Label
                                                        htmlFor="residential_area"
                                                        hasError={!!errors.residential_area}
                                                    >
                                                        المنطقة
                                                    </Label>

                                                    <Input
                                                        id="residential_area"
                                                        type="text"
                                                        name="residential_area"
                                                        defaultValue={getStringValue('residential_area')}
                                                        hasError={!!errors.guardian_name}
                                                        autoComplete="off"
                                                        placeholder="أدخل اسم المنطقة"
                                                    />

                                                    <InputError message={errors.residential_area} />
                                                </Field>

                                                <Field>
                                                    <Label
                                                        htmlFor="residential_street"
                                                        hasError={!!errors.residential_street}
                                                    >
                                                        الشارع
                                                    </Label>

                                                    <Input
                                                        id="residential_street"
                                                        type="text"
                                                        name="residential_street"
                                                        defaultValue={getStringValue('residential_street')}
                                                        hasError={!!errors.residential_street}
                                                        autoComplete="off"
                                                        placeholder="أدخل اسم الشارع"
                                                    />

                                                    <InputError message={errors.residential_street} />
                                                </Field>

                                                <Field className="col-span-full">
                                                    <Label
                                                        htmlFor="nearest_landmark"
                                                        hasError={!!errors.nearest_landmark}
                                                    >
                                                        أقرب نقطة دالة
                                                    </Label>

                                                    <Input
                                                        id="nearest_landmark"
                                                        type="text"
                                                        name="nearest_landmark"
                                                        defaultValue={getStringValue('nearest_landmark')}
                                                        hasError={!!errors.nearest_landmark}
                                                        autoComplete="off"
                                                        placeholder="مثل: مسجد، مدرسة، مستشفى، إلخ"
                                                    />

                                                    <InputError message={errors.nearest_landmark} />
                                                </Field>

                                                <Field className="col-span-full">
                                                    <Label
                                                        htmlFor="previous_activities"
                                                        hasError={!!errors.previous_activities}
                                                    >
                                                        النشاطات التي شارك فيها الطالب سابقاً
                                                    </Label>

                                                    <Textarea
                                                        id="previous_activities"
                                                        name="previous_activities"
                                                        defaultValue={getStringValue('previous_activities')}
                                                        hasError={!!errors.previous_activities}
                                                        rows={3}
                                                        placeholder="اذكر النشاطات التي شارك فيها الطالب في السنوات السابقة"
                                                    />

                                                    <InputError message={errors.previous_activities} />
                                                </Field>

                                                <Field className="col-span-full">
                                                    <Label
                                                        htmlFor="talents"
                                                        hasError={!!errors.talents}
                                                    >
                                                        المواهب التي يتمتع بها الطالب
                                                    </Label>

                                                    <Textarea
                                                        id="talents"
                                                        name="talents"
                                                        defaultValue={getStringValue('talents')}
                                                        hasError={!!errors.talents}
                                                        rows={3}
                                                        placeholder="اذكر المواهب والقدرات الخاصة التي يتمتع بها الطالب"
                                                    />

                                                    <InputError message={errors.talents} />
                                                </Field>
                                            </div>
                                        </section>

                                        <Separator />

                                        {/* Health Information */}
                                        <section aria-labelledby="health-section" className="space-y-6">
                                            <Heading
                                                variant="small"
                                                title="البيانات الصحية"
                                                description="معلومات عن الحالة الصحية للطالب والأمراض السابقة والإعاقات إن وجدت"
                                            />
                                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                                <Field className="col-span-full">
                                                    <Label
                                                        htmlFor="previous_diseases"
                                                        hasError={!!errors.previous_diseases}
                                                    >
                                                        الأمراض التي سبق الإصابة بها
                                                    </Label>

                                                    <Textarea
                                                        id="previous_diseases"
                                                        name="previous_diseases"
                                                        defaultValue={getStringValue('previous_diseases')}
                                                        hasError={!!errors.previous_diseases}
                                                        rows={3}
                                                        placeholder="اذكر الأمراض التي أصيب بها الطالب سابقاً مع تواريخ الإصابة إن أمكن"
                                                    />

                                                    <InputError message={errors.previous_diseases} />
                                                </Field>

                                                <Field className="col-span-full">
                                                    <Label
                                                        htmlFor="physical_disability_type"
                                                        hasError={!!errors.physical_disability_type}
                                                    >
                                                        نوع الإعاقة الجسمية إن وُجدت
                                                    </Label>

                                                    <Textarea
                                                        id="physical_disability_type"
                                                        name="physical_disability_type"
                                                        defaultValue={getStringValue('physical_disability_type')}
                                                        hasError={!!errors.physical_disability_type}
                                                        rows={3}
                                                        placeholder="اذكر نوع الإعاقة الجسمية إن وجدت مع تفاصيل إضافية"
                                                    />

                                                    <InputError message={errors.physical_disability_type} />
                                                </Field>

                                                <Field>
                                                    <Label
                                                        htmlFor="vision_level"
                                                        hasError={!!errors.vision_level}
                                                    >
                                                        مستوى النظر
                                                    </Label>

                                                    <Select
                                                        name="vision_level"
                                                        defaultValue={getStringValue('vision_level')}
                                                    >
                                                        <SelectTrigger
                                                            id="vision_level"
                                                            hasError={!!errors.vision_level}
                                                        >
                                                            <SelectValue placeholder="اختر المستوى" />
                                                        </SelectTrigger>
                                                        <SelectContent>
                                                            <SelectGroup>
                                                                {healthLevels.map((level) => (
                                                                    <SelectItem
                                                                        key={level.id}
                                                                        value={level.id}
                                                                    >
                                                                        {level.name}
                                                                    </SelectItem>
                                                                ))}
                                                            </SelectGroup>
                                                        </SelectContent>
                                                    </Select>

                                                    <InputError message={errors.vision_level} />
                                                </Field>

                                                <Field>
                                                    <Label
                                                        htmlFor="hearing_level"
                                                        hasError={!!errors.hearing_level}
                                                    >
                                                        السمع
                                                    </Label>

                                                    <Select
                                                        name="hearing_level"
                                                        defaultValue={getStringValue('hearing_level')}
                                                    >
                                                        <SelectTrigger
                                                            id="hearing_level"
                                                            hasError={!!errors.hearing_level}
                                                        >
                                                            <SelectValue placeholder="اختر المستوى" />
                                                        </SelectTrigger>
                                                        <SelectContent>
                                                            <SelectGroup>
                                                                {healthLevels.map((level) => (
                                                                    <SelectItem
                                                                        key={level.id}
                                                                        value={level.id}
                                                                    >
                                                                        {level.name}
                                                                    </SelectItem>
                                                                ))}
                                                            </SelectGroup>
                                                        </SelectContent>
                                                    </Select>

                                                    <InputError message={errors.hearing_level} />
                                                </Field>
                                            </div>
                                            <Alert>
                                                <AlertTriangleIcon />
                                                <AlertTitle>ملاحظة مهمة</AlertTitle>
                                                <AlertDescription>
                                                    في حالة وجود مرض داخلي يرجى إحضار تقرير طبي معتمد ومترجم باللغة العربية مع توصيات الطبيب.
                                                </AlertDescription>
                                            </Alert>
                                        </section>

                                        <Separator />

                                        {/* Accommodation and Income */}
                                        <section aria-labelledby="accommodation-section" className="space-y-6">
                                            <Heading
                                                variant="small"
                                                title="الحالة الاقتصادية ونوع السكن للأسرة"
                                                description="معلومات عن الوضع الاقتصادي للأسرة ومواصفات السكن"
                                            />
                                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                                <Field>
                                                    <Label
                                                        htmlFor="family_income"
                                                        hasError={!!errors.family_income}
                                                    >
                                                        دخل الأسرة
                                                    </Label>

                                                    <Select
                                                        name="family_income"
                                                        defaultValue={getStringValue('family_income')}
                                                    >
                                                        <SelectTrigger
                                                            id="family_income"
                                                            hasError={!!errors.family_income}
                                                        >
                                                            <SelectValue placeholder="اختر مستوى الدخل" />
                                                        </SelectTrigger>
                                                        <SelectContent>
                                                            <SelectGroup>
                                                                {familyIncomes.map((income) => (
                                                                    <SelectItem
                                                                        key={income.id}
                                                                        value={income.id}
                                                                    >
                                                                        {income.name}
                                                                    </SelectItem>
                                                                ))}
                                                            </SelectGroup>
                                                        </SelectContent>
                                                    </Select>

                                                    <InputError message={errors.family_income} />
                                                </Field>

                                                <Field>
                                                    <Label
                                                        htmlFor="accommodation_type"
                                                        hasError={!!errors.accommodation_type}
                                                    >
                                                        نوع السكن
                                                    </Label>

                                                    <Select
                                                        name="accommodation_type"
                                                        defaultValue={getStringValue('accommodation_type')}
                                                    >
                                                        <SelectTrigger
                                                            id="accommodation_type"
                                                            hasError={!!errors.accommodation_type}
                                                        >
                                                            <SelectValue placeholder="اختر نوع السكن" />
                                                        </SelectTrigger>
                                                        <SelectContent>
                                                            <SelectGroup>
                                                                {accommodationTypes.map((type) => (
                                                                    <SelectItem
                                                                        key={type.id}
                                                                        value={type.id}
                                                                    >
                                                                        {type.name}
                                                                    </SelectItem>
                                                                ))}
                                                            </SelectGroup>
                                                        </SelectContent>
                                                    </Select>

                                                    <InputError message={errors.accommodation_type} />
                                                </Field>

                                                <Field>
                                                    <Label
                                                        htmlFor="accommodation_form"
                                                        hasError={!!errors.accommodation_form}
                                                    >
                                                        مواصفات السكن
                                                    </Label>

                                                    <Select
                                                        name="accommodation_form"
                                                        defaultValue={getStringValue('accommodation_form')}
                                                    >
                                                        <SelectTrigger
                                                            id="accommodation_form"
                                                            hasError={!!errors.accommodation_form}
                                                        >
                                                            <SelectValue placeholder="اختر شكل السكن" />
                                                        </SelectTrigger>
                                                        <SelectContent>
                                                            <SelectGroup>
                                                                {accommodationForms.map((form) => (
                                                                    <SelectItem
                                                                        key={form.id}
                                                                        value={form.id}
                                                                    >
                                                                        {form.name}
                                                                    </SelectItem>
                                                                ))}
                                                            </SelectGroup>
                                                        </SelectContent>
                                                    </Select>

                                                    <InputError message={errors.accommodation_form} />
                                                </Field>
                                            </div>
                                        </section>

                                        <Separator />

                                        {/* Behavioral Problems */}
                                        <section aria-labelledby="behavioral-section" className="space-y-6">
                                            <Heading
                                                variant="small"
                                                title="المشاكل السلوكية"
                                                description="المشاكل (الاضطرابات) السلوكية التي يعاني منها الطالب من وجهة نظر ولي الأمر خلال تواجده في البيت"
                                            />
                                            <div className="space-y-4">
                                                <div className="border divide-y overflow-hidden">
                                                    <div className="grid grid-cols-12 gap-4 p-4 bg-muted/50 text-sm font-medium border-b">
                                                        <div className="col-span-5">السلوك</div>
                                                        <div className="col-span-2 text-center">نعم</div>
                                                        <div className="col-span-5">ملاحظات</div>
                                                    </div>
                                                    {behavioralProblems.map((problem) => {
                                                        const problemState = behavioralProblemsState.find(
                                                            (behavioralProblem) => behavioralProblem.behavior === problem.id
                                                        );
                                                        const hasProblem = problemState?.has_problem ?? false;
                                                        const notes = problemState?.notes ?? '';
                                                        const inputId = `behavioral_problem_${problem.id}`;
                                                        const notesId = `behavioral_problem_notes_${problem.id}`;

                                                        return (
                                                            <div
                                                                key={problem.id}
                                                                className="grid grid-cols-12 gap-4 p-4 items-start hover:bg-muted/30 transition-colors"
                                                            >
                                                                <div className="col-span-5 flex items-center">
                                                                    <Label htmlFor={inputId} className="font-normal cursor-pointer text-sm">
                                                                        {problem.name}
                                                                    </Label>
                                                                </div>
                                                                <div className="col-span-2 flex justify-center">
                                                                    <Checkbox
                                                                        id={inputId}
                                                                        checked={hasProblem}
                                                                        onCheckedChange={(checked: boolean | "indeterminate") => {
                                                                            updateBehavioralProblem(problem.id, 'has_problem', checked === true);
                                                                        }}
                                                                    />
                                                                </div>
                                                                <div className="col-span-5">
                                                                    {hasProblem ? (
                                                                        <Textarea
                                                                            id={notesId}
                                                                            value={notes}
                                                                            onChange={(e) => {
                                                                                updateBehavioralProblem(problem.id, 'notes', e.target.value);
                                                                            }}
                                                                            rows={1}
                                                                            placeholder="أدخل ملاحظات..."
                                                                        />
                                                                    ) : (
                                                                        <span className="text-muted-foreground font-mono">-</span>
                                                                    )}
                                                                </div>
                                                            </div>
                                                        );
                                                    })}
                                                </div>
                                                {errors.behavioral_problems && (
                                                    <InputError message={errors.behavioral_problems} />
                                                )}
                                            </div>
                                        </section>

                                        <Separator />

                                        {/* Guardian Representative */}
                                        <section aria-labelledby="representative-section" className="space-y-6">
                                            <Heading
                                                variant="small"
                                                title="الممثل عن ولي الأمر"
                                                description="في حالة تعذر ولي الأمر عن زيارة المدرسة فمن ينوب عنه"
                                            />
                                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                                <Field>
                                                    <Label
                                                        htmlFor="guardian_representative_name"
                                                        hasError={!!errors.guardian_representative_name}
                                                    >
                                                        الاسم
                                                    </Label>

                                                    <Input
                                                        id="guardian_representative_name"
                                                        type="text"
                                                        name="guardian_representative_name"
                                                        defaultValue={getStringValue('guardian_representative_name')}
                                                        hasError={!!errors.previous_activities}
                                                        autoComplete="off"
                                                        placeholder="الاسم الكامل"
                                                    />

                                                    <InputError message={errors.guardian_representative_name} />
                                                </Field>

                                                <Field>
                                                    <Label
                                                        htmlFor="guardian_representative_relationship"
                                                        hasError={!!errors.guardian_representative_relationship}
                                                    >
                                                        صلة القرابة
                                                    </Label>

                                                    <Input
                                                        id="guardian_representative_relationship"
                                                        type="text"
                                                        name="guardian_representative_relationship"
                                                        defaultValue={getStringValue('guardian_representative_relationship')}
                                                        hasError={!!errors.guardian_representative_relationship}
                                                        autoComplete="off"
                                                        placeholder="مثل: عم، خال، إلخ"
                                                    />

                                                    <InputError message={errors.guardian_representative_relationship} />
                                                </Field>

                                                <Field>
                                                    <Label
                                                        htmlFor="guardian_representative_id_card_number"
                                                        hasError={!!errors.guardian_representative_id_card_number}
                                                    >
                                                        رقم بطاقة الهوية
                                                    </Label>

                                                    <Input
                                                        id="guardian_representative_id_card_number"
                                                        type="text"
                                                        name="guardian_representative_id_card_number"
                                                        defaultValue={getStringValue('guardian_representative_id_card_number')}
                                                        hasError={!!errors.guardian_representative_id_card_number}
                                                        autoComplete="off"
                                                        placeholder="أدخل رقم بطاقة الهوية"
                                                        onInput={(e: React.InputEvent<HTMLInputElement>): void => {
                                                            const input = e.currentTarget;
                                                            input.value = input.value.replace(/[^0-9a-zA-Z]/g, '');
                                                            input.classList.toggle("font-mono", input.value.length > 0);
                                                        }}
                                                    />

                                                    <InputError message={errors.guardian_representative_id_card_number} />
                                                </Field>

                                                <Field>
                                                    <Label
                                                        htmlFor="guardian_representative_phone_number"
                                                        hasError={!!errors.guardian_representative_phone_number}
                                                    >
                                                        رقم الهاتف
                                                    </Label>

                                                    <LibyanPhoneNumberInput
                                                        id="guardian_representative_phone_number"
                                                        name="guardian_representative_phone_number"
                                                        defaultValue={getStringValue('guardian_representative_phone_number')}
                                                        hasError={!!errors.guardian_representative_phone_number}
                                                    />

                                                    <InputError message={errors.guardian_representative_phone_number} />
                                                </Field>

                                                <Field>
                                                    <Label
                                                        htmlFor="guardian_representative_work_place"
                                                        hasError={!!errors.guardian_representative_work_place}
                                                    >
                                                        مكان العمل
                                                    </Label>

                                                    <Input
                                                        id="guardian_representative_work_place"
                                                        type="text"
                                                        name="guardian_representative_work_place"
                                                        defaultValue={getStringValue('guardian_representative_work_place')}
                                                        hasError={!!errors.guardian_representative_work_place}
                                                        autoComplete="off"
                                                        placeholder="اسم المؤسسة أو مكان العمل"
                                                    />

                                                    <InputError message={errors.guardian_representative_work_place} />
                                                </Field>
                                            </div>
                                            <Alert>
                                                <InfoIcon />
                                                <AlertTitle>معلومات مهمة</AlertTitle>
                                                <AlertDescription>
                                                    ضرورة زيارة ولي الأمر للمدرسة لمتابعة تحصيل ابنه الدراسي والاطلاع على سلوكه العام، ومراجعة الأخصائي الاجتماعي والإرشاد النفسي في بداية كل أسبوع ما بعد الساعة العاشرة صباحاً للفترة الصباحية والساعة الثانية مساءً للفترة المسائية.
                                                </AlertDescription>
                                            </Alert>
                                        </section>
                                    </CardFormContent>

                                    <CardFormFooter>
                                        <Button variant="outline" className="flex items-center gap-x-2" asChild>
                                            <Link href={show.url({ student: student })}>
                                                <ReplyIcon />
                                                <span>إلغاء الأمر</span>
                                            </Link>
                                        </Button>

                                        <UpdateButton processing={processing} />
                                    </CardFormFooter>
                                </Card>
                            </section>
                        </FormLayout>
                    )}
                </Form>
            </MainContainer>
        </>
    );
}

Edit.layout = (props: PageProps) => ({
    breadcrumbs: [
        {
            title: 'الطلاب',
            href: indexStudents.url(),
        },
        {
            title: 'عرض بيانات الطالب',
            href: showStudents.url({ student: props.student }),
        },
        {
            title: 'البطاقة الإجتماعية والنفسية',
            href: show.url({ student: props.student }),
        },
        {
            title: 'تعديل البطاقة الإجتماعية والنفسية',
            href: edit.url({ student: props.student }),
        },
    ],
});

function StudentDetailsSection({ student }: { student: Student }) {
    return (
        <section>
            <Card>
                <CardHeader className="border-b">
                    <CardTitle>
                        <NotepadTextIcon />
                        <span>عرض بيانات الطالب</span>
                    </CardTitle>
                </CardHeader>
                <CardContent className="flex flex-col gap-6">
                    <DetailFields columns={2}>
                        <StudentGradeLevelField student={student} />

                        <StudentClassroomField student={student} />

                        <DetailField>
                            <DetailLabel>اسم الطالب</DetailLabel>
                            <DetailValue value={student.full_name} />
                        </DetailField>

                        <DetailField>
                            <DetailLabel>الجنسية</DetailLabel>
                            <DetailValue value={student.nationality?.name} />
                        </DetailField>

                        {student.is_libyan && (
                            <>
                                <DetailField>
                                    <DetailLabel>الرقم الوطني</DetailLabel>
                                    <DetailValue value={student.national_id} className="font-mono" />
                                </DetailField>

                                <DetailField>
                                    <DetailLabel>رقم القيد</DetailLabel>
                                    <DetailValue value={student.family_registration_number} className="font-mono" />
                                </DetailField>
                            </>
                        )}
                    </DetailFields>
                </CardContent>
            </Card>
        </section>
    );
}
