import React from 'react'

import { Form, Head, Link } from "@inertiajs/react";

import { decimalInputConstraints, libyanNationalIdInputConstraints, passportNumberInputConstraints } from "@/lib/input-constraints";

import type { Nationality, Student } from "@/types";

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
import { UpdateButton } from "@/components/ui/actions/submit-button";

import { ReplyIcon } from "lucide-react";

import { index, show, edit, update } from "@/routes/school/students";

type PageProps = {
    student: Student;
    nationalities: Nationality[];
    libyanNationalityId: number;
};

export default function Edit({ student, nationalities, libyanNationalityId }: PageProps) {
    const [selectedNationalityId, setSelectedNationalityId] = React.useState<string>(student.nationality_id.toString());

    const isLibyanNationalitySelected = selectedNationalityId === libyanNationalityId.toString();

    return (
        <>
            <Head title="تعديل بيانات الطالب" />

            <MainContainer showAcademicYearNotice>
                <Form
                    {...update.form({ student: student })}
                    disableWhileProcessing
                >
                    {({ processing, errors }) => (
                        <FormLayout>
                            <ValidationErrors errors={errors} />

                            <section>
                                <Card>
                                    <CardHeader className="border-b">
                                        <CardTitle>تعديل بيانات الطالب</CardTitle>
                                        <CardDescription>
                                            <RequiredFieldsNote />
                                        </CardDescription>
                                    </CardHeader>

                                    <CardFormContent>
                                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                                                    defaultValue={student.first_name}
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
                                                    defaultValue={student.father_name}
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
                                                    defaultValue={student.grandfather_name}
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
                                                    defaultValue={student.surname}
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
                                                    defaultValue={student.mother_name}
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
                                                    defaultValue={student.nationality_id.toString() || libyanNationalityId.toString() || undefined}
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
                                                    defaultValue={student.passport_number}
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
                                                    defaultValue={student.gender.id}
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
                                                    date={student.date_of_birth}
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
                                                            defaultValue={student.national_id}
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
                                                            defaultValue={student.family_registration_number}
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
                                            <Link href={show.url({ student: student })}>
                                                <ReplyIcon />
                                                <span>إلغاء الأمر</span>
                                            </Link>
                                        </Button>

                                        <UpdateButton
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

Edit.layout = (props: PageProps) => ({
    breadcrumbs: [
        {
            title: 'الطلاب',
            href: index.url(),
        },
        {
            title: 'عرض بيانات الطالب',
            href: show.url({ student: props.student }),
        },
        {
            title: 'تعديل بيانات الطالب',
            href: edit.url({ student: props.student }),
        },
    ],
});
