import React from "react";

import { Form, usePage } from "@inertiajs/react";

import { FormLayout } from "@/components/ui/structure/form-layout";

import { Alert, AlertAction, AlertDescription, AlertTitle } from "@/components/ui/alerts/alert";

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
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/components/ui/controls/select";
import InputError from "@/components/ui/controls/input-error";

import { Button } from "@/components/ui/actions/button";
import { UpdateButton } from "@/components/ui/actions/submit-button";

import { TriangleAlertIcon } from "lucide-react";

import { update as updateStudentsGender } from "@/routes/school/students-gender";

export default function ConfigureStudentsGenderNotice() {
    const { dashboard, organization } = usePage().props;

    const [isDialogOpen, setIsDialogOpen] = React.useState(false);
    const [isConfigured, setIsConfigured] = React.useState(false);
    const [selectedGender, setSelectedGender] = React.useState("");

    const school = organization?.type === "school" ? organization : null;
    const isSchoolDashboard = dashboard?.key === "school";
    const needsConfiguration = school !== null && school.students_gender === null;
    const genderOptions = school?.students_gender_options ?? [];

    if (!isSchoolDashboard || !needsConfiguration || isConfigured) {
        return null;
    }

    const handleDialogOpenChange = (open: boolean): void => {
        setIsDialogOpen(open);

        if (open) {
            setSelectedGender("");
        }
    };

    const handleFormSuccess = (): void => {
        setIsConfigured(true);
        setIsDialogOpen(false);
    };

    return (
        <Dialog
            open={isDialogOpen}
            onOpenChange={handleDialogOpenChange}
        >
            <Alert
                variant="warning"
                aria-live="polite"
                aria-atomic="true"
            >
                <TriangleAlertIcon />
                <AlertTitle>يجب تحديد جنس الطلاب الدارسين بالمدرسة</AlertTitle>
                <AlertDescription>
                    لم يتم تحديد جنس الطلاب الدارسين في هذه المدرسة بعد، يرجى التحديث الآن.
                </AlertDescription>
                <AlertAction>
                    <DialogTrigger asChild>
                        <Button
                            size="xs"
                            className="bg-amber-700 hover:bg-amber-700/80"
                        >
                            تحديث
                        </Button>
                    </DialogTrigger>
                </AlertAction>
            </Alert>

            <DialogContent>
                <Form
                    action={updateStudentsGender.url()}
                    method="PATCH"
                    disableWhileProcessing
                    options={{ preserveScroll: true }}
                    onSuccess={handleFormSuccess}
                >
                    {({ processing, errors }) => (
                        <FormLayout>
                            <DialogHeader>
                                <DialogTitle>تحديد جنس الطلاب الدارسين بالمدرسة</DialogTitle>
                                <DialogDescription>
                                    اختر جنس الطلاب الدارسين في هذه المدرسة. هذا الإعداد مطلوب لإكمال تهيئة المدرسة في النظام.
                                </DialogDescription>
                            </DialogHeader>

                            <DialogBody>
                                <Field>
                                    <Label
                                        htmlFor="students_gender"
                                        hasError={!!errors.students_gender}
                                        required
                                    >
                                        جنس الطلاب الدارسين بالمدرسة
                                    </Label>
                                    <Select
                                        name="students_gender"
                                        value={selectedGender}
                                        onValueChange={setSelectedGender}
                                        required
                                    >
                                        <SelectTrigger
                                            id="students_gender"
                                            hasError={!!errors.students_gender}
                                        >
                                            <SelectValue placeholder="اختر جنس الطلاب" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectGroup>
                                                {genderOptions.map((gender) => (
                                                    <SelectItem
                                                        key={gender.id}
                                                        value={gender.id.toString()}
                                                    >
                                                        {gender.name}
                                                    </SelectItem>
                                                ))}
                                            </SelectGroup>
                                        </SelectContent>
                                    </Select>
                                    <InputError message={errors.students_gender} />
                                </Field>
                            </DialogBody>

                            <DialogFooter>
                                <DialogClose asChild>
                                    <Button variant="outline">إغلاق</Button>
                                </DialogClose>
                                <UpdateButton
                                    processing={processing}
                                    disabled={selectedGender === ""}
                                />
                            </DialogFooter>
                        </FormLayout>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    );
}
