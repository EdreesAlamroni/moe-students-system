import React from "react";

import { Alert, AlertDescription, AlertTitle } from "@/components/ui/alerts/alert";

import { AlertCircleIcon } from "lucide-react";

export default function ValidationErrors({ errors }: { errors: Record<string, string> }) {
    return (
        <>
            {Object.keys(errors).length > 0 && (
                <Alert variant="destructive">
                    <AlertCircleIcon />
                    <AlertTitle>عذراً، حدث خطأ ما!</AlertTitle>
                    <AlertDescription className="gap-1.5">
                        <p>يرجى التحقق من البيانات المدخلة والمحاولة مرة أخرى.</p>
                        <ul className="list-inside list-disc text-sm space-y-1 mt-0.5">
                            {Object.values(errors).map((error, index) => (
                                <li key={index}>{error}</li>
                            ))}
                        </ul>
                    </AlertDescription>
                </Alert>
            )}
        </>
    );
}
