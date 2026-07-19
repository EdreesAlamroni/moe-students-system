import React from 'react'

import { Form, Head, Link } from "@inertiajs/react";

import type { Enum, School } from "@/types";

import MainContainer from "@/components/ui/structure/main-container";
import { Card, CardDescription, CardFooter, CardFormContent, CardHeader, CardTitle } from "@/components/ui/structure/card";
import { FormLayout } from "@/components/ui/structure/form-layout";
import { Separator } from "@/components/ui/structure/separator";

import RequiredFieldsNote from "@/components/ui/display/required-fields-note";

import Field from "@/components/ui/controls/field";
import { Label } from "@/components/ui/controls/label";
import { Input } from "@/components/ui/controls/input";
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/controls/select";
import InputError from "@/components/ui/controls/input-error";

import ValidationErrors from "@/components/ui/alerts/validation-errors";

import { Button } from "@/components/ui/actions/button";
import { UpdateButton } from "@/components/ui/actions/submit-button";

import { ReplyIcon } from "lucide-react";

import { edit, index, show, update } from "@/routes/administration/schools";

type PageProps = {
    school: School;
    branchTypes: Enum[];
    buildingTypes: Enum[];
}

export default function Edit({ school, branchTypes, buildingTypes }: PageProps) {
    const isPrivate = school.is_private === true;

    return (
        <>
            <Head title="تعديل بيانات المدرسة" />

            <MainContainer>
                <Form
                    {...update.form({ school: school })}
                    disableWhileProcessing
                >
                    {({ processing, errors }) => (
                        <FormLayout>
                            <ValidationErrors errors={errors} />

                            <section>
                                <Card>
                                    <CardHeader className="border-b">
                                        <CardTitle>تعديل بيانات المدرسة</CardTitle>
                                        <CardDescription>
                                            <RequiredFieldsNote />
                                        </CardDescription>
                                    </CardHeader>

                                    <CardFormContent>
                                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <Field>
                                                <Label htmlFor="monitor_name">المُراقبة</Label>
                                                <Input
                                                    id="monitor_name"
                                                    type="text"
                                                    value={school.monitor?.name ?? ""}
                                                    disabled
                                                    readOnly
                                                />
                                            </Field>

                                            <Field>
                                                <Label htmlFor="serial_number">الرقم التسلسلي</Label>
                                                <Input
                                                    id="serial_number"
                                                    type="text"
                                                    value={school.serial_number}
                                                    className="font-mono"
                                                    disabled
                                                    readOnly
                                                />
                                            </Field>

                                            <Field className="col-span-full">
                                                <Label htmlFor="type_name">نوع المدرسة</Label>
                                                <Input
                                                    id="type_name"
                                                    type="text"
                                                    value={school.type?.name ?? ""}
                                                    disabled
                                                    readOnly
                                                />
                                            </Field>

                                            <Separator className="col-span-full" />

                                            <Field className="col-span-full">
                                                <Label htmlFor="name" hasError={!!errors.name} required>
                                                    اسم المدرسة
                                                </Label>
                                                <Input
                                                    id="name"
                                                    type="text"
                                                    name="name"
                                                    defaultValue={school.name}
                                                    hasError={!!errors.name}
                                                    autoComplete="off"
                                                    required
                                                />
                                                <InputError message={errors.name} />
                                            </Field>

                                            {isPrivate && (
                                                <>
                                                    <Field className="col-span-full">
                                                        <Label htmlFor="educational_company_name" hasError={!!errors.educational_company_name} required>
                                                            اسم الشركة التعليمية
                                                        </Label>
                                                        <Input
                                                            id="educational_company_name"
                                                            type="text"
                                                            name="educational_company_name"
                                                            defaultValue={school.educational_company_name ?? ""}
                                                            autoComplete="off"
                                                            hasError={!!errors.educational_company_name}
                                                            required
                                                        />
                                                        <InputError message={errors.educational_company_name} />
                                                    </Field>

                                                    <Field>
                                                        <Label htmlFor="branch_type" hasError={!!errors.branch_type} required>
                                                            فرع المدرسة
                                                        </Label>
                                                        <Select name="branch_type" defaultValue={school.branch_type?.id}>
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
                                                        <Select name="building_type" defaultValue={school.building_type?.id}>
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
                                        </div>
                                    </CardFormContent>
                                    <CardFooter className="justify-end gap-x-4 border-t">
                                        <Button variant="outline" className="flex items-center gap-x-2" asChild>
                                            <Link href={show.url({ school: school })}>
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
    )
}

Edit.layout = (props: PageProps) => ({
    breadcrumbs: [
        {
            title: 'المدارس',
            href: index.url(),
        },
        {
            title: 'عرض بيانات المدرسة',
            href: show.url({ school: props.school }),
        },
        {
            title: 'تعديل بيانات المدرسة',
            href: edit.url({ school: props.school }),
        },
    ],
});
