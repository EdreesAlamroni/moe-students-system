import React from "react";

import { Form, router } from "@inertiajs/react";

import type { GradeLevel, SchoolPeriod } from "@/types";

import { FormLayout } from "@/components/ui/structure/form-layout";

import {
    Dialog,
    DialogBody,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from "@/components/ui/overlay/dialog";

import Field from "@/components/ui/controls/field";
import { Label } from "@/components/ui/controls/label";
import { Input } from "@/components/ui/controls/input";
import { MultiSelect } from "@/components/ui/controls/multi-select";
import InputError from "@/components/ui/controls/input-error";

import { Button } from "@/components/ui/actions/button";
import { ConfirmButton } from "@/components/ui/actions/submit-button";

import { store } from "@/routes/school/grade-levels/transfers";

type TransferGradeLevelsDialogProps = {
    gradeLevels: GradeLevel[];
    siblingPeriod: SchoolPeriod;
    children: React.ReactNode;
};

export default function TransferGradeLevelsDialog({
    gradeLevels,
    siblingPeriod,
    children,
}: TransferGradeLevelsDialogProps) {
    const [isDialogOpen, setIsDialogOpen] = React.useState(false);
    const [selectedGradeLevels, setSelectedGradeLevels] = React.useState<string[]>([]);
    const [gradeLevelsFieldKey, setGradeLevelsFieldKey] = React.useState(0);

    const resetGradeLevelSelection = (): void => {
        setSelectedGradeLevels([]);
        setGradeLevelsFieldKey((key) => key + 1);
    };

    const handleDialogOpenChange = (open: boolean): void => {
        setIsDialogOpen(open);

        if (open) {
            resetGradeLevelSelection();
        }
    };

    const handleFormSuccess = (): void => {
        setIsDialogOpen(false);
        router.flushAll();
    };

    return (
        <Dialog
            open={isDialogOpen}
            onOpenChange={handleDialogOpenChange}
        >
            <DialogTrigger asChild>{children}</DialogTrigger>

            <DialogContent>
                <Form
                    {...store.form()}
                    disableWhileProcessing
                    options={{ preserveScroll: true, preserveState: "errors" }}
                    onSuccess={handleFormSuccess}
                    onError={resetGradeLevelSelection}
                >
                    {({ processing, errors }) => (
                        <FormLayout>
                            <DialogHeader>
                                <DialogTitle>نقل الصفوف الدراسية</DialogTitle>
                                <DialogDescription>
                                    اختر الصفوف الدراسية التي ترغب في نقلها من الفترة الدراسية الحالية إلى الفترة الدراسية الأخرى لنفس المدرسة.
                                    سيتم إنشاء المراحل الدراسية الناقصة في الفترة الدراسية الأخرى تلقائياً عند الحاجة.
                                </DialogDescription>
                            </DialogHeader>

                            <DialogBody>
                                {selectedGradeLevels.map((gradeLevelId) => (
                                    <input
                                        key={gradeLevelId}
                                        type="hidden"
                                        name="grade_levels[]"
                                        value={gradeLevelId}
                                    />
                                ))}

                                <Field>
                                    <Label
                                        htmlFor="grade_levels"
                                        hasError={!!errors.grade_levels}
                                        required
                                    >
                                        الصفوف الدراسية
                                    </Label>

                                    <MultiSelect
                                        key={gradeLevelsFieldKey}
                                        id="grade_levels"
                                        options={gradeLevels}
                                        defaultValue={[]}
                                        onValueChange={setSelectedGradeLevels}
                                        placeholder="اختر الصفوف الدراسية"
                                        emptyPlaceholder="لا توجد صفوف دراسية متاحة للنقل"
                                        singleLine={false}
                                        maxCount={1}
                                        minWidth="0"
                                        className="w-full max-w-full"
                                        popoverClassName="w-[var(--radix-popover-trigger-width)]"
                                        modalPopover
                                        aria-invalid={!!errors.grade_levels}
                                    />

                                    <InputError message={errors.grade_levels} />
                                </Field>

                                <Field>
                                    <Label htmlFor="destination_period">
                                        الفترة الدراسية الأخرى
                                    </Label>

                                    <Input
                                        id="destination_period"
                                        type="text"
                                        value={siblingPeriod.name}
                                        disabled
                                        readOnly
                                    />
                                </Field>
                            </DialogBody>

                            <DialogFooter>
                                <DialogClose asChild>
                                    <Button variant="outline">إغلاق</Button>
                                </DialogClose>
                                <ConfirmButton
                                    processing={processing}
                                    disabled={selectedGradeLevels.length === 0}
                                    title="نـقـل"
                                />
                            </DialogFooter>
                        </FormLayout>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    );
}
