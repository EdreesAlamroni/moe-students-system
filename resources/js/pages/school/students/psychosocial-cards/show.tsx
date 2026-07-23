import React from 'react'

import { Head, Link, usePage } from "@inertiajs/react";

import { cn } from "@/lib/utils";

import type { CanPermissions, Student, StudentPsychosocialCard } from "@/types";

import MainContainer from "@/components/ui/structure/main-container";
import ActionsSection from "@/components/ui/structure/actions-section";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/structure/card";
import { Separator } from "@/components/ui/structure/separator";

import Heading from "@/components/ui/display/heading";
import EmptyState from "@/components/ui/display/empty-state";
import { DetailField, DetailFields } from "@/components/ui/display/detail-field";
import { DetailLabel } from "@/components/ui/display/detail-label";
import { DetailValue } from "@/components/ui/display/detail-value";

import { Button } from "@/components/ui/actions/button";

import { BookUserIcon, CheckCircleIcon, NotepadTextIcon, PrinterIcon, SquarePenIcon } from "lucide-react";

import { index as indexStudents, show as showStudents } from "@/routes/school/students";
import { show, edit, print } from "@/routes/school/students/psychosocial-card";
import { StudentClassroomField, StudentGradeLevelField } from "@/components/shared/students/student-enrollment-fields";

type PageProps = {
    student: Student;
    psychosocialCard: StudentPsychosocialCard;
    canAny: boolean;
    can: CanPermissions;
};

