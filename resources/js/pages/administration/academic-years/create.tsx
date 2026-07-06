import React from 'react'

import { Form, Head, Link } from "@inertiajs/react";

import MainContainer from "@/components/ui/structure/main-container";
import { Card, CardFormContent, CardDescription, CardHeader, CardTitle, CardFooter } from "@/components/ui/structure/card";
import { FormLayout } from "@/components/ui/structure/form-layout";

import RequiredFieldsNote from "@/components/ui/display/required-fields-note";
import { DetailField } from "@/components/ui/display/detail-field";
import { DetailLabel } from "@/components/ui/display/detail-label";
import { DetailValue } from "@/components/ui/display/detail-value";

import Field from "@/components/ui/controls/field";
import { Label } from "@/components/ui/controls/label";
import DatePicker from "@/components/ui/controls/date-picker";
import InputError from "@/components/ui/controls/input-error";

import ValidationErrors from "@/components/ui/alerts/validation-errors";

import { Button } from "@/components/ui/actions/button";
import { CreateButton } from "@/components/ui/actions/submit-button";

import { ReplyIcon } from "lucide-react";

import { index, create, store } from "@/routes/administration/academic-years";

type PageProps = {
    name: string;
    minStartDate: string;
    maxEndDate: string;
}

export default function Create({ name, minStartDate, maxEndDate }: PageProps) {
    return (
        <>
            <Head title="إضافة سنة دراسية" />

            <MainContainer>
                <Form
                    action={store.url()}
                    method="POST"
                    disableWhileProcessing
                >
                    {({ processing, errors }) => (
                        <FormLayout>
                            <ValidationErrors errors={errors} />

                            <section>
                                <Card>
                                    <CardHeader className="border-b">
                                        <CardTitle>إضافة سنة دراسية</CardTitle>
                                        <CardDescription>
                                            <RequiredFieldsNote />
                                        </CardDescription>
                                    </CardHeader>

                                    <CardFormContent>
                                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <DetailField className="col-span-full">
                                                <DetailLabel>السنة الدراسية</DetailLabel>
                                                <DetailValue value={name} className="font-mono" />
                                            </DetailField>

                                            <Field>
                                                <Label
                                                    htmlFor="start_date"
                                                    hasError={!!errors.start_date}
                                                    required
                                                >
                                                    تاريخ بداية العام الدراسي
                                                </Label>

                                                <DatePicker
                                                    id="start_date"
                                                    name="start_date"
                                                    hasError={!!errors.start_date}
                                                    placeholder="تاريخ بداية العام الدراسي"
                                                    required
                                                    min={minStartDate}
                                                    max={maxEndDate}
                                                />

                                                <InputError message={errors.start_date} />
                                            </Field>

                                            <Field>
                                                <Label
                                                    htmlFor="end_date"
                                                    hasError={!!errors.end_date}
                                                    required
                                                >
                                                    تاريخ انتهاء العام الدراسي
                                                </Label>

                                                <DatePicker
                                                    id="end_date"
                                                    name="end_date"
                                                    hasError={!!errors.end_date}
                                                    placeholder="تاريخ انتهاء العام الدراسي"
                                                    required
                                                    min={minStartDate}
                                                    max={maxEndDate}
                                                />

                                                <InputError message={errors.end_date} />
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

Create.layout = () => ({
    breadcrumbs: [
        {
            title: 'السنوات الدراسية',
            href: index.url(),
        },
        {
            title: 'إضافة سنة دراسية جديدة',
            href: create.url(),
        },
    ],
});
