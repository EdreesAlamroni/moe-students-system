import React from 'react'

import { Form, Head, Link } from "@inertiajs/react";

import type { Classroom } from "@/types";

import MainContainer from "@/components/ui/structure/main-container";
import { Card, CardDescription, CardFooter, CardFormContent, CardHeader, CardTitle } from "@/components/ui/structure/card";
import { FormLayout } from "@/components/ui/structure/form-layout";

import RequiredFieldsNote from "@/components/ui/display/required-fields-note";
import { DetailField, DetailFields } from "@/components/ui/display/detail-field";
import { DetailLabel } from "@/components/ui/display/detail-label";
import { DetailValue } from "@/components/ui/display/detail-value";

import Field from "@/components/ui/controls/field";
import { Label } from "@/components/ui/controls/label";
import { Input } from "@/components/ui/controls/input";
import InputError from "@/components/ui/controls/input-error";

import ValidationErrors from "@/components/ui/alerts/validation-errors";

import { Button } from "@/components/ui/actions/button";
import { UpdateButton } from "@/components/ui/actions/submit-button";

import { ReplyIcon } from "lucide-react";

import { index, show, edit, update } from "@/routes/school/classrooms";

type PageProps = {
    classroom: Classroom;
};

export default function Edit({ classroom }: PageProps) {
    return (
        <>
            <Head title="تعديل بيانات الفصل الدراسي" />

            <MainContainer showAcademicYearNotice>
                <Form
                    {...update.form({ classroom: classroom })}
                    disableWhileProcessing
                >
                    {({ processing, errors }) => (
                        <FormLayout>
                            <ValidationErrors errors={errors} />

                            <section>
                                <Card>
                                    <CardHeader className="border-b">
                                        <CardTitle>تعديل بيانات الفصل الدراسي</CardTitle>
                                        <CardDescription>
                                            <RequiredFieldsNote />
                                        </CardDescription>
                                    </CardHeader>

                                    <CardFormContent>
                                        <DetailFields columns={2}>
                                            <DetailField>
                                                <DetailLabel>المرحلة الدراسية</DetailLabel>
                                                <DetailValue value={classroom.grade_level?.educational_stage.name} />
                                            </DetailField>

                                            <DetailField>
                                                <DetailLabel>الصف الدراسي</DetailLabel>
                                                <DetailValue value={classroom.grade_level?.name} />
                                            </DetailField>

                                            <DetailField>
                                                <DetailLabel>اسم الفصل الدراسي</DetailLabel>
                                                <DetailValue value={classroom.name} className="font-mono" />
                                            </DetailField>

                                            <Field>
                                                <Label
                                                    htmlFor="capacity"
                                                    hasError={!!errors.capacity}
                                                    required
                                                >
                                                    السعة
                                                </Label>

                                                <Input
                                                    id="capacity"
                                                    type="number"
                                                    name="capacity"
                                                    className="font-mono"
                                                    min={1}
                                                    defaultValue={classroom.capacity}
                                                    hasError={!!errors.capacity}
                                                    autoComplete="off"
                                                    required
                                                />

                                                <InputError message={errors.capacity} />
                                            </Field>
                                        </DetailFields>
                                    </CardFormContent>

                                    <CardFooter className="justify-end gap-x-4 border-t">
                                        <Button variant="outline" className="flex items-center gap-x-2" asChild>
                                            <Link href={show.url({ classroom: classroom })}>
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
            title: 'الفصول الدراسية',
            href: index.url(),
        },
        {
            title: 'تعديل بيانات الفصل الدراسي',
            href: edit.url({ classroom: props.classroom }),
        },
    ],
});
