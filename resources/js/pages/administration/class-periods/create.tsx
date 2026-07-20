import React, { useState } from 'react'

import { Form, Head, Link } from "@inertiajs/react";

import type { Enum } from "@/types";

import MainContainer from "@/components/ui/structure/main-container";
import { Card, CardFormContent, CardDescription, CardHeader, CardTitle, CardFooter } from "@/components/ui/structure/card";
import { FormLayout } from "@/components/ui/structure/form-layout";

import RequiredFieldsNote from "@/components/ui/display/required-fields-note";

import Field from "@/components/ui/controls/field";
import { Label } from "@/components/ui/controls/label";
import { Input } from "@/components/ui/controls/input";
import { Checkbox } from "@/components/ui/controls/checkbox";
import InputError from "@/components/ui/controls/input-error";

import ValidationErrors from "@/components/ui/alerts/validation-errors";

import { Button } from "@/components/ui/actions/button";
import { CreateButton } from "@/components/ui/actions/submit-button";

import { ReplyIcon } from "lucide-react";

import { index, create, store } from "@/routes/administration/class-periods";

type PageProps = {
    academicPeriod: Enum;
    nextOrder: number;
}

export default function Create({ academicPeriod, nextOrder }: PageProps) {
    const [isBreak, setIsBreak] = useState(false);

    return (
        <>
            <Head title="إضافة حصة جديدة" />

            <MainContainer showAcademicYearNotice>
                <Form
                    {...store.form()}
                    disableWhileProcessing
                >
                    {({ processing, errors }) => (
                        <FormLayout>
                            <ValidationErrors errors={errors} />

                            <input type="hidden" name="academic_period" value={academicPeriod.id} />
                            <input type="hidden" name="is_break" value={isBreak ? "1" : "0"} />

                            <section>
                                <Card>
                                    <CardHeader className="border-b">
                                        <CardTitle>إضافة حصة جديدة</CardTitle>
                                        <CardDescription>
                                            <RequiredFieldsNote />
                                        </CardDescription>
                                    </CardHeader>

                                    <CardFormContent>
                                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <Field>
                                                <Label
                                                    htmlFor="academic_period_display"
                                                    required
                                                >
                                                    الفترة الدراسية
                                                </Label>

                                                <Input
                                                    id="academic_period_display"
                                                    type="text"
                                                    value={academicPeriod.name}
                                                    disabled
                                                    autoComplete="off"
                                                />
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
                                                    hasError={!!errors.name}
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
                                                    hasError={!!errors.start_time}
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
                                                    hasError={!!errors.end_time}
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
                                                    defaultValue={nextOrder}
                                                    hasError={!!errors.order}
                                                    min={0}
                                                    className="font-mono"
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

Create.layout = (props: PageProps) => ({
    breadcrumbs: [
        {
            title: 'الحصص الدراسية',
            href: index.url(),
        },
        {
            title: 'إضافة حصة جديدة',
            href: create.url(props.academicPeriod.id),
        },
    ],
});
