import React from "react";

import { Form, router, usePage } from "@inertiajs/react";

import { cn } from "@/lib/utils";

import { FormLayout } from "@/components/ui/structure/form-layout";

import { Alert, AlertDescription, AlertTitle } from "@/components/ui/alerts/alert";

import { Dialog, DialogBody, DialogClose, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from "@/components/ui/overlay/dialog";

import Field from "@/components/ui/controls/field";
import { Label } from "@/components/ui/controls/label";
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/controls/select";
import InputError from "@/components/ui/controls/input-error";

import { Button } from "@/components/ui/actions/button";
import { UpdateButton } from "@/components/ui/actions/submit-button";

import { InfoIcon } from "lucide-react";

import { select as selectAcademicYear } from "@/routes/academic-year";

type MainContainerProps = React.ComponentProps<"main"> & {
    showAcademicYearNotice?: boolean;
    changeAcademicYearNotice?: boolean;
};

export default function MainContainer({
    showAcademicYearNotice = false,
    changeAcademicYearNotice = false,
    children,
    className,
    ...props
}: MainContainerProps) {
    return (
        <main
            className={cn("flex flex-col gap-6 p-4", className)}
            {...props}
        >
            {(showAcademicYearNotice && !changeAcademicYearNotice) && <ShowAcademicYearNotice />}
            {changeAcademicYearNotice && <ChangeAcademicYearNotice />}
            {children}
        </main>
    );
}

function ShowAcademicYearNotice() {
    const { currentAcademicYear } = usePage().props;

    return (
        <Alert aria-live="polite" aria-atomic="true">
            <InfoIcon className="mt-px" />
            <AlertTitle className="flex flex-wrap items-center gap-2">
                <span>يتم حالياً عرض بيانات السنة الدراسية</span>
                <span className="font-mono">{currentAcademicYear?.name}</span>
            </AlertTitle>
        </Alert>
    );
}

function ChangeAcademicYearNotice() {
    const { currentAcademicYear, availableAcademicYears } = usePage().props;

    const [isDialogOpen, setIsDialogOpen] = React.useState(false);
    const [selectedYearId, setSelectedYearId] = React.useState(
        () => currentAcademicYear?.id?.toString() ?? "",
    );

    const hasAvailableYears = availableAcademicYears.length > 0;

    const handleDialogOpenChange = (open: boolean): void => {
        setIsDialogOpen(open);

        if (open) {
            setSelectedYearId(currentAcademicYear?.id?.toString() ?? "");
        }
    };

    const handleFormSuccess = (): void => {
        setIsDialogOpen(false);
        router.flushAll();
    };

    return (
        <Dialog open={isDialogOpen} onOpenChange={handleDialogOpenChange}>
            <Alert aria-live="polite" aria-atomic="true">
                <InfoIcon className="mt-px" />
                <AlertTitle className="flex flex-wrap items-center gap-2">
                    <span>يتم حالياً عرض بيانات السنة الدراسية</span>
                    <span className="font-mono">{currentAcademicYear?.name}</span>
                </AlertTitle>
                <AlertDescription className="mt-0.5 flex flex-wrap items-center gap-1">
                    <span>
                        يمكنك اختيار سنة دراسية مختلفة لعرض بياناتها، وسيتم تحديث البيانات بناءً على اختيارك.
                    </span>
                    <DialogTrigger asChild>
                        <button
                            type="button"
                            className="cursor-pointer font-medium underline"
                        >
                            تغيير من هنـا
                        </button>
                    </DialogTrigger>
                </AlertDescription>
            </Alert>

            <DialogContent>
                <Form
                    action={selectAcademicYear.url()}
                    method="PATCH"
                    disableWhileProcessing
                    options={{ preserveScroll: true, preserveState: false }}
                    onSuccess={handleFormSuccess}
                >
                    {({ processing, errors }) => (
                        <FormLayout>
                            <DialogHeader>
                                <DialogTitle>تغيير السنة الدراسية</DialogTitle>
                                <DialogDescription>
                                    اختر السنة الدراسية التي ترغب في عرض بياناتها. يمكنك تعديل الاختيار في أي وقت لاحقاً.
                                </DialogDescription>
                            </DialogHeader>

                            <DialogBody>
                                <Field>
                                    <Label
                                        htmlFor="academic_year_id"
                                        hasError={!!errors.academic_year_id}
                                        required
                                    >
                                        السنة الدراسية
                                    </Label>
                                    <Select
                                        name="academic_year_id"
                                        value={selectedYearId}
                                        onValueChange={setSelectedYearId}
                                    >
                                        <SelectTrigger
                                            id="academic_year_id"
                                            hasError={!!errors.academic_year_id}
                                        >
                                            <SelectValue
                                                placeholder={
                                                    hasAvailableYears
                                                        ? "اختر السنة الدراسية"
                                                        : "لا توجد سنوات دراسية متاحة للاختيار"
                                                }
                                            />
                                        </SelectTrigger>
                                        {hasAvailableYears && (
                                            <SelectContent>
                                                <SelectGroup>
                                                    {availableAcademicYears.map((academicYear) => (
                                                        <SelectItem
                                                            key={academicYear.id}
                                                            value={academicYear.id.toString()}
                                                        >
                                                            <span className="font-mono">{academicYear.name}</span>
                                                        </SelectItem>
                                                    ))}
                                                </SelectGroup>
                                            </SelectContent>
                                        )}
                                    </Select>
                                    <InputError message={errors.academic_year_id} />
                                </Field>
                            </DialogBody>

                            <DialogFooter>
                                <DialogClose asChild>
                                    <Button variant="outline">إغلاق</Button>
                                </DialogClose>
                                <UpdateButton processing={processing} />
                            </DialogFooter>
                        </FormLayout>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    );
}
