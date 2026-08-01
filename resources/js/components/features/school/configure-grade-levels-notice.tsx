import { Link, usePage } from "@inertiajs/react";

import { Alert, AlertAction, AlertDescription, AlertTitle } from "@/components/ui/alerts/alert";

import { Button } from "@/components/ui/actions/button";

import { TriangleAlertIcon } from "lucide-react";

import AddGradeLevelsDialog from "@/components/features/school/add-grade-levels-dialog";

import { index as gradeLevelsIndex } from "@/routes/school/grade-levels";

export default function ConfigureGradeLevelsNotice() {
    const { dashboard, organization } = usePage().props;

    const school = organization?.type === "school" ? organization : null;
    const isSchoolDashboard = dashboard?.key === "school";
    const availableGradeLevels = school?.available_grade_levels ?? [];

    if (!isSchoolDashboard || availableGradeLevels.length === 0) {
        return null;
    }

    return (
        <Alert
            variant="warning"
        >
            <TriangleAlertIcon />
            <AlertTitle>يجب إعداد الصفوف الدراسية بالمدرسة</AlertTitle>
            <AlertDescription>
                لم يتم إضافة أي صف دراسي للمدرسة في السنة الدراسية الحالية، ويجب إعدادها قبل المتابعة في إضافة
                الفصول الدراسية وتسجيل الطلاب، أو مراجعتها من{" "}
                <Link href={gradeLevelsIndex.url()}>صفحة الصفوف الدراسية</Link>.
            </AlertDescription>
            <AlertAction>
                <AddGradeLevelsDialog gradeLevels={availableGradeLevels}>
                    <Button
                        size="xs"
                        className="bg-amber-700 hover:bg-amber-700/80"
                    >
                        إضافة
                    </Button>
                </AddGradeLevelsDialog>
            </AlertAction>
        </Alert>
    );
}
