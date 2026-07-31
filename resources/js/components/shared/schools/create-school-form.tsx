import React from 'react';

import { Form, Link } from '@inertiajs/react';
import type { FormDataErrors } from '@inertiajs/core';

import type { CreateSchoolFormData, Enum, GradeLevel } from '@/types';

import MainContainer from '@/components/ui/structure/main-container';
import { Card, CardDescription, CardFooter, CardFormContent, CardHeader, CardTitle } from "@/components/ui/structure/card";
import { FormLayout } from "@/components/ui/structure/form-layout";
import { Separator } from "@/components/ui/structure/separator";

import RequiredFieldsNote from "@/components/ui/display/required-fields-note";

import Field from "@/components/ui/controls/field";
import { Label } from "@/components/ui/controls/label";
import { Input } from "@/components/ui/controls/input";
import { MultiSelect } from "@/components/ui/controls/multi-select";
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/controls/select";
import { RadioGroup, RadioGroupItem } from "@/components/ui/controls/radio-group";
import InputError from "@/components/ui/controls/input-error";

import ValidationErrors from "@/components/ui/alerts/validation-errors";

import { Button } from "@/components/ui/actions/button";
import { CreateButton } from "@/components/ui/actions/submit-button";

import { ReplyIcon } from 'lucide-react';

type FormOptions = Omit<React.ComponentProps<typeof Form<CreateSchoolFormData>>, 'children'>;

export type CreateSchoolFormProps = {
    form: FormOptions;
    indexUrl: string;
    organizationFields?: (errors: FormDataErrors<CreateSchoolFormData>) => React.ReactNode;
    types: Enum[];
    academicPeriods: Enum[];
    studentsGender: Enum[];
    branchTypes: Enum[];
    buildingTypes: Enum[];
    educationalStages: Enum[];
    gradeLevels: GradeLevel[];
    schoolPrivateType: string;
    schoolDualAcademicPeriod: string;
};

function filterGradeLevelsByStages(
    gradeLevels: GradeLevel[],
    stages: string[],
): GradeLevel[] {
    if (stages.length === 0) {
        return [];
    }

    return gradeLevels.filter((gradeLevel) => stages.includes(gradeLevel.educational_stage.id));
}

function pruneGradeLevelSelections(
    selectedIds: string[],
    availableGradeLevels: GradeLevel[],
): string[] {
    if (selectedIds.length === 0) {
        return selectedIds;
    }

    const validIds = new Set(availableGradeLevels.map((gradeLevel) => gradeLevel.id.toString()));

    return selectedIds.filter((id) => validIds.has(id));
}

