import React, { useMemo, useState } from 'react'

import { Form, Head, Link } from "@inertiajs/react";

import type { EducationMonitor, EducationServicesOffice, Enum } from "@/types";

import MainContainer from "@/components/ui/structure/main-container";
import { Card, CardDescription, CardFooter, CardFormContent, CardHeader, CardTitle } from "@/components/ui/structure/card";
import { FormLayout } from "@/components/ui/structure/form-layout";
import { Separator } from "@/components/ui/structure/separator";

import RequiredFieldsNote from "@/components/ui/display/required-fields-note";

import Field from "@/components/ui/controls/field";
import { Label } from "@/components/ui/controls/label";
import { Input } from "@/components/ui/controls/input";
import { EmptyOptionsInput } from "@/components/ui/controls/empty-options-input";
import { Checkbox } from "@/components/ui/controls/checkbox";
import { MultiSelect } from "@/components/ui/controls/multi-select";
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/controls/select";
import InputError from "@/components/ui/controls/input-error";

import ValidationErrors from "@/components/ui/alerts/validation-errors";

import { Button } from "@/components/ui/actions/button";
import { CreateButton } from "@/components/ui/actions/submit-button";

import { ReplyIcon } from "lucide-react";

import { create, index, store } from "@/routes/administration/schools";

type MonitorWithOffices = Pick<EducationMonitor, "id" | "name"> & {
    offices: Pick<EducationServicesOffice, "id" | "name">[];
};

type PageProps = {
    monitors: MonitorWithOffices[];
    types: Enum[];
    academicPeriods: Enum[];
    studentsGender: Enum[];
    branchTypes: Enum[];
    buildingTypes: Enum[];
    educationalStages: Enum[];
    schoolPrivateType: string;
    schoolDualAcademicPeriod: string;
}

