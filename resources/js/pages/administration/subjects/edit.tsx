import React from 'react'

import { Form, Head, Link } from "@inertiajs/react";

import type { Subject } from "@/types";

import MainContainer from "@/components/ui/structure/main-container";
import { Card, CardFormContent, CardDescription, CardHeader, CardTitle, CardFooter } from "@/components/ui/structure/card";
import { FormLayout } from "@/components/ui/structure/form-layout";

import RequiredFieldsNote from "@/components/ui/display/required-fields-note";
import { DetailField } from "@/components/ui/display/detail-field";
import { DetailLabel } from "@/components/ui/display/detail-label";
import { DetailValue } from "@/components/ui/display/detail-value";

import Field from "@/components/ui/controls/field";
import { Label } from "@/components/ui/controls/label";
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/controls/select";
import InputError from "@/components/ui/controls/input-error";

import ValidationErrors from "@/components/ui/alerts/validation-errors";

import { Button } from "@/components/ui/actions/button";
import { UpdateButton } from "@/components/ui/actions/submit-button";

import { cn } from "@/lib/utils";

import { ReplyIcon } from "lucide-react";

import { index, show, edit, update } from "@/routes/administration/subjects";

type GradeLevelSummary = {
    id: number;
    name: string;
}

type SubjectProps = Subject & {
    grade_level?: GradeLevelSummary;
}

type PageProps = {
    subject: SubjectProps;
}

const BOOLEAN_OPTIONS = [
    { value: "1", label: "نعم" },
    { value: "0", label: "لا" },
];

export default function Edit({ subject }: PageProps) {
    return (
        <>
            <Head title="تعديل المقرر الدراسي" />

            <MainContainer>
                <Form
                    {...update.form({ subject: subject })}
                    disableWhileProcessing
                >
                    {({ processing, errors }) => (
                        <FormLayout>
                            <ValidationErrors errors={errors} />

                            <input type="hidden" name="grade_level_id" value={subject.grade_level_id} />
                            <input type="hidden" name="name" value={subject.name} />
                            <input type="hidden" name="code" value={subject.code} />
                            <input
                                type="hidden"
                                name="included_in_total_score"
                                value={subject.included_in_total_score ? "1" : "0"}
                            />

                            <section>
                                <Card>
                                    <CardHeader className="border-b">
                                        <CardTitle>تعديل المقرر الدراسي</CardTitle>
                                        <CardDescription>
                                            <RequiredFieldsNote />
                                        </CardDescription>
                                    </CardHeader>

                                    <CardFormContent>
                                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <DetailField>
                                                <DetailLabel>اسم المقرر الدراسي</DetailLabel>
                                                <DetailValue value={subject.name} />
                                            </DetailField>

                                            <DetailField>
                                                <DetailLabel>الصف الدراسي</DetailLabel>
                                                <DetailValue value={subject.grade_level?.name} />
                                            </DetailField>

                                            <Field className="col-span-full">
                                                <Label
                                                    htmlFor="needs_lab"
                                                    hasError={!!errors.needs_lab}
                                                    required
                                                >
                                                    هل يحتاج إلى معمل ؟
                                                </Label>

                                                <Select
                                                    name="needs_lab"
                                                    defaultValue={subject.needs_lab ? "1" : "0"}
                                                >
                                                    <SelectTrigger
                                                        id="needs_lab"
                                                        hasError={!!errors.needs_lab}
                                                    >
                                                        <SelectValue placeholder="اختر" />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectGroup>
                                                            {BOOLEAN_OPTIONS.map((option) => (
                                                                <SelectItem
                                                                    key={option.value}
                                                                    value={option.value}
                                                                >
                                                                    {option.label}
                                                                </SelectItem>
                                                            ))}
                                                        </SelectGroup>
                                                    </SelectContent>
                                                </Select>

                                                <InputError message={errors.needs_lab} />
                                            </Field>

                                            <Field className="col-span-full">
                                                <Label
                                                    htmlFor="description"
                                                    hasError={!!errors.description}
                                                >
                                                    الوصف
                                                </Label>

                                                <textarea
                                                    id="description"
                                                    name="description"
                                                    rows={4}
                                                    defaultValue={subject.description ?? ""}
                                                    aria-invalid={!!errors.description}
                                                    className={cn(
                                                        "min-h-24 w-full min-w-0 px-2.5 py-2 bg-transparent text-sm border border-input outline-none transition-colors placeholder:text-muted-foreground",
                                                        "focus-visible:border-primary focus-visible:ring-1 focus-visible:ring-primary/50",
                                                        "disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50",
                                                        "aria-invalid:border-destructive aria-invalid:ring-destructive/30",
                                                    )}
                                                />

                                                <InputError message={errors.description} />
                                            </Field>
                                        </div>
                                    </CardFormContent>
                                    <CardFooter className="justify-end gap-x-4 border-t">
                                        <Button variant="outline" className="flex items-center gap-x-2" asChild>
                                            <Link href={show.url({ subject: subject })}>
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
            title: 'المقررات الدراسية',
            href: index.url(),
        },
        {
            title: 'تعديل المقرر الدراسي',
            href: edit.url({ subject: props.subject }),
        },
    ],
});