export default function Show({ student, psychosocialCard, canAny, can }: PageProps) {
    const { currentAcademicYear } = usePage().props;

    const hasFullData = Boolean(
        psychosocialCard?.guardian_name
        && psychosocialCard?.guardian_date_of_birth
        && psychosocialCard?.guardian_nationality?.name
        && psychosocialCard?.guardian_relationship
        && psychosocialCard?.guardian_phone_number
        && psychosocialCard?.guardian_education_level
        && psychosocialCard?.guardian_job_title
        && psychosocialCard?.guardian_work_place
        && psychosocialCard?.mother_date_of_birth
        && psychosocialCard?.mother_nationality?.name
        && psychosocialCard?.mother_phone_number
        && psychosocialCard?.mother_education_level
        && psychosocialCard?.mother_profession
        && psychosocialCard?.mother_work_place
        && psychosocialCard?.number_of_family_members
        && psychosocialCard?.student_family_order
        && psychosocialCard?.number_of_siblings
        && psychosocialCard?.student_living_situation?.name
        && psychosocialCard?.family_situation_reason?.name
        && psychosocialCard?.residential_area
        && psychosocialCard?.residential_street
        && psychosocialCard?.nearest_landmark
        && psychosocialCard?.previous_activities
        && psychosocialCard?.talents
        && psychosocialCard?.previous_diseases
        && psychosocialCard?.physical_disability_type
        && psychosocialCard?.vision_level?.name
        && psychosocialCard?.hearing_level?.name
        && psychosocialCard?.family_income?.name
        && psychosocialCard?.accommodation_type?.name
        && psychosocialCard?.accommodation_form?.name
        && (psychosocialCard?.behavioral_problems?.length ?? 0) > 0
        && psychosocialCard?.guardian_representative_name
        && psychosocialCard?.guardian_representative_relationship
        && psychosocialCard?.guardian_representative_id_card_number
        && psychosocialCard?.guardian_representative_phone_number
        && psychosocialCard?.guardian_representative_work_place,
    );

    return (
        <>
            <Head title="البطاقة الإجتماعية والنفسية للطالب" />

            <MainContainer changeAcademicYearNotice>
                <section>
                    <header className="flex items-center gap-3 border-b pb-4">
                        <BookUserIcon className="w-4 h-4 shrink-0" />
                        <h1 className="text-sm font-medium text-foreground">
                            البطاقة الإجتماعية والنفسية للطالب
                        </h1>
                    </header>
                </section>

                {canAny && (
                    <ActionsSection>
                        {(can.updatePsychosocialCard && currentAcademicYear?.is_active) && (
                            <Button
                                variant="outline"
                                asChild
                            >
                                <Link href={edit.url({ student: student })}>
                                    <SquarePenIcon />
                                    <span>تعديل بيانات البطاقة</span>
                                </Link>
                            </Button>
                        )}

                        {can.printPsychosocialCard && (
                            <Button
                                variant="outline"
                                disabled={!hasFullData}
                                asChild
                            >
                                <a
                                    href={print.url({ student: student })}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className={cn(!hasFullData && "cursor-not-allowed opacity-50 ")}
                                    onClick={(event: React.MouseEvent<HTMLAnchorElement>): void => {
                                        if (!hasFullData) {
                                            event.preventDefault();
                                            event.stopPropagation();
                                        }
                                    }}
                                    aria-disabled={!hasFullData || undefined}
                                    tabIndex={!hasFullData ? -1 : undefined}
                                >
                                    <PrinterIcon />
                                    <span>طباعة البطاقة</span>
                                </a>
                            </Button>
                        )}
                    </ActionsSection>
                )}

                <StudentDetailsSection
                    student={student}
                />

                <section>
                    <Card>
                        <CardHeader className="border-b">
                            <CardTitle>
                                <BookUserIcon />
                                <span>عرض بيانات البطاقة الإجتماعية والنفسية</span>
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="flex flex-col gap-6">
                            {psychosocialCard ? (
                                <>
                                    {/* Guardian Information */}
                                    <section aria-labelledby="guardian-section" className="space-y-6">
                                        <Heading
                                            variant="small"
                                            title="بيانات ولي الأمر"
                                            description="المعلومات الأساسية لولي أمر الطالب المسؤول عن متابعة شؤونه الدراسية"
                                        />
                                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <DetailField>
                                                <DetailLabel>اسم ولي الأمر</DetailLabel>
                                                <DetailValue value={psychosocialCard.guardian_name || student.father_full_name} />
                                            </DetailField>

                                            <DetailField>
                                                <DetailLabel>تاريخ الميلاد</DetailLabel>
                                                <DetailValue value={psychosocialCard.guardian_date_of_birth} className="font-mono" />
                                            </DetailField>

                                            <DetailField>
                                                <DetailLabel>الجنسية</DetailLabel>
                                                <DetailValue value={psychosocialCard.guardian_nationality?.name} />
                                            </DetailField>

                                            <DetailField>
                                                <DetailLabel>صلة القرابة</DetailLabel>
                                                <DetailValue value={psychosocialCard.guardian_relationship} />
                                            </DetailField>

                                            <DetailField>
                                                <DetailLabel>رقم الهاتف</DetailLabel>
                                                <DetailValue value={psychosocialCard.guardian_phone_number} className="font-mono" />
                                            </DetailField>

                                            <DetailField>
                                                <DetailLabel>المستوى التعليمي</DetailLabel>
                                                <DetailValue value={psychosocialCard.guardian_education_level} />
                                            </DetailField>

                                            <DetailField>
                                                <DetailLabel>المسمى الوظيفي</DetailLabel>
                                                <DetailValue value={psychosocialCard.guardian_job_title} />
                                            </DetailField>

                                            <DetailField>
                                                <DetailLabel>مكان العمل</DetailLabel>
                                                <DetailValue value={psychosocialCard.guardian_work_place} />
                                            </DetailField>
                                        </div>
                                    </section>

                                    <Separator />

                                    {/* Mother Information */}
                                    <section aria-labelledby="mother-section" className="space-y-6">
                                        <Heading
                                            variant="small"
                                            title="بيانات الأم"
                                            description="المعلومات الشخصية والتعليمية والمهنية لأم الطالب"
                                        />
                                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <DetailField>
                                                <DetailLabel>تاريخ الميلاد</DetailLabel>
                                                <DetailValue value={psychosocialCard.mother_date_of_birth} className="font-mono" />
                                            </DetailField>

                                            <DetailField>
                                                <DetailLabel>الجنسية</DetailLabel>
                                                <DetailValue value={psychosocialCard.mother_nationality?.name} />
                                            </DetailField>

                                            <DetailField>
                                                <DetailLabel>رقم الهاتف</DetailLabel>
                                                <DetailValue value={psychosocialCard.mother_phone_number} className="font-mono" />
                                            </DetailField>

                                            <DetailField>
                                                <DetailLabel>المستوى التعليمي</DetailLabel>
                                                <DetailValue value={psychosocialCard.mother_education_level} />
                                            </DetailField>

                                            <DetailField>
                                                <DetailLabel>المهنة</DetailLabel>
                                                <DetailValue value={psychosocialCard.mother_profession} />
                                            </DetailField>

                                            <DetailField>
                                                <DetailLabel>مكان العمل</DetailLabel>
                                                <DetailValue value={psychosocialCard.mother_work_place} />
                                            </DetailField>
                                        </div>
                                    </section>

                                    <Separator />

                                    {/* Family Structure */}
                                    <section aria-labelledby="family-structure-section" className="space-y-6">
                                        <Heading
                                            variant="small"
                                            title="التركيب الأسري"
                                            description="معلومات عن تكوين الأسرة وترتيب الطالب بين أفرادها"
                                        />
                                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <DetailField>
                                                <DetailLabel>عدد أفراد الأسرة</DetailLabel>
                                                <DetailValue value={psychosocialCard.number_of_family_members} className="font-mono" />
                                            </DetailField>

                                            <DetailField>
                                                <DetailLabel>ترتيب الطالب في الأسرة</DetailLabel>
                                                <DetailValue value={psychosocialCard.student_family_order} className="font-mono" />
                                            </DetailField>

                                            <DetailField>
                                                <DetailLabel>عدد الإخوة</DetailLabel>
                                                <DetailValue value={psychosocialCard.number_of_siblings} className="font-mono" />
                                            </DetailField>
                                        </div>
                                    </section>

                                    <Separator />

                                    {/* Living Situation */}
                                    <section aria-labelledby="living-situation-section" className="space-y-6">
                                        <Heading
                                            variant="small"
                                            title="الوضع السكني"
                                            description="تفاصيل مكان السكن والعنوان الكامل والأنشطة والمواهب التي يمارسها الطالب"
                                        />
                                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <DetailField>
                                                <DetailLabel>معيشة الطالب</DetailLabel>
                                                <DetailValue value={psychosocialCard.student_living_situation?.name} />
                                            </DetailField>

                                            <DetailField>
                                                <DetailLabel>السبب</DetailLabel>
                                                <DetailValue value={psychosocialCard.family_situation_reason?.name} />
                                            </DetailField>

                                            <DetailField>
                                                <DetailLabel>المنطقة</DetailLabel>
                                                <DetailValue value={psychosocialCard.residential_area} />
                                            </DetailField>

                                            <DetailField>
                                                <DetailLabel>الشارع</DetailLabel>
                                                <DetailValue value={psychosocialCard.residential_street} />
                                            </DetailField>

                                            <DetailField className="col-span-full">
                                                <DetailLabel>أقرب نقطة دالة</DetailLabel>
                                                <DetailValue value={psychosocialCard.nearest_landmark} />
                                            </DetailField>

                                            <DetailField className="col-span-full">
                                                <DetailLabel>النشاطات التي شارك فيها الطالب سابقاً</DetailLabel>
                                                <DetailValue value={psychosocialCard.previous_activities} />
                                            </DetailField>

                                            <DetailField className="col-span-full">
                                                <DetailLabel>المواهب التي يتمتع بها الطالب</DetailLabel>
                                                <DetailValue value={psychosocialCard.talents} />
                                            </DetailField>
                                        </div>
                                    </section>

                                    <Separator />

                                    {/* Health Information */}
                                    <section aria-labelledby="health-section" className="space-y-6">
                                        <Heading
                                            variant="small"
                                            title="البيانات الصحية"
                                            description="معلومات عن الحالة الصحية للطالب والأمراض السابقة والإعاقات إن وجدت"
                                        />
                                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <DetailField className="col-span-full">
                                                <DetailLabel>الأمراض التي سبق الإصابة بها</DetailLabel>
                                                <DetailValue value={psychosocialCard.previous_diseases} />
                                            </DetailField>

                                            <DetailField className="col-span-full">
                                                <DetailLabel>نوع الإعاقة الجسمية إن وُجدت</DetailLabel>
                                                <DetailValue value={psychosocialCard.physical_disability_type} />
                                            </DetailField>

                                            <DetailField>
                                                <DetailLabel>مستوى النظر</DetailLabel>
                                                <DetailValue value={psychosocialCard.vision_level?.name} />
                                            </DetailField>

                                            <DetailField>
                                                <DetailLabel>السمع</DetailLabel>
                                                <DetailValue value={psychosocialCard.hearing_level?.name} />
                                            </DetailField>
                                        </div>
                                    </section>

                                    <Separator />

                                    {/* Accommodation and Income */}
                                    <section aria-labelledby="accommodation-section" className="space-y-6">
                                        <Heading
                                            variant="small"
                                            title="الحالة الاقتصادية ونوع السكن للأسرة"
                                            description="معلومات عن الوضع الاقتصادي للأسرة ومواصفات السكن"
                                        />
                                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <DetailField>
                                                <DetailLabel>دخل الأسرة</DetailLabel>
                                                <DetailValue value={psychosocialCard.family_income?.name} />
                                            </DetailField>

                                            <DetailField>
                                                <DetailLabel>نوع السكن</DetailLabel>
                                                <DetailValue value={psychosocialCard.accommodation_type?.name} />
                                            </DetailField>

                                            <DetailField>
                                                <DetailLabel>مواصفات السكن</DetailLabel>
                                                <DetailValue value={psychosocialCard.accommodation_form?.name} />
                                            </DetailField>
                                        </div>
                                    </section>

                                    <Separator />

                                    {/* Behavioral Problems */}
                                    {psychosocialCard.behavioral_problems && psychosocialCard.behavioral_problems.length > 0 && (
                                        <>
                                            <section aria-labelledby="behavioral-section" className="space-y-6">
                                                <Heading
                                                    variant="small"
                                                    title="المشاكل السلوكية"
                                                    description="المشاكل (الاضطرابات) السلوكية التي يعاني منها الطالب من وجهة نظر ولي الأمر خلال تواجده في البيت"
                                                />
                                                <div className="space-y-4">
                                                    <div className="border rounded-lg divide-y overflow-hidden">
                                                        <div className="grid grid-cols-12 gap-4 p-4 bg-muted/50 text-sm font-semibold border-b">
                                                            <div className="col-span-5">السلوك</div>
                                                            <div className="col-span-2 text-center">نعم</div>
                                                            <div className="col-span-5">ملاحظات</div>
                                                        </div>
                                                        {psychosocialCard.behavioral_problems.map((problem) => (
                                                            <div
                                                                key={problem.behavior}
                                                                className="grid grid-cols-12 gap-4 p-4 items-center hover:bg-muted/30 transition-colors"
                                                            >
                                                                <div className="col-span-5 flex items-center text-sm">
                                                                    {problem.label}
                                                                </div>
                                                                <div className="col-span-2 flex justify-center">
                                                                    <CheckCircleIcon className="w-4 h-4" />
                                                                </div>
                                                                <div className="col-span-5">
                                                                    <DetailValue value={problem.notes} fallback="لا توجد ملاحظات" />
                                                                </div>
                                                            </div>
                                                        ))}
                                                    </div>
                                                </div>
                                            </section>

                                            <Separator />
                                        </>
                                    )}

                                    {/* Guardian Representative */}
                                    <section aria-labelledby="representative-section" className="space-y-6">
                                        <Heading
                                            variant="small"
                                            title="الممثل عن ولي الأمر"
                                            description="في حالة تعذر ولي الأمر عن زيارة المدرسة فمن ينوب عنه"
                                        />
                                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <DetailField>
                                                <DetailLabel>الاسم</DetailLabel>
                                                <DetailValue value={psychosocialCard.guardian_representative_name} />
                                            </DetailField>

                                            <DetailField>
                                                <DetailLabel>صلة القرابة</DetailLabel>
                                                <DetailValue value={psychosocialCard.guardian_representative_relationship} />
                                            </DetailField>

                                            <DetailField>
                                                <DetailLabel>رقم بطاقة الهوية</DetailLabel>
                                                <DetailValue value={psychosocialCard.guardian_representative_id_card_number} className="font-mono" />
                                            </DetailField>

                                            <DetailField>
                                                <DetailLabel>رقم الهاتف</DetailLabel>
                                                <DetailValue value={psychosocialCard.guardian_representative_phone_number} className="font-mono" />
                                            </DetailField>

                                            <DetailField>
                                                <DetailLabel>مكان العمل</DetailLabel>
                                                <DetailValue value={psychosocialCard.guardian_representative_work_place} />
                                            </DetailField>
                                        </div>
                                    </section>
                                </>
                            ) : (
                                <EmptyState />
                            )}
                        </CardContent>
                    </Card>
                </section>
            </MainContainer>
        </>
    );
}