export default function Create({
    monitors,
    types,
    academicPeriods,
    studentsGender,
    branchTypes,
    buildingTypes,
    educationalStages,
    schoolPrivateType,
    schoolDualAcademicPeriod,
}: PageProps) {
    const [selectedMonitorId, setSelectedMonitorId] = useState<string>();
    const [selectedOfficeId, setSelectedOfficeId] = useState<string>();
    const [selectedType, setSelectedType] = useState<string>();
    const [selectedAcademicPeriod, setSelectedAcademicPeriod] = useState<string>();
    const [sameSchoolName, setSameSchoolName] = useState<boolean>(false);
    const [selectedStages, setSelectedStages] = useState<string[]>([]);
    const [selectedStagesMorning, setSelectedStagesMorning] = useState<string[]>([]);
    const [selectedStagesEvening, setSelectedStagesEvening] = useState<string[]>([]);

    const isPrivate = selectedType === schoolPrivateType;
    const isDualPeriod = selectedAcademicPeriod === schoolDualAcademicPeriod;

    const availableOffices = useMemo(() => {
        if (!selectedMonitorId) {
            return [];
        }

        return monitors.find((monitor) => monitor.id.toString() === selectedMonitorId)?.offices ?? [];
    }, [monitors, selectedMonitorId]);

    const handleMonitorChange = (value: string) => {
        setSelectedMonitorId(value);
        setSelectedOfficeId(undefined);
    };

    return (
        <>
            <Head title="إضافة مدرسة جديدة" />

            <MainContainer>
                <Form
                    {...store.form()}
                    disableWhileProcessing
                >
                    {({ processing, errors }) => (
                        <FormLayout>
                            <ValidationErrors errors={errors} />

                            {isDualPeriod ? (
                                <>
                                    <input type="hidden" name="same_school_name" value={sameSchoolName ? "1" : "0"} />
                                    <input type="hidden" name="educational_stages_morning" value={JSON.stringify(selectedStagesMorning)} />
                                    <input type="hidden" name="educational_stages_evening" value={JSON.stringify(selectedStagesEvening)} />
                                </>
                            ) : (
                                <input type="hidden" name="educational_stages" value={JSON.stringify(selectedStages)} />
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
                                            <Field>
                                                <Label htmlFor="education_monitor_id" hasError={!!errors.education_monitor_id} required>
                                                    المُراقبة
                                                </Label>

                                                {monitors.length > 0 ? (
                                                    <Select
                                                        name="education_monitor_id"
                                                        value={selectedMonitorId}
                                                        onValueChange={handleMonitorChange}
                                                    >
                                                        <SelectTrigger id="education_monitor_id" hasError={!!errors.education_monitor_id}>
                                                            <SelectValue placeholder="اختر المُراقبة" />
                                                        </SelectTrigger>
                                                        <SelectContent>
                                                            <SelectGroup>
                                                                {monitors.map((monitor) => (
                                                                    <SelectItem key={monitor.id} value={monitor.id.toString()}>
                                                                        {monitor.name}
                                                                    </SelectItem>
                                                                ))}
                                                            </SelectGroup>
                                                        </SelectContent>
                                                    </Select>
                                                ) : (
                                                    <EmptyOptionsInput
                                                        id="education_monitor_id"
                                                        placeholder="لا توجد مُراقبات متاحة للاختيار"
                                                        aria-invalid={!!errors.education_monitor_id}
                                                    />
                                                )}

                                                <InputError message={errors.education_monitor_id} />
                                            </Field>

                                            <Field>
                                                <Label
                                                    htmlFor="education_services_office_id"
                                                    hasError={!!errors.education_services_office_id}
                                                    required={availableOffices.length > 0}
                                                >
                                                    مكتب الخدمات التعليمية
                                                </Label>

                                                {selectedMonitorId ? (
                                                    availableOffices.length > 0 ? (
                                                        <Select
                                                            name="education_services_office_id"
                                                            value={selectedOfficeId}
                                                            onValueChange={setSelectedOfficeId}
                                                        >
                                                            <SelectTrigger id="education_services_office_id" hasError={!!errors.education_services_office_id}>
                                                                <SelectValue placeholder="اختر مكتب الخدمات التعليمية" />
                                                            </SelectTrigger>
                                                            <SelectContent>
                                                                <SelectGroup>
                                                                    {availableOffices.map((office) => (
                                                                        <SelectItem key={office.id} value={office.id.toString()}>
                                                                            {office.name}
                                                                        </SelectItem>
                                                                    ))}
                                                                </SelectGroup>
                                                            </SelectContent>
                                                        </Select>
                                                    ) : (
                                                        <EmptyOptionsInput
                                                            id="education_services_office_id"
                                                            placeholder="لا توجد مكاتب خدمات تعليمية متاحة للاختيار"
                                                            aria-invalid={!!errors.education_services_office_id}
                                                        />
                                                    )
                                                ) : (
                                                    <EmptyOptionsInput
                                                        id="education_services_office_id"
                                                        placeholder="يرجى اختيار المُراقبة أولاً"
                                                        aria-invalid={!!errors.education_services_office_id}
                                                    />
                                                )}

                                                <InputError message={errors.education_services_office_id} />
                                            </Field>

                                            <Field>
                                                <Label htmlFor="type" hasError={!!errors.type} required>
                                                    نوع المدرسة
                                                </Label>
                                                <Select name="type" value={selectedType} onValueChange={setSelectedType}>
                                                    <SelectTrigger id="type" hasError={!!errors.type}>
                                                        <SelectValue placeholder="اختر نوع المدرسة" />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectGroup>
                                                            {types.map((type) => (
                                                                <SelectItem key={type.id} value={type.id}>
                                                                    {type.name}
                                                                </SelectItem>
                                                            ))}
                                                        </SelectGroup>
                                                    </SelectContent>
                                                </Select>
                                                <InputError message={errors.type} />
                                            </Field>

                                            <Field>
                                                <Label htmlFor="academic_period" hasError={!!errors.academic_period} required>
                                                    الفترة الدراسية
                                                </Label>
                                                <Select name="academic_period" value={selectedAcademicPeriod} onValueChange={setSelectedAcademicPeriod}>
                                                    <SelectTrigger id="academic_period" hasError={!!errors.academic_period}>
                                                        <SelectValue placeholder="اختر الفترة الدراسية" />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectGroup>
                                                            {academicPeriods.map((period) => (
                                                                <SelectItem key={period.id} value={period.id}>
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
                                                        <Label htmlFor="educational_company_name" hasError={!!errors.educational_company_name} required>
                                                            اسم الشركة التعليمية
                                                        </Label>
                                                        <Input
                                                            id="educational_company_name"
                                                            type="text"
                                                            name="educational_company_name"
                                                            hasErrors={!!errors.educational_company_name}
                                                            autoComplete="off"
                                                            required
                                                        />
                                                        <InputError message={errors.educational_company_name} />
                                                    </Field>

                                                    <Field>
                                                        <Label htmlFor="branch_type" hasError={!!errors.branch_type} required>
                                                            فرع المدرسة
                                                        </Label>
                                                        <Select name="branch_type">
                                                            <SelectTrigger id="branch_type" hasError={!!errors.branch_type}>
                                                                <SelectValue placeholder="اختر فرع المدرسة" />
                                                            </SelectTrigger>
                                                            <SelectContent>
                                                                <SelectGroup>
                                                                    {branchTypes.map((branchType) => (
                                                                        <SelectItem key={branchType.id} value={branchType.id}>
                                                                            {branchType.name}
                                                                        </SelectItem>
                                                                    ))}
                                                                </SelectGroup>
                                                            </SelectContent>
                                                        </Select>
                                                        <InputError message={errors.branch_type} />
                                                    </Field>

                                                    <Field>
                                                        <Label htmlFor="building_type" hasError={!!errors.building_type} required>
                                                            نوع المبنى
                                                        </Label>
                                                        <Select name="building_type">
                                                            <SelectTrigger id="building_type" hasError={!!errors.building_type}>
                                                                <SelectValue placeholder="اختر نوع المبنى" />
                                                            </SelectTrigger>
                                                            <SelectContent>
                                                                <SelectGroup>
                                                                    {buildingTypes.map((buildingType) => (
                                                                        <SelectItem key={buildingType.id} value={buildingType.id}>
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
                                                        <Label htmlFor="name" hasError={!!errors.name} required>
                                                            اسم المدرسة
                                                        </Label>
                                                        <Input
                                                            id="name"
                                                            type="text"
                                                            name="name"
                                                            hasErrors={!!errors.name}
                                                            autoComplete="off"
                                                            required
                                                        />
                                                        <InputError message={errors.name} />
                                                    </Field>

                                                    <Field>
                                                        <Label htmlFor="students_gender" hasError={!!errors.students_gender} required>
                                                            جنس الطلاب الدارسين بالمدرسة
                                                        </Label>
                                                        <Select name="students_gender">
                                                            <SelectTrigger id="students_gender" hasError={!!errors.students_gender}>
                                                                <SelectValue placeholder="اختر جنس الطلاب الدارسين بالمدرسة" />
                                                            </SelectTrigger>
                                                            <SelectContent>
                                                                <SelectGroup>
                                                                    {studentsGender.map((gender) => (
                                                                        <SelectItem key={gender.id} value={gender.id}>
                                                                            {gender.name}
                                                                        </SelectItem>
                                                                    ))}
                                                                </SelectGroup>
                                                            </SelectContent>
                                                        </Select>
                                                        <InputError message={errors.students_gender} />
                                                    </Field>

                                                    <Field className="col-span-full">
                                                        <Label htmlFor="educational_stages" hasError={!!errors.educational_stages} required>
                                                            المراحل الدراسية
                                                        </Label>
                                                        <MultiSelect
                                                            id="educational_stages"
                                                            options={educationalStages}
                                                            defaultValue={selectedStages}
                                                            onValueChange={setSelectedStages}
                                                            placeholder="اختر المراحل الدراسية"
                                                            aria-invalid={!!errors.educational_stages}
                                                        />
                                                        <InputError message={errors.educational_stages} />
                                                    </Field>
                                                </>
                                            ) : (
                                                <>
                                                    <Field className="col-span-full">
                                                        <div className="flex items-center gap-x-3">
                                                            <Checkbox
                                                                id="same_school_name"
                                                                checked={sameSchoolName}
                                                                onCheckedChange={(checked) => {
                                                                    setSameSchoolName(checked === true);
                                                                }}
                                                            />

                                                            <Label
                                                                htmlFor="same_school_name"
                                                                style={{ fontWeight: '500' }}
                                                            >
                                                                كلتا الفترتين تتبعان نفس المدرسة (استخدام نفس الاسم للفترتين)
                                                            </Label>
                                                        </div>

                                                        <InputError message={errors.same_school_name} />
                                                    </Field>

                                                    {sameSchoolName ? (
                                                        <Field className="col-span-full">
                                                            <Label htmlFor="name" hasError={!!errors.name} required>
                                                                اسم المدرسة
                                                            </Label>
                                                            <Input
                                                                id="name"
                                                                type="text"
                                                                name="name"
                                                                hasErrors={!!errors.name}
                                                                autoComplete="off"
                                                                required
                                                            />
                                                            <InputError message={errors.name} />
                                                        </Field>
                                                    ) : (
                                                        <>
                                                            <Field>
                                                                <Label htmlFor="name_morning" hasError={!!errors.name_morning} required>
                                                                    <span>اسم المدرسة</span>
                                                                    <span className="text-muted-foreground ms-1.5">( الفترة الصباحية )</span>
                                                                </Label>
                                                                <Input
                                                                    id="name_morning"
                                                                    type="text"
                                                                    name="name_morning"
                                                                    hasErrors={!!errors.name_morning}
                                                                    autoComplete="off"
                                                                    required
                                                                />
                                                                <InputError message={errors.name_morning} />
                                                            </Field>

                                                            <Field>
                                                                <Label htmlFor="name_evening" hasError={!!errors.name_evening} required>
                                                                    <span>اسم المدرسة</span>
                                                                    <span className="text-muted-foreground ms-1.5">( الفترة المسائية )</span>
                                                                </Label>
                                                                <Input
                                                                    id="name_evening"
                                                                    type="text"
                                                                    name="name_evening"
                                                                    hasErrors={!!errors.name_evening}
                                                                    autoComplete="off"
                                                                    required
                                                                />
                                                                <InputError message={errors.name_evening} />
                                                            </Field>
                                                        </>
                                                    )}

                                                    <Field>
                                                        <Label htmlFor="students_gender_morning" hasError={!!errors.students_gender_morning} required>
                                                            <span>جنس الطلاب الدارسين بالمدرسة</span>
                                                            <span className="text-muted-foreground ms-1.5">( الفترة الصباحية )</span>
                                                        </Label>
                                                        <Select name="students_gender_morning">
                                                            <SelectTrigger id="students_gender_morning" hasError={!!errors.students_gender_morning}>
                                                                <SelectValue placeholder="اختر جنس الطلاب الدارسين بالمدرسة" />
                                                            </SelectTrigger>
                                                            <SelectContent>
                                                                <SelectGroup>
                                                                    {studentsGender.map((gender) => (
                                                                        <SelectItem key={gender.id} value={gender.id}>
                                                                            {gender.name}
                                                                        </SelectItem>
                                                                    ))}
                                                                </SelectGroup>
                                                            </SelectContent>
                                                        </Select>
                                                        <InputError message={errors.students_gender_morning} />
                                                    </Field>

                                                    <Field>
                                                        <Label htmlFor="students_gender_evening" hasError={!!errors.students_gender_evening} required>
                                                            <span>جنس الطلاب الدارسين بالمدرسة</span>
                                                            <span className="text-muted-foreground ms-1.5">( الفترة المسائية )</span>
                                                        </Label>
                                                        <Select name="students_gender_evening">
                                                            <SelectTrigger id="students_gender_evening" hasError={!!errors.students_gender_evening}>
                                                                <SelectValue placeholder="اختر جنس الطلاب الدارسين بالمدرسة" />
                                                            </SelectTrigger>
                                                            <SelectContent>
                                                                <SelectGroup>
                                                                    {studentsGender.map((gender) => (
                                                                        <SelectItem key={gender.id} value={gender.id}>
                                                                            {gender.name}
                                                                        </SelectItem>
                                                                    ))}
                                                                </SelectGroup>
                                                            </SelectContent>
                                                        </Select>
                                                        <InputError message={errors.students_gender_evening} />
                                                    </Field>

                                                    <Field>
                                                        <Label htmlFor="educational_stages_morning" hasError={!!errors.educational_stages_morning} required>
                                                            <span>المراحل الدراسية</span>
                                                            <span className="text-muted-foreground ms-1.5">( الفترة الصباحية )</span>
                                                        </Label>
                                                        <MultiSelect
                                                            id="educational_stages_morning"
                                                            options={educationalStages}
                                                            defaultValue={selectedStagesMorning}
                                                            onValueChange={setSelectedStagesMorning}
                                                            placeholder="اختر المراحل الدراسية"
                                                            aria-invalid={!!errors.educational_stages_morning}
                                                        />
                                                        <InputError message={errors.educational_stages_morning} />
                                                    </Field>

                                                    <Field>
                                                        <Label htmlFor="educational_stages_evening" hasError={!!errors.educational_stages_evening} required>
                                                            <span>المراحل الدراسية</span>
                                                            <span className="text-muted-foreground ms-1.5">( الفترة المسائية )</span>
                                                        </Label>
                                                        <MultiSelect
                                                            id="educational_stages_evening"
                                                            options={educationalStages}
                                                            defaultValue={selectedStagesEvening}
                                                            onValueChange={setSelectedStagesEvening}
                                                            placeholder="اختر المراحل الدراسية"
                                                            aria-invalid={!!errors.educational_stages_evening}
                                                        />
                                                        <InputError message={errors.educational_stages_evening} />
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
                                            <Link href={index.url()}>
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
        </>
    )
}

Create.layout = () => ({
    breadcrumbs: [
        {
            title: 'المدارس',
            href: index.url(),
        },
        {
            title: 'إضافة مدرسة جديدة',
            href: create.url(),
        },
    ],
});