export function CreateSchoolForm({
    form,
    indexUrl,
    organizationFields,
    types,
    academicPeriods,
    studentsGender,
    branchTypes,
    buildingTypes,
    educationalStages,
    gradeLevels,
    schoolPrivateType,
    schoolDualAcademicPeriod,
}: CreateSchoolFormProps) {
    const [selectedType, setSelectedType] = React.useState('');
    const [selectedAcademicPeriod, setSelectedAcademicPeriod] = React.useState('');
    const [isSameSchoolValue, setIsSameSchoolValue] = React.useState<'yes' | 'no'>('no');
    const deferredIsSameSchoolValue = React.useDeferredValue(isSameSchoolValue);
    const [selectedStages, setSelectedStages] = React.useState<string[]>([]);
    const [selectedStagesMorning, setSelectedStagesMorning] = React.useState<string[]>([]);
    const [selectedStagesEvening, setSelectedStagesEvening] = React.useState<string[]>([]);
    const [selectedGradeLevels, setSelectedGradeLevels] = React.useState<string[]>([]);
    const [selectedGradeLevelsMorning, setSelectedGradeLevelsMorning] = React.useState<string[]>([]);
    const [selectedGradeLevelsEvening, setSelectedGradeLevelsEvening] = React.useState<string[]>([]);

    const isPrivate = selectedType === schoolPrivateType;
    const isDualPeriod = selectedAcademicPeriod === schoolDualAcademicPeriod;

    const availableGradeLevels = React.useMemo(
        () => filterGradeLevelsByStages(gradeLevels, selectedStages),
        [gradeLevels, selectedStages],
    );

    const availableGradeLevelsMorning = React.useMemo(
        () => filterGradeLevelsByStages(gradeLevels, selectedStagesMorning),
        [gradeLevels, selectedStagesMorning],
    );

    const availableGradeLevelsEvening = React.useMemo(
        () => filterGradeLevelsByStages(gradeLevels, selectedStagesEvening),
        [gradeLevels, selectedStagesEvening],
    );

    const handleStagesChange = (stages: string[]) => {
        setSelectedStages(stages);
        setSelectedGradeLevels((current) =>
            pruneGradeLevelSelections(current, filterGradeLevelsByStages(gradeLevels, stages)),
        );
    };

    const handleStagesMorningChange = (stages: string[]) => {
        setSelectedStagesMorning(stages);
        setSelectedGradeLevelsMorning((current) =>
            pruneGradeLevelSelections(current, filterGradeLevelsByStages(gradeLevels, stages)),
        );
    };

    const handleStagesEveningChange = (stages: string[]) => {
        setSelectedStagesEvening(stages);
        setSelectedGradeLevelsEvening((current) =>
            pruneGradeLevelSelections(current, filterGradeLevelsByStages(gradeLevels, stages)),
        );
    };

    return (
        <MainContainer>
            <Form<CreateSchoolFormData>
                {...form}
                disableWhileProcessing
            >
                {({ processing, errors }) => (
                    <FormLayout>
                        <ValidationErrors errors={errors} />

                        {isDualPeriod ? (
                            <>
                                <input type="hidden" name="is_same_school" value={isSameSchoolValue === "yes" ? "1" : "0"} />
                                <input type="hidden" name="educational_stages_morning" value={JSON.stringify(selectedStagesMorning)} />
                                <input type="hidden" name="educational_stages_evening" value={JSON.stringify(selectedStagesEvening)} />
                                <input type="hidden" name="grade_levels_morning" value={JSON.stringify(selectedGradeLevelsMorning)} />
                                <input type="hidden" name="grade_levels_evening" value={JSON.stringify(selectedGradeLevelsEvening)} />
                            </>
                        ) : (
                            <>
                                <input type="hidden" name="educational_stages" value={JSON.stringify(selectedStages)} />
                                <input type="hidden" name="grade_levels" value={JSON.stringify(selectedGradeLevels)} />
                            </>
                        )}

                        <section>
                            <Card>
                                <CardHeader className="border-b">
                                    <CardTitle>إضافة مدرسة جديدة</CardTitle>
                                    <CardDescription>
                                        <RequiredFieldsNote />
                                    </CardDescription>
                                </CardHeader>

                                <CardFormContent>
                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        {organizationFields?.(errors)}

                                        <Field>
                                            <Label
                                                htmlFor="type"
                                                hasError={!!errors.type}
                                                required
                                            >
                                                نوع المدرسة
                                            </Label>

                                            <Select
                                                name="type"
                                                value={selectedType}
                                                onValueChange={setSelectedType}
                                            >
                                                <SelectTrigger
                                                    id="type"
                                                    hasError={!!errors.type}
                                                >
                                                    <SelectValue placeholder="اختر نوع المدرسة" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectGroup>
                                                        {types.map((type) => (
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

                                            <InputError message={errors.type} />
                                        </Field>

                                        <Field>
                                            <Label
                                                htmlFor="academic_period"
                                                hasError={!!errors.academic_period}
                                                required
                                            >
                                                الفترة الدراسية
                                            </Label>

                                            <Select
                                                name="academic_period"
                                                value={selectedAcademicPeriod}
                                                onValueChange={setSelectedAcademicPeriod}
                                            >
                                                <SelectTrigger
                                                    id="academic_period"
                                                    hasError={!!errors.academic_period}
                                                >
                                                    <SelectValue placeholder="اختر الفترة الدراسية" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectGroup>
                                                        {academicPeriods.map((period) => (
                                                            <SelectItem
                                                                key={period.id}
                                                                value={period.id}
                                                            >
                                                                {period.name}
                                                            </SelectItem>
                                                        ))}
                                                    </SelectGroup>
                                                </SelectContent>
                                            </Select>

                                            <InputError message={errors.academic_period} />
                                        </Field>

                                        {isPrivate && (
                                            <>
                                                <Separator className="col-span-full" />

                                                <Field className="col-span-full">
                                                    <Label
                                                        htmlFor="educational_company_name"
                                                        hasError={!!errors.educational_company_name}
                                                        required
                                                    >
                                                        اسم الشركة التعليمية
                                                    </Label>

                                                    <Input
                                                        id="educational_company_name"
                                                        type="text"
                                                        name="educational_company_name"
                                                        hasError={!!errors.educational_company_name}
                                                        autoComplete="off"
                                                        required
                                                    />

                                                    <InputError message={errors.educational_company_name} />
                                                </Field>

                                                <Field>
                                                    <Label
                                                        htmlFor="branch_type"
                                                        hasError={!!errors.branch_type}
                                                        required
                                                    >
                                                        فرع المدرسة
                                                    </Label>

                                                    <Select name="branch_type">
                                                        <SelectTrigger
                                                            id="branch_type"
                                                            hasError={!!errors.branch_type}
                                                        >
                                                            <SelectValue placeholder="اختر فرع المدرسة" />
                                                        </SelectTrigger>
                                                        <SelectContent>
                                                            <SelectGroup>
                                                                {branchTypes.map((branchType) => (
                                                                    <SelectItem
                                                                        key={branchType.id}
                                                                        value={branchType.id}
                                                                    >
                                                                        {branchType.name}
                                                                    </SelectItem>
                                                                ))}
                                                            </SelectGroup>
                                                        </SelectContent>
                                                    </Select>

                                                    <InputError message={errors.branch_type} />
                                                </Field>

                                                <Field>
                                                    <Label
                                                        htmlFor="building_type"
                                                        hasError={!!errors.building_type}
                                                        required
                                                    >
                                                        نوع المبنى
                                                    </Label>

                                                    <Select name="building_type">
                                                        <SelectTrigger
                                                            id="building_type"
                                                            hasError={!!errors.building_type}
                                                        >
                                                            <SelectValue placeholder="اختر نوع المبنى" />
                                                        </SelectTrigger>
                                                        <SelectContent>
                                                            <SelectGroup>
                                                                {buildingTypes.map((buildingType) => (
                                                                    <SelectItem
                                                                        key={buildingType.id}
                                                                        value={buildingType.id}
                                                                    >
                                                                        {buildingType.name}
                                                                    </SelectItem>
                                                                ))}
                                                            </SelectGroup>
                                                        </SelectContent>
                                                    </Select>

                                                    <InputError message={errors.building_type} />
                                                </Field>
                                            </>
                                        )}

                                        <Separator className="col-span-full" />

                                        {!isDualPeriod ? (
                                            <>
                                                <Field>
                                                    <Label
                                                        htmlFor="name"
                                                        hasError={!!errors.name}
                                                        required
                                                    >
                                                        اسم المدرسة
                                                    </Label>

                                                    <Input
                                                        id="name"
                                                        type="text"
                                                        name="name"
                                                        hasError={!!errors.name}
                                                        autoComplete="off"
                                                        required
                                                    />

                                                    <InputError message={errors.name} />
                                                </Field>

                                                <Field>
                                                    <Label
                                                        htmlFor="students_gender"
                                                        hasError={!!errors.students_gender}
                                                    >
                                                        جنس الطلاب الدارسين بالمدرسة
                                                    </Label>

                                                    <Select name="students_gender">
                                                        <SelectTrigger
                                                            id="students_gender"
                                                            hasError={!!errors.students_gender}
                                                        >
                                                            <SelectValue placeholder="اختر جنس الطلاب الدارسين بالمدرسة" />
                                                        </SelectTrigger>
                                                        <SelectContent>
                                                            <SelectGroup>
                                                                {studentsGender.map((gender) => (
                                                                    <SelectItem
                                                                        key={gender.id}
                                                                        value={gender.id}
                                                                    >
                                                                        {gender.name}
                                                                    </SelectItem>
                                                                ))}
                                                            </SelectGroup>
                                                        </SelectContent>
                                                    </Select>

                                                    <InputError message={errors.students_gender} />
                                                </Field>

                                                <Field className="col-span-full">
                                                    <Label
                                                        htmlFor="educational_stages"
                                                        hasError={!!errors.educational_stages}
                                                        required
                                                    >
                                                        المراحل الدراسية
                                                    </Label>

                                                    <MultiSelect
                                                        id="educational_stages"
                                                        options={educationalStages}
                                                        defaultValue={selectedStages}
                                                        onValueChange={handleStagesChange}
                                                        placeholder="اختر المراحل الدراسية"
                                                        aria-invalid={!!errors.educational_stages}
                                                    />

                                                    <InputError message={errors.educational_stages} />
                                                </Field>

                                                <Field className="col-span-full">
                                                    <Label
                                                        htmlFor="grade_levels"
                                                        hasError={!!errors.grade_levels}
                                                    >
                                                        الصفوف الدراسية
                                                    </Label>

                                                    <MultiSelect
                                                        id="grade_levels"
                                                        options={availableGradeLevels}
                                                        defaultValue={selectedGradeLevels}
                                                        onValueChange={setSelectedGradeLevels}
                                                        placeholder="اختر الصفوف الدراسية"
                                                        emptyPlaceholder="يرجى اختيار مرحلة دراسية واحدة أو أكثر أولاً"
                                                        disabled={selectedStages.length === 0}
                                                        aria-invalid={!!errors.grade_levels}
                                                    />

                                                    <InputError message={errors.grade_levels} />
                                                </Field>
                                            </>
                                        ) : (
                                            <>
                                                <Field className="col-span-full">
                                                    <Label hasError={!!errors.is_same_school} required>
                                                        هل الفترتان الصباحية والمسائية لنفس المدرسة وبنفس الاسم؟
                                                    </Label>

                                                    <RadioGroup
                                                        className="grid-cols-2"
                                                        value={isSameSchoolValue}
                                                        onValueChange={(value) => setIsSameSchoolValue(value as 'yes' | 'no')}
                                                    >
                                                        <RadioGroupItem value="yes">
                                                            نعم، باسم واحد
                                                        </RadioGroupItem>
                                                        <RadioGroupItem value="no">
                                                            لا، باسمين مختلفين
                                                        </RadioGroupItem>
                                                    </RadioGroup>

                                                    <InputError message={errors.is_same_school} />
                                                </Field>

                                                {deferredIsSameSchoolValue === "yes" ? (
                                                    <Field className="col-span-full">
                                                        <Label
                                                            htmlFor="name"
                                                            hasError={!!errors.name}
                                                            required
                                                        >
                                                            اسم المدرسة
                                                        </Label>

                                                        <Input
                                                            id="name"
                                                            type="text"
                                                            name="name"
                                                            hasError={!!errors.name}
                                                            autoComplete="off"
                                                            required
                                                        />

                                                        <InputError message={errors.name} />
                                                    </Field>
                                                ) : (
                                                    <>
                                                        <Field>
                                                            <Label
                                                                htmlFor="name_morning"
                                                                hasError={!!errors.name_morning}
                                                                required
                                                            >
                                                                <span>اسم المدرسة</span>
                                                                <span className="text-muted-foreground ms-1.5">( الفترة الصباحية )</span>
                                                            </Label>

                                                            <Input
                                                                id="name_morning"
                                                                type="text"
                                                                name="name_morning"
                                                                hasError={!!errors.name_morning}
                                                                autoComplete="off"
                                                                required
                                                            />

                                                            <InputError message={errors.name_morning} />
                                                        </Field>

                                                        <Field>
                                                            <Label
                                                                htmlFor="name_evening"
                                                                hasError={!!errors.name_evening}
                                                                required
                                                            >
                                                                <span>اسم المدرسة</span>
                                                                <span className="text-muted-foreground ms-1.5">( الفترة المسائية )</span>
                                                            </Label>

                                                            <Input
                                                                id="name_evening"
                                                                type="text"
                                                                name="name_evening"
                                                                hasError={!!errors.name_evening}
                                                                autoComplete="off"
                                                                required
                                                            />

                                                            <InputError message={errors.name_evening} />
                                                        </Field>
                                                    </>
                                                )}

                                                <Field>
                                                    <Label
                                                        htmlFor="students_gender_morning"
                                                        hasError={!!errors.students_gender_morning}
                                                    >
                                                        <span>جنس الطلاب الدارسين بالمدرسة</span>
                                                        <span className="text-muted-foreground ms-1.5">( الفترة الصباحية )</span>
                                                    </Label>

                                                    <Select name="students_gender_morning">
                                                        <SelectTrigger
                                                            id="students_gender_morning"
                                                            hasError={!!errors.students_gender_morning}
                                                        >
                                                            <SelectValue placeholder="اختر جنس الطلاب الدارسين بالمدرسة" />
                                                        </SelectTrigger>
                                                        <SelectContent>
                                                            <SelectGroup>
                                                                {studentsGender.map((gender) => (
                                                                    <SelectItem
                                                                        key={gender.id}
                                                                        value={gender.id}
                                                                    >
                                                                        {gender.name}
                                                                    </SelectItem>
                                                                ))}
                                                            </SelectGroup>
                                                        </SelectContent>
                                                    </Select>

                                                    <InputError message={errors.students_gender_morning} />
                                                </Field>

                                                <Field>
                                                    <Label
                                                        htmlFor="students_gender_evening"
                                                        hasError={!!errors.students_gender_evening}
                                                    >
                                                        <span>جنس الطلاب الدارسين بالمدرسة</span>
                                                        <span className="text-muted-foreground ms-1.5">( الفترة المسائية )</span>
                                                    </Label>

                                                    <Select name="students_gender_evening">
                                                        <SelectTrigger
                                                            id="students_gender_evening"
                                                            hasError={!!errors.students_gender_evening}
                                                        >
                                                            <SelectValue placeholder="اختر جنس الطلاب الدارسين بالمدرسة" />
                                                        </SelectTrigger>
                                                        <SelectContent>
                                                            <SelectGroup>
                                                                {studentsGender.map((gender) => (
                                                                    <SelectItem
                                                                        key={gender.id}
                                                                        value={gender.id}
                                                                    >
                                                                        {gender.name}
                                                                    </SelectItem>
                                                                ))}
                                                            </SelectGroup>
                                                        </SelectContent>
                                                    </Select>

                                                    <InputError message={errors.students_gender_evening} />
                                                </Field>

                                                <Field>
                                                    <Label
                                                        htmlFor="educational_stages_morning"
                                                        hasError={!!errors.educational_stages_morning}
                                                        required
                                                    >
                                                        <span>المراحل الدراسية</span>
                                                        <span className="text-muted-foreground ms-1.5">( الفترة الصباحية )</span>
                                                    </Label>

                                                    <MultiSelect
                                                        id="educational_stages_morning"
                                                        options={educationalStages}
                                                        defaultValue={selectedStagesMorning}
                                                        onValueChange={handleStagesMorningChange}
                                                        placeholder="اختر المراحل الدراسية"
                                                        aria-invalid={!!errors.educational_stages_morning}
                                                    />

                                                    <InputError message={errors.educational_stages_morning} />
                                                </Field>

                                                <Field>
                                                    <Label
                                                        htmlFor="educational_stages_evening"
                                                        hasError={!!errors.educational_stages_evening}
                                                        required
                                                    >
                                                        <span>المراحل الدراسية</span>
                                                        <span className="text-muted-foreground ms-1.5">( الفترة المسائية )</span>
                                                    </Label>

                                                    <MultiSelect
                                                        id="educational_stages_evening"
                                                        options={educationalStages}
                                                        defaultValue={selectedStagesEvening}
                                                        onValueChange={handleStagesEveningChange}
                                                        placeholder="اختر المراحل الدراسية"
                                                        aria-invalid={!!errors.educational_stages_evening}
                                                    />

                                                    <InputError message={errors.educational_stages_evening} />
                                                </Field>

                                                <Field>
                                                    <Label
                                                        htmlFor="grade_levels_morning"
                                                        hasError={!!errors.grade_levels_morning}
                                                    >
                                                        <span>الصفوف الدراسية</span>
                                                        <span className="text-muted-foreground ms-1.5">( الفترة الصباحية )</span>
                                                    </Label>

                                                    <MultiSelect
                                                        id="grade_levels_morning"
                                                        options={availableGradeLevelsMorning}
                                                        defaultValue={selectedGradeLevelsMorning}
                                                        onValueChange={setSelectedGradeLevelsMorning}
                                                        placeholder="اختر الصفوف الدراسية"
                                                        emptyPlaceholder="يرجى اختيار مرحلة دراسية واحدة أو أكثر أولاً"
                                                        disabled={selectedStagesMorning.length === 0}
                                                        aria-invalid={!!errors.grade_levels_morning}
                                                    />

                                                    <InputError message={errors.grade_levels_morning} />
                                                </Field>

                                                <Field>
                                                    <Label
                                                        htmlFor="grade_levels_evening"
                                                        hasError={!!errors.grade_levels_evening}
                                                    >
                                                        <span>الصفوف الدراسية</span>
                                                        <span className="text-muted-foreground ms-1.5">( الفترة المسائية )</span>
                                                    </Label>

                                                    <MultiSelect
                                                        id="grade_levels_evening"
                                                        options={availableGradeLevelsEvening}
                                                        defaultValue={selectedGradeLevelsEvening}
                                                        onValueChange={setSelectedGradeLevelsEvening}
                                                        placeholder="اختر الصفوف الدراسية"
                                                        emptyPlaceholder="يرجى اختيار مرحلة دراسية واحدة أو أكثر أولاً"
                                                        disabled={selectedStagesEvening.length === 0}
                                                        aria-invalid={!!errors.grade_levels_evening}
                                                    />

                                                    <InputError message={errors.grade_levels_evening} />
                                                </Field>
                                            </>
                                        )}
                                    </div>
                                </CardFormContent>
                                <CardFooter className="justify-end gap-x-4 border-t">
                                    <Button
                                        variant="outline"
                                        className="flex items-center gap-x-2"
                                        asChild
                                    >
                                        <Link href={indexUrl}>
                                            <ReplyIcon />
                                            <span>إلغاء الأمر</span>
                                        </Link>
                                    </Button>

                                    <CreateButton
                                        processing={processing}
                                    />
                                </CardFooter>
                            </Card>
                        </section>
                    </FormLayout>
                )}
            </Form>
        </MainContainer>
    );
}