Show.layout = (props: PageProps) => ({
    breadcrumbs: [
        {
            title: 'الطلاب',
            href: indexStudents.url(),
        },
        {
            title: 'عرض بيانات الطالب',
            href: showStudents.url({ student: props.student }),
        },
        {
            title: 'البطاقة الإجتماعية والنفسية',
            href: show.url({ student: props.student }),
        },
    ],
});


function StudentDetailsSection({ student }: { student: Student }) {
    return (
        <section>
            <Card>
                <CardHeader className="border-b">
                    <CardTitle>
                        <NotepadTextIcon />
                        <span>عرض بيانات الطالب</span>
                    </CardTitle>
                </CardHeader>
                <CardContent className="flex flex-col gap-6">
                    <DetailFields columns={2}>
                        <StudentGradeLevelField student={student} />

                        <StudentClassroomField student={student} />

                        <DetailField>
                            <DetailLabel>اسم الطالب</DetailLabel>
                            <DetailValue value={student.full_name} />
                        </DetailField>

                        <DetailField>
                            <DetailLabel>الجنسية</DetailLabel>
                            <DetailValue value={student.nationality?.name} />
                        </DetailField>

                        {student.is_libyan && (
                            <>
                                <DetailField>
                                    <DetailLabel>الرقم الوطني</DetailLabel>
                                    <DetailValue value={student.national_id} className="font-mono" />
                                </DetailField>

                                <DetailField>
                                    <DetailLabel>رقم القيد</DetailLabel>
                                    <DetailValue value={student.family_registration_number} className="font-mono" />
                                </DetailField>
                            </>
                        )}
                    </DetailFields>
                </CardContent>
            </Card>
        </section>
    );
}
