import React from 'react'

import { Form, Head, Link } from "@inertiajs/react";

import { decimalInputConstraints, libyanNationalIdInputConstraints, passportNumberInputConstraints } from "@/lib/input-constraints";

import type { Enum, GradeLevel, Nationality } from "@/types";

import MainContainer from "@/components/ui/structure/main-container";
import { Card, CardDescription, CardFooter, CardFormContent, CardHeader, CardTitle } from "@/components/ui/structure/card";
import { FormLayout } from "@/components/ui/structure/form-layout";

import RequiredFieldsNote from "@/components/ui/display/required-fields-note";

import Field from "@/components/ui/controls/field";
import { Label } from "@/components/ui/controls/label";
import { Input } from "@/components/ui/controls/input";
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/controls/select";
import DatePicker from "@/components/ui/controls/date-picker";
import InputError from "@/components/ui/controls/input-error";

import ValidationErrors from "@/components/ui/alerts/validation-errors";

import { Button } from "@/components/ui/actions/button";
import { CreateButton } from "@/components/ui/actions/submit-button";

import { ReplyIcon } from "lucide-react";

import { index, create, store } from "@/routes/school/students";

type PageProps = {
    gradeLevels: GradeLevel[];
    registrationStatuses: Enum[];
    nationalities: Nationality[];
    libyanNationalityId: number;
};

