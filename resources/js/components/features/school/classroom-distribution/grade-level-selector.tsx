import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/structure/card';

import type { GradeLevel } from '@/types';

import Field from '@/components/ui/controls/field';
import { Label } from '@/components/ui/controls/label';
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/controls/select';

import { GraduationCapIcon } from 'lucide-react';

type GradeLevelSelectorProps = {
    gradeLevels: GradeLevel[];
    selectedGradeLevelId: number | null;
    onGradeChange: (value: string) => void;
};

export default function GradeLevelSelector({
    gradeLevels,
    selectedGradeLevelId,
    onGradeChange,
}: GradeLevelSelectorProps) {
    return (
        <section>
            <Card>
                <CardHeader className="border-b">
                    <CardTitle>
                        <GraduationCapIcon />
                        <span>الصف الدراسي</span>
                    </CardTitle>
                    <CardDescription>
                        اختر الصف الدراسي لعرض الفصول الدراسية والطلاب غير
                        المعيَّنين في فصل ضمن السنة الدراسية الحالية.
                    </CardDescription>
                </CardHeader>

                <CardContent>
                    <Field className="max-w-md">
                        <Label htmlFor="grade_level_select">الصف الدراسي</Label>

                        <Select
                            value={
                                selectedGradeLevelId !== null
                                    ? String(selectedGradeLevelId)
                                    : ''
                            }
                            onValueChange={onGradeChange}
                        >
                            <SelectTrigger id="grade_level_select">
                                <SelectValue placeholder="اختر الصف الدراسي" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectGroup>
                                    {gradeLevels.map((grade: GradeLevel) => (
                                        <SelectItem
                                            key={grade.id}
                                            value={String(grade.id)}
                                        >
                                            {grade.name}
                                        </SelectItem>
                                    ))}
                                </SelectGroup>
                            </SelectContent>
                        </Select>
                    </Field>
                </CardContent>
            </Card>
        </section>
    );
}
