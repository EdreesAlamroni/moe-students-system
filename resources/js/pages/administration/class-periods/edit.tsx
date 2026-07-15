import React, { useState } from 'react'

import { Form, Head, Link } from "@inertiajs/react";

import type { ClassPeriod, Enum } from "@/types";

import MainContainer from "@/components/ui/structure/main-container";
import { Card, CardFormContent, CardDescription, CardHeader, CardTitle, CardFooter } from "@/components/ui/structure/card";
import { FormLayout } from "@/components/ui/structure/form-layout";

import RequiredFieldsNote from "@/components/ui/display/required-fields-note";

import Field from "@/components/ui/controls/field";
import { Label } from "@/components/ui/controls/label";
import { Input } from "@/components/ui/controls/input";
import { Checkbox } from "@/components/ui/controls/checkbox";
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/controls/select";
import InputError from "@/components/ui/controls/input-error";

import ValidationErrors from "@/components/ui/alerts/validation-errors";

import { Button } from "@/components/ui/actions/button";
import { UpdateButton } from "@/components/ui/actions/submit-button";

import { ReplyIcon } from "lucide-react";

import { index, show, edit, update } from "@/routes/administration/class-periods";

type PageProps = {
    classPeriod: ClassPeriod;
    academicPeriods: Enum[];
}

export default function Edit({ classPeriod, academicPeriods }: PageProps) {
    const [isBreak, setIsBreak] = useState(classPeriod.is_break);

    return (
        <>
            <Head title="تعديل بيانات الحصة" />

            <MainContainer>
                <Form
                    {...update.form({ classPeriod: classPeriod })}
                    disableWhileProcessing
                >
                    {({ processing, errors }) => (
                        <FormLayout>
                            <ValidationErrors errors={errors} />

                            <input type="hidden" name="is_break" value={isBreak ? "1" : "0"} />

                            <section>
                                <Card>
                                    <CardHeader className="border-b">
                                        <CardTitle>تعديل بيانات الحصة</CardTitle>
                                        <CardDescription>
                                            <RequiredFieldsNote />
                                        </CardDescription>
                                    </CardHeader>

                                    <CardFormContent>
                                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                                                    defaultValue={classPeriod.academic_period.id}
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

                                            <Field>
                                                <Label
                                                    htmlFor="name"
                                                    hasError={!!errors.name}
                                                    required
                                                >
                                                    اسم الحصة
                                                </Label>

                                                <Input
                                                    id="name"
                                                    type="text"
                                                    name="name"
                                                    defaultValue={classPeriod.name}
                                                    hasErrors={!!errors.name}
                                                    placeholder="مثال: الحصة الأولى"
                                                    autoComplete="off"
                                                    required
                                                />

                                                <InputError message={errors.name} />
                                            </Field>

                                            <Field>
                                                <Label
                                                    htmlFor="start_time"
                                                    hasError={!!errors.start_time}
                                                    required
                                                >
                                                    وقت البداية
                                                </Label>

                                                <Input
                                                    id="start_time"
                                                    type="time"
                                                    name="start_time"
                                                    className="font-mono"
                                                    defaultValue={classPeriod.start_time}
                                                    hasErrors={!!errors.start_time}
                                                    autoComplete="off"
                                                    required
                                                />

                                                <InputError message={errors.start_time} />
                                            </Field>

                                            <Field>
                                                <Label
                                                    htmlFor="end_time"
                                                    hasError={!!errors.end_time}
                                                    required
                                                >
                                                    وقت النهاية
                                                </Label>

                                                <Input
                                                    id="end_time"
                                                    type="time"
                                                    name="end_time"
                                                    className="font-mono"
                                                    defaultValue={classPeriod.end_time}
                                                    hasErrors={!!errors.end_time}
                                                    autoComplete="off"
                                                    required
                                                />

                                                <InputError message={errors.end_time} />
                                            </Field>

                                            <Field>
                                                <Label
                                                    htmlFor="order"
                                                    hasError={!!errors.order}
                                                    required
                                                >
                                                    الترتيب
                                                </Label>

                                                <Input
                                                    id="order"
                                                    type="number"
                                                    name="order"
                                                    className="font-mono"
                                                    defaultValue={classPeriod.order}
                                                    hasErrors={!!errors.order}
                                                    min={0}
                                                    autoComplete="off"
                                                    required
                                                />

                                                <InputError message={errors.order} />
                                            </Field>

                                            <Field className="flex flex-row items-end">
                                                <div className="flex items-center gap-x-3 pb-[0.65rem]">
                                                    <Checkbox
                                                        id="is_break"
                                                        checked={isBreak}
                                                        onCheckedChange={(checked) => {
                                                            setIsBreak(checked === true);
                                                        }}
                                                    />

                                                    <Label
                                                        htmlFor="is_break"
                                                        style={{ fontWeight: '500' }}
                                                    >
                                                        هذه فترة استراحة
                                                    </Label>
                                                </div>

                                                <InputError message={errors.is_break} />
                                            </Field>
                                        </div>
                                    </CardFormContent>
                                    <CardFooter className="justify-end gap-x-4 border-t">
                                        <Button variant="outline" className="flex items-center gap-x-2" asChild>
                                            <Link href={show.url({ classPeriod: classPeriod })}>
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
            title: 'الحصص الدراسية',
            href: index.url(),
        },
        {
            title: 'تعديل بيانات الحصة',
            href: edit.url({ classPeriod: props.classPeriod }),
        },
    ],
});
