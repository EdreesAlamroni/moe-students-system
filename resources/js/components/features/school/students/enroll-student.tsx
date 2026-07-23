import React from 'react';

import { Form, router } from '@inertiajs/react';

import type { Classroom, GradeLevel, Student } from '@/types';

import Field from '@/components/ui/controls/field';
import { Label } from '@/components/ui/controls/label';
import { EmptyOptionsInput } from '@/components/ui/controls/empty-options-input';
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/controls/select';
import InputError from '@/components/ui/controls/input-error';

import { Button } from '@/components/ui/actions/button';
import { CreateButton, ConfirmButton } from '@/components/ui/actions/submit-button';

import {
    Dialog,
    DialogBody,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogFormLayout,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/overlay/dialog';

import { GraduationCapIcon, PresentationIcon, ArrowRightLeftIcon } from 'lucide-react';
import type { LucideIcon } from 'lucide-react';

import { store as storeClassroomEnrollment, update as updateClassroomEnrollment } from '@/routes/school/students/classroom-enrollments';
import { store as storeGradeLevelEnrollment } from '@/routes/school/students/grade-level-enrollments';

type EnrollmentOption = {
    id: number;
    name: string;
};

type EnrollmentDialogConfig = {
    propName: 'gradeLevels' | 'classrooms';
    fieldName: 'grade_level_id' | 'classroom_id';
    buttonLabel: string;
    title: string;
    description: string;
    label: string;
    selectPlaceholder: string;
    loadingPlaceholder: string;
    emptyPlaceholder: string;
    fetchErrorMessage: string;
    icon: LucideIcon;
    resolveForm: (student: Student) => ReturnType<typeof storeGradeLevelEnrollment.form>;
    submitTitle?: string;
    submitMode?: 'create' | 'confirm';
};

const enrollGradeLevelConfig: EnrollmentDialogConfig = {
    propName: 'gradeLevels',
    fieldName: 'grade_level_id',
    buttonLabel: 'تسجيل في صف دراسي',
    title: 'تسجيل في صف دراسي',
    description: 'يرجى اختيار الصف الدراسي الذي ترغب في تسجيل الطالب فيه.',
    label: 'الصف الدراسي',
    selectPlaceholder: 'اختر الصف الدراسي',
    loadingPlaceholder: 'جاري تحميل الصفوف الدراسية ...',
    emptyPlaceholder: 'لا توجد صفوف دراسية متاحة حالياً',
    fetchErrorMessage: 'تعذر تحميل الصفوف الدراسية حالياً. يرجى إعادة تحميل الصفحة والمحاولة مرة أخرى.',
    icon: GraduationCapIcon,
    resolveForm: (student) => storeGradeLevelEnrollment.form({ student }),
    submitTitle: 'تسجيل',
};

const enrollClassroomConfig: EnrollmentDialogConfig = {
    propName: 'classrooms',
    fieldName: 'classroom_id',
    buttonLabel: 'تسجيل في فصل دراسي',
    title: 'تسجيل في فصل دراسي',
    description: 'يرجى اختيار الفصل الدراسي الذي ترغب في تسجيل الطالب فيه.',
    label: 'الفصل الدراسي',
    selectPlaceholder: 'اختر الفصل الدراسي',
    loadingPlaceholder: 'جاري تحميل الفصول الدراسية ...',
    emptyPlaceholder: 'لا توجد فصول دراسية متاحة حالياً',
    fetchErrorMessage: 'تعذر تحميل الفصول الدراسية حالياً. يرجى إعادة تحميل الصفحة والمحاولة مرة أخرى.',
    icon: PresentationIcon,
    resolveForm: (student) => storeClassroomEnrollment.form({ student }),
    submitTitle: 'تسجيل',
};

const transferClassroomConfig: EnrollmentDialogConfig = {
    propName: 'classrooms',
    fieldName: 'classroom_id',
    buttonLabel: 'نقل إلى فصل دراسي آخر',
    title: 'نقل إلى فصل دراسي آخر',
    description: 'يرجى اختيار الفصل الدراسي الجديد ضمن نفس الصف الدراسي.',
    label: 'الفصل الدراسي الجديد',
    selectPlaceholder: 'اختر الفصل الدراسي الجديد',
    loadingPlaceholder: 'جاري تحميل الفصول الدراسية ...',
    emptyPlaceholder: 'لا توجد فصول دراسية أخرى متاحة حالياً',
    fetchErrorMessage: 'تعذر تحميل الفصول الدراسية حالياً. يرجى إعادة تحميل الصفحة والمحاولة مرة أخرى.',
    icon: ArrowRightLeftIcon,
    resolveForm: (student) => updateClassroomEnrollment.form({ student }),
    submitTitle: 'نقل',
    submitMode: 'confirm',
};

type EnrollmentDialogProps = {
    student: Student;
    options?: EnrollmentOption[];
    config: EnrollmentDialogConfig;
};

function EnrollmentDialog({ student, options, config }: EnrollmentDialogProps) {
    const [isOpen, setIsOpen] = React.useState(false);
    const [isLoading, setIsLoading] = React.useState(false);
    const [fetchError, setFetchError] = React.useState<string | undefined>(undefined);
    const [selectedOptionId, setSelectedOptionId] = React.useState<string | undefined>(undefined);

    const availableOptions = options ?? [];
    const Icon = config.icon;
    const isSubmitDisabled = isLoading || !!fetchError || availableOptions.length === 0;

    const reloadOptions = (): void => {
        setIsLoading(true);
        setFetchError(undefined);

        router.reload({
            only: [config.propName],
            preserveErrors: true,
            onHttpException: () => setFetchError(config.fetchErrorMessage),
            onNetworkError: () => setFetchError(config.fetchErrorMessage),
            onFinish: () => setIsLoading(false),
        });
    };

    const handleOpenChange = (open: boolean): void => {
        setIsOpen(open);
        setFetchError(undefined);
        setSelectedOptionId(undefined);

        if (open) {
            reloadOptions();
        }
    };

    const handleSuccess = (): void => {
        setIsOpen(false);
    };

    const renderOptionsControl = (fieldError?: string) => {
        const hasError = !!fieldError;

        if (isLoading) {
            return (
                <EmptyOptionsInput
                    id={config.fieldName}
                    placeholder={config.loadingPlaceholder}
                    hasError={hasError}
                />
            );
        }

        if (fetchError || availableOptions.length === 0) {
            return (
                <EmptyOptionsInput
                    id={config.fieldName}
                    placeholder={config.emptyPlaceholder}
                    hasError={hasError}
                />
            );
        }

        return (
            <Select
                name={config.fieldName}
                value={selectedOptionId}
                onValueChange={setSelectedOptionId}
                required
            >
                <SelectTrigger
                    id={config.fieldName}
                    hasError={hasError}
                >
                    <SelectValue placeholder={config.selectPlaceholder} />
                </SelectTrigger>
                <SelectContent>
                    <SelectGroup>
                        {availableOptions.map((option) => (
                            <SelectItem
                                key={option.id}
                                value={option.id.toString()}
                            >
                                {option.name}
                            </SelectItem>
                        ))}
                    </SelectGroup>
                </SelectContent>
            </Select>
        );
    };

    return (
        <Dialog
            open={isOpen}
            onOpenChange={handleOpenChange}
        >
            <DialogTrigger
                asChild
            >
                <Button
                    type="button"
                    variant="outline"
                >
                    <Icon />
                    <span>{config.buttonLabel}</span>
                </Button>
            </DialogTrigger>

            <DialogContent>
                <Form
                    {...config.resolveForm(student)}
                    disableWhileProcessing
                    resetOnError={[config.fieldName]}
                    onSuccess={handleSuccess}
                    onError={() => {
                        setSelectedOptionId(undefined);
                        reloadOptions();
                    }}
                    options={{
                        preserveScroll: true,
                        preserveState: false,
                    }}
                >
                    {({ processing, errors }) => {
                        const fieldError = errors[config.fieldName];
                        const SubmitButton = config.submitMode === 'confirm' ? ConfirmButton : CreateButton;

                        return (
                            <DialogFormLayout>
                                <DialogHeader>
                                    <DialogTitle>{config.title}</DialogTitle>
                                    <DialogDescription>{config.description}</DialogDescription>
                                </DialogHeader>

                                <DialogBody>
                                    <Field>
                                        <Label
                                            htmlFor={config.fieldName}
                                            hasError={!!fieldError}
                                            required
                                        >
                                            {config.label}
                                        </Label>

                                        {renderOptionsControl(fieldError)}

                                        <InputError message={fetchError} />
                                        <InputError message={fieldError} />
                                    </Field>
                                </DialogBody>

                                <DialogFooter>
                                    <DialogClose asChild>
                                        <Button type="button" variant="outline">
                                            <span>إغلاق</span>
                                        </Button>
                                    </DialogClose>
                                    <SubmitButton
                                        title={config.submitTitle ?? 'تـأكيد'}
                                        processing={processing || isLoading}
                                        disabled={processing || isSubmitDisabled}
                                    />
                                </DialogFooter>
                            </DialogFormLayout>
                        );
                    }}
                </Form>
            </DialogContent>
        </Dialog>
    );
}

type EnrollInGradeLevelProps = {
    student: Student;
    gradeLevels?: GradeLevel[];
};

export function EnrollInGradeLevel({ student, gradeLevels }: EnrollInGradeLevelProps) {
    return (
        <EnrollmentDialog
            student={student}
            options={gradeLevels}
            config={enrollGradeLevelConfig}
        />
    );
}

type EnrollInClassroomProps = {
    student: Student;
    classrooms?: Classroom[];
};

export function EnrollInClassroom({ student, classrooms }: EnrollInClassroomProps) {
    return (
        <EnrollmentDialog
            student={student}
            options={classrooms}
            config={enrollClassroomConfig}
        />
    );
}

type TransferClassroomProps = {
    student: Student;
    classrooms?: Classroom[];
};

export function TransferClassroom({ student, classrooms }: TransferClassroomProps) {
    const availableClassrooms = React.useMemo(
        () => (classrooms ?? []).filter((classroom) => classroom.id !== student.classroom?.id),
        [classrooms, student.classroom?.id],
    );

    const currentClassroom = student.classroom?.name
        ? (student.grade_level?.name
            ? `${student.grade_level.name} / ${student.classroom.name}`
            : student.classroom.name)
        : null;

    const config: EnrollmentDialogConfig = currentClassroom
        ? {
            ...transferClassroomConfig,
            description: `الفصل الحالي: ${currentClassroom}. يرجى اختيار الفصل الدراسي الجديد ضمن نفس الصف الدراسي.`,
        }
        : transferClassroomConfig;

    return (
        <EnrollmentDialog
            student={student}
            options={availableClassrooms}
            config={config}
        />
    );
}
