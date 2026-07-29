import React from 'react'

import { Form, Head } from "@inertiajs/react";

import type { CanPermissions, Classroom } from "@/types";

import MainContainer from "@/components/ui/structure/main-container";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/structure/card";
import ActionsSection from "@/components/ui/structure/actions-section";

import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/controls/select";
import { EmptyOptionsInput } from "@/components/ui/controls/empty-options-input";

import { Button } from "@/components/ui/actions/button";

import { Alert, AlertDescription, AlertTitle } from "@/components/ui/alerts/alert";

import { AlertCircleIcon, PrinterIcon, SheetIcon } from "lucide-react";

import { index, print } from "@/routes/school/reports/attendance";

type MonthOption = {
    id: number;
    name: string;
}

type PageProps = {
    classrooms: Classroom[];
    months: MonthOption[];
    canAny: boolean;
    can: CanPermissions;
}

export default function Index({ classrooms, months, canAny, can }: PageProps) {
    const [classroomId, setClassroomId] = React.useState<string>("");
    const [month, setMonth] = React.useState<string>("");

    const isReady: boolean = classroomId !== "" && month !== "";

    return (
        <>
            <Head title="تقرير الغياب" />

            <MainContainer showAcademicYearNotice>
                <section>
                    <Alert variant="warning">
                        <AlertCircleIcon />
                        <AlertTitle>تنبيه مهم!</AlertTitle>
                        <AlertDescription>
                            هذا التقرير مخصص لطباعة كشف الغياب الخاص بالطلاب خلال شهر معين.
                            يُرجى تحديد الحقول التالية المبيّنة بدقة لإتمام عملية الطباعة بنجاح.
                        </AlertDescription>
                    </Alert>
                </section>

                <section>
                    <Form
                        {...index.form()}
                    >
                        <Card>
                            <CardHeader className="border-b">
                                <CardTitle>
                                    <SheetIcon />
                                    <span>تقرير الغياب</span>
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                    {classrooms.length > 0 ? (
                                        <Select
                                            onValueChange={setClassroomId}
                                        >
                                            <SelectTrigger>
                                                <SelectValue placeholder="اختر الفصل الدراسي" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectGroup>
                                                    {classrooms.map((classroom) => (
                                                        <SelectItem
                                                            key={classroom.uuid}
                                                            value={classroom.id.toString()}
                                                        >
                                                            {classroom.name}
                                                        </SelectItem>
                                                    ))}
                                                </SelectGroup>
                                            </SelectContent>
                                        </Select>
                                    ) : (
                                        <EmptyOptionsInput
                                            placeholder="لا توجد فصول دراسية متاحة للاختيار"
                                        />
                                    )}


                                    <Select
                                        onValueChange={setMonth}
                                    >
                                        <SelectTrigger>
                                            <SelectValue placeholder="اختر الشهر" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectGroup>
                                                {months.map((month) => (
                                                    <SelectItem
                                                        key={month.id}
                                                        value={month.id.toString()}
                                                    >
                                                        {month.name}
                                                    </SelectItem>
                                                ))}
                                            </SelectGroup>
                                        </SelectContent>
                                    </Select>
                                </div>
                            </CardContent>
                        </Card>
                    </Form>
                </section>

                {canAny && (
                    <ActionsSection>
                        {can.print && (
                            <Button
                                variant="default"
                                disabled={!isReady}
                                asChild
                            >
                                {isReady ? (
                                    <a href={print.url({
                                        query: {
                                            classroom_id: classroomId,
                                            month: month,
                                        }
                                    })} target="_blank">
                                        <PrinterIcon />
                                        <span>طباعة التقرير</span>
                                    </a>
                                ) : (
                                    <span className="flex cursor-not-allowed items-center gap-1 opacity-50">
                                        <PrinterIcon />
                                        <span>طباعة التقرير</span>
                                    </span>
                                )}
                            </Button>
                        )}
                    </ActionsSection>
                )}

            </MainContainer>
        </>
    )
}

Index.layout = () => ({
    breadcrumbs: [
        {
            title: 'تقرير الغياب',
            href: index.url(),
        },
    ],
});