export default function Create({ gradeLevels, registrationStatuses, nationalities, libyanNationalityId }: PageProps) {
    const [selectedNationalityId, setSelectedNationalityId] = React.useState<string>(libyanNationalityId.toString());

    const isLibyanNationalitySelected = selectedNationalityId === libyanNationalityId.toString();

    return (
        <>
            <Head title="إضافة طالب جديد" />

            <MainContainer showAcademicYearNotice>
                <Form
                    {...store.form()}
                    disableWhileProcessing
                >
                    {({ processing, errors }) => (
                        <FormLayout>
                            <ValidationErrors errors={errors} />

                            <section>
                                <Card>
                                    <CardHeader className="border-b">
                                        <CardTitle>إضافة طالب جديد</CardTitle>
                                        <CardDescription>
                                            <RequiredFieldsNote />
                                        </CardDescription>
                                    </CardHeader>

                                    <CardFormContent>
                                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <Field>
                                                <Label
                                                    htmlFor="grade_level_id"
                                                    hasError={!!errors.grade_level_id}
                                                    required
                                                >
                                                    الصف الدراسي
                                                </Label>

                                                <Select
                                                    name="grade_level_id"
                                                    required
                                                >
                                                    <SelectTrigger
                                                        id="grade_level_id"
                                                        hasError={!!errors.grade_level_id}
                                                    >
                                                        <SelectValue placeholder="اختر الصف الدراسي" />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectGroup>
                                                            {gradeLevels.map((gradeLevel) => (
                                                                <SelectItem
                                                                    key={gradeLevel.id}
                                                                    value={gradeLevel.id.toString()}
                                                                >
                                                                    {gradeLevel.name}
                                                                </SelectItem>
                                                            ))}
                                                        </SelectGroup>
                                                    </SelectContent>
                                                </Select>

                                                <InputError message={errors.grade_level_id} />
                                            </Field>

                                            <Field>
                                                <Label
                                                    htmlFor="registration_status"
                                                    hasError={!!errors.registration_status}
                                                    required
                                                >
                                                    صفة القيد
                                                </Label>

                                                <Select
                                                    name="registration_status"
                                                    required
                                                >
                                                    <SelectTrigger
                                                        id="registration_status"
                                                        hasError={!!errors.registration_status}
                                                    >
                                                        <SelectValue placeholder="اختر صفة القيد" />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectGroup>
                                                            {registrationStatuses.map((status) => (
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

                                                <InputError message={errors.registration_status} />
                                            </Field>

                                            <Field>
                                                <Label
                                                    htmlFor="student_first_name"
                                                    hasError={!!errors.student_first_name}
                                                    required
                                                >
                                                    الاسم الأول للطالب
                                                </Label>

                                                <Input
                                                    id="student_first_name"
                                                    type="text"
                                                    name="student_first_name"
                                                    hasError={!!errors.student_first_name}
                                                    autoComplete="off"
                                                    required
                                                />

                                                <InputError message={errors.student_first_name} />
                                            </Field>

                                            <Field>
                                                <Label
                                                    htmlFor="student_father_name"
                                                    hasError={!!errors.student_father_name}
                                                    required
                                                >
                                                    الاسم الأب للطالب
                                                </Label>

                                                <Input
                                                    id="student_father_name"
                                                    type="text"
                                                    name="student_father_name"
                                                    hasError={!!errors.student_father_name}
                                                    autoComplete="off"
                                                    required
                                                />

                                                <InputError message={errors.student_father_name} />
                                            </Field>

                                            <Field>
                                                <Label
                                                    htmlFor="student_grandfather_name"
                                                    hasError={!!errors.student_grandfather_name}
                                                    required
                                                >
                                                    الاسم الجد للطالب
                                                </Label>

                                                <Input
                                                    id="student_grandfather_name"
                                                    type="text"
                                                    name="student_grandfather_name"
                                                    hasError={!!errors.student_grandfather_name}
                                                    autoComplete="off"
                                                    required
                                                />

                                                <InputError message={errors.student_grandfather_name} />
                                            </Field>

                                            <Field>
                                                <Label
                                                    htmlFor="student_surname"
                                                    hasError={!!errors.student_surname}
                                                    required
                                                >
                                                    اللقب للطالب
                                                </Label>

                                                <Input
                                                    id="student_surname"
                                                    type="text"
                                                    name="student_surname"
                                                    hasError={!!errors.student_surname}
                                                    autoComplete="off"
                                                    required
                                                />

                                                <InputError message={errors.student_surname} />
                                            </Field>

                                            <Field className="col-span-full">
                                                <Label
                                                    htmlFor="mother_name"
                                                    hasError={!!errors.mother_name}
                                                    required
                                                >
                                                    اسم الأم
                                                </Label>

                                                <Input
                                                    id="mother_name"
                                                    type="text"
                                                    name="mother_name"
                                                    hasError={!!errors.mother_name}
                                                    autoComplete="off"
                                                    required
                                                />

                                                <InputError message={errors.mother_name} />
                                            </Field>

                                            <Field>
                                                <Label
                                                    htmlFor="nationality_id"
                                                    hasError={!!errors.nationality_id}
                                                    required
                                                >
                                                    الجنسية
                                                </Label>

                                                <Select
                                                    name="nationality_id"
                                                    defaultValue={libyanNationalityId.toString() || undefined}
                                                    onValueChange={setSelectedNationalityId}
                                                    required
                                                >
                                                    <SelectTrigger
                                                        id="nationality_id"
                                                        hasError={!!errors.nationality_id}
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

                                                <InputError message={errors.nationality_id} />
                                            </Field>

                                            <Field>
                                                <Label
                                                    htmlFor="passport_number"
                                                    hasError={!!errors.passport_number}
                                                >
                                                    رقم جواز السفر
                                                </Label>

                                                <Input
                                                    id="passport_number"
                                                    type="text"
                                                    name="passport_number"
                                                    className="font-mono"
                                                    hasError={!!errors.passport_number}
                                                    {...passportNumberInputConstraints()}
                                                />

                                                <InputError message={errors.passport_number} />
                                            </Field>

                                            <Field>
                                                <Label
                                                    htmlFor="gender"
                                                    hasError={!!errors.gender}
                                                    required
                                                >
                                                    الجنس
                                                </Label>

                                                <Select
                                                    name="gender"
                                                    required
                                                >
                                                    <SelectTrigger
                                                        id="gender"
                                                        hasError={!!errors.gender}
                                                    >
                                                        <SelectValue placeholder="اختر الجنس" />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectGroup>
                                                            <SelectItem
                                                                key="male"
                                                                value="male"
                                                            >
                                                                ذكر
                                                            </SelectItem>
                                                            <SelectItem
                                                                key="female"
                                                                value="female"
                                                            >
                                                                أنثى
                                                            </SelectItem>
                                                        </SelectGroup>
                                                    </SelectContent>
                                                </Select>

                                                <InputError message={errors.gender} />
                                            </Field>


                                            <Field>
                                                <Label
                                                    htmlFor="date_of_birth"
                                                    hasError={!!errors.date_of_birth}
                                                    required
                                                >
                                                    تاريخ الميلاد
                                                </Label>

                                                <DatePicker
                                                    id="date_of_birth"
                                                    name="date_of_birth"
                                                    hasError={!!errors.date_of_birth}
                                                    placeholder="تاريخ الميلاد"
                                                    required
                                                // TODO: Add min and max dates
                                                />

                                                <InputError message={errors.date_of_birth} />
                                            </Field>

                                            {isLibyanNationalitySelected && (
                                                <>

                                                    <Field>
                                                        <Label
                                                            htmlFor="national_id"
                                                            hasError={!!errors.national_id}
                                                            required={isLibyanNationalitySelected}
                                                        >
                                                            الرقم الوطني
                                                        </Label>

                                                        <Input
                                                            id="national_id"
                                                            type="text"
                                                            name="national_id"
                                                            className="font-mono"
                                                            hasError={!!errors.national_id}
                                                            required={isLibyanNationalitySelected}
                                                            {...libyanNationalIdInputConstraints()}
                                                        />

                                                        <InputError message={errors.national_id} />
                                                    </Field>

                                                    <Field>
                                                        <Label
                                                            htmlFor="passport_number"
                                                            hasError={!!errors.passport_number}
                                                            required={isLibyanNationalitySelected}
                                                        >
                                                            رقم القيد
                                                        </Label>

                                                        <Input
                                                            id="family_registration_number"
                                                            type="text"
                                                            name="family_registration_number"
                                                            className="font-mono"
                                                            hasError={!!errors.family_registration_number}
                                                            required={isLibyanNationalitySelected}
                                                            {...decimalInputConstraints({
                                                                allowDecimal: false,
                                                                allowNegative: false,
                                                                min: 1,
                                                            })}
                                                        />

                                                        <InputError message={errors.family_registration_number} />
                                                    </Field>
                                                </>
                                            )}
                                        </div>
                                    </CardFormContent>

                                    <CardFooter className="justify-end gap-x-4 border-t">
                                        <Button variant="outline" className="flex items-center gap-x-2" asChild>
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
    );
}

Create.layout = () => ({
    breadcrumbs: [
        {
            title: 'الطلاب',
            href: index.url(),
        },
        {
            title: 'إضافة طالب جديد',
            href: create.url(),
        },
    ],
});
