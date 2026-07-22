import React, { useState } from 'react'

import { Form, Head, Link } from "@inertiajs/react";

import type { Enum, GradeLevel } from "@/types";

import MainContainer from "@/components/ui/structure/main-container";
import { Card, CardDescription, CardFooter, CardFormContent, CardHeader, CardTitle } from "@/components/ui/structure/card";
import { FormLayout } from "@/components/ui/structure/form-layout";

import RequiredFieldsNote from "@/components/ui/display/required-fields-note";

import Field from "@/components/ui/controls/field";
import { Label } from "@/components/ui/controls/label";
import { Input } from "@/components/ui/controls/input";
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/controls/select";
import InputError from "@/components/ui/controls/input-error";

import ValidationErrors from "@/components/ui/alerts/validation-errors";

import { Button } from "@/components/ui/actions/button";
import { CreateButton } from "@/components/ui/actions/submit-button";

import { ReplyIcon } from "lucide-react";

import { create, index, store } from "@/routes/school/classrooms";
import { decimalInputConstraints } from "@/lib/input-constraints";

type GradeLevelOption = Pick<GradeLevel, "id" | "name" | "educational_stage">;
type ClassroomNameOption = Pick<Enum, "id" | "name">;

type PageProps = {
    educationalStages: Enum[];
    gradeLevels: GradeLevelOption[];
    classroomNames: ClassroomNameOption[];
};

export default function Create({ educationalStages, gradeLevels, classroomNames }: PageProps) {
    const [selectedStage, setSelectedStage] = useState<string>("");
    const [selectedGradeLevelId, setSelectedGradeLevelId] = useState<string>("");

    const availableGradeLevels = selectedStage
        ? gradeLevels.filter((gradeLevel) => gradeLevel.educational_stage.id === selectedStage)
        : [];

    return (
        <>
            <Head title="إضافة فصل دراسي جديد" />

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
                                        <CardTitle>إضافة فصل دراسي جديد</CardTitle>
                                        <CardDescription>
                                            <RequiredFieldsNote />
                                        </CardDescription>
                                    </CardHeader>

                                    <CardFormContent>
                                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <Field>
                                                <Label
                                                    htmlFor="educational_stage"
                                                    hasError={!!errors.educational_stage}
                                                    required
                                                >
                                                    المرحلة الدراسية
                                                </Label>

                                                <Select
                                                    name="educational_stage"
                                                    value={selectedStage || undefined}
                                                    onValueChange={(stage) => {
                                                        setSelectedStage(stage);
                                                        setSelectedGradeLevelId("");
                                                    }}
                                                >
                                                    <SelectTrigger id="educational_stage" hasError={!!errors.educational_stage}>
                                                        <SelectValue placeholder="اختر المرحلة الدراسية" />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectGroup>
                                                            {educationalStages.map((stage) => (
                                                                <SelectItem
                                                                    key={stage.id}
                                                                    value={stage.id}
                                                                >
                                                                    {stage.name}
                                                                </SelectItem>
                                                            ))}
                                                        </SelectGroup>
                                                    </SelectContent>
                                                </Select>

                                                <InputError message={errors.educational_stage} />
                                            </Field>

                                            <Field>
                                                <Label
                                                    htmlFor="grade_level_id"
                                                    hasError={!!errors.grade_level_id}
                                                    required
                                                >
                                                    الصف الدراسي
                                                </Label>

                                                <Select
                                                    key={selectedStage}
                                                    name="grade_level_id"
                                                    value={selectedGradeLevelId || undefined}
                                                    onValueChange={setSelectedGradeLevelId}
                                                    disabled={!selectedStage}
                                                >
                                                    <SelectTrigger id="grade_level_id" hasError={!!errors.grade_level_id}>
                                                        <SelectValue placeholder="اختر الصف الدراسي" />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectGroup>
                                                            {availableGradeLevels.map((gradeLevel) => (
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
                                                <Label htmlFor="name" hasError={!!errors.name} required>
                                                    اسم الفصل الدراسي
                                                </Label>

                                                <Select name="name">
                                                    <SelectTrigger
                                                        id="name"
                                                        hasError={!!errors.name}
                                                    >
                                                        <SelectValue placeholder="اختر اسم الفصل الدراسي" />
                                                    </SelectTrigger>
                                                    <SelectContent className="font-mono">
                                                        <SelectGroup>
                                                            {classroomNames.map((classroomName) => (
                                                                <SelectItem
                                                                    key={classroomName.id}
                                                                    value={classroomName.id}
                                                                >
                                                                    {classroomName.name}
                                                                </SelectItem>
                                                            ))}
                                                        </SelectGroup>
                                                    </SelectContent>
                                                </Select>

                                                <InputError message={errors.name} />
                                            </Field>

                                            <Field>
                                                <Label htmlFor="capacity" hasError={!!errors.capacity} required>
                                                    السعة
                                                </Label>

                                                <Input
                                                    id="capacity"
                                                    type="text"
                                                    name="capacity"
                                                    className="font-mono"
                                                    min={1}
                                                    hasError={!!errors.capacity}
                                                    autoComplete="off"
                                                    required
                                                    {...decimalInputConstraints({
                                                        min: 1,
                                                        allowNegative: false,
                                                        allowDecimal: false,
                                                    })}
                                                />

                                                <InputError message={errors.capacity} />
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
    );
}

Create.layout = () => ({
    breadcrumbs: [
        {
            title: 'الفصول الدراسية',
            href: index.url(),
        },
        {
            title: 'إضافة فصل دراسي جديد',
            href: create.url(),
        },
    ],
});
