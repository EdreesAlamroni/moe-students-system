import React from 'react'

import { Form, Head, Link } from "@inertiajs/react";

import { codeSlugInputConstraints } from "@/lib/input-constraints";

import type { GradeLevel } from "@/types";

import MainContainer from "@/components/ui/structure/main-container";
import { Card, CardFormContent, CardDescription, CardHeader, CardTitle, CardFooter } from "@/components/ui/structure/card";
import { FormLayout } from "@/components/ui/structure/form-layout";

import RequiredFieldsNote from "@/components/ui/display/required-fields-note";

import Field from "@/components/ui/controls/field";
import { Label } from "@/components/ui/controls/label";
import { Input } from "@/components/ui/controls/input";
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/controls/select";
import InputError from "@/components/ui/controls/input-error";
import { Hint } from "@/components/ui/controls/hint";

import ValidationErrors from "@/components/ui/alerts/validation-errors";

import { Button } from "@/components/ui/actions/button";
import { CreateButton } from "@/components/ui/actions/submit-button";

import { ReplyIcon } from "lucide-react";

import { index, create, store } from "@/routes/administration/subjects";
import { Textarea } from "@/components/ui/controls/textarea";

type GradeLevelOption = Pick<GradeLevel, "id" | "name">;

type PageProps = {
    gradeLevels: GradeLevelOption[];
}

const BOOLEAN_OPTIONS = [
    { value: "1", label: "نعم" },
    { value: "0", label: "لا" },
];

export default function Create({ gradeLevels }: PageProps) {
    return (
        <>
            <Head title="إضافة مقرر دراسي" />

            <MainContainer>
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
                                        <CardTitle>إضافة مقرر دراسي</CardTitle>
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

                                                <Select name="grade_level_id">
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
                                                    htmlFor="name"
                                                    hasError={!!errors.name}
                                                    required
                                                >
                                                    اسم المقرر الدراسي
                                                </Label>

                                                <Input
                                                    id="name"
                                                    type="text"
                                                    name="name"
                                                    hasErrors={!!errors.name}
                                                    required
                                                />

                                                <InputError message={errors.name} />
                                            </Field>

                                            <Field>
                                                <Label
                                                    htmlFor="code"
                                                    hasError={!!errors.code}
                                                    required
                                                >
                                                    الرمز / الكود
                                                </Label>

                                                <Input
                                                    id="code"
                                                    type="text"
                                                    name="code"
                                                    hasErrors={!!errors.code}
                                                    className="font-mono"
                                                    required
                                                    aria-describedby="code-hint"
                                                    {...codeSlugInputConstraints()}
                                                />

                                                <Hint id="code-hint">
                                                    يجب أن يتكوّن الرمز / الكود من حروفٍ إنجليزية وأرقامٍ فقط، مع السماح باستخدام ( - أو _ ).
                                                </Hint>

                                                <InputError message={errors.code} />
                                            </Field>

                                            <Field>
                                                <Label
                                                    htmlFor="included_in_total_score"
                                                    hasError={!!errors.included_in_total_score}
                                                    required
                                                >
                                                    هل المقرر داخل المجموع ؟
                                                </Label>

                                                <Select name="included_in_total_score">
                                                    <SelectTrigger
                                                        id="included_in_total_score"
                                                        hasError={!!errors.included_in_total_score}
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

                                                <InputError message={errors.included_in_total_score} />
                                            </Field>

                                            <Field>
                                                <Label
                                                    htmlFor="needs_lab"
                                                    hasError={!!errors.needs_lab}
                                                    required
                                                >
                                                    هل يحتاج إلى معمل ؟
                                                </Label>

                                                <Select name="needs_lab">
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

                                                <Textarea
                                                    id="description"
                                                    name="description"
                                                    hasError={!!errors.description}
                                                />

                                                <InputError message={errors.description} />
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
            title: 'المقررات الدراسية',
            href: index.url(),
        },
        {
            title: 'إضافة مقرر دراسي',
            href: create.url(),
        },
    ],
});
