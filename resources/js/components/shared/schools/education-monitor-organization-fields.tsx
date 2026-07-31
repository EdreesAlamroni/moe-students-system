import React from 'react';

import type { FormDataErrors } from '@inertiajs/core';

import type { EducationServicesOffice, CreateSchoolFormData } from '@/types';

import Field from '@/components/ui/controls/field';
import InputError from '@/components/ui/controls/input-error';
import { Label } from '@/components/ui/controls/label';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/controls/select';

type EducationMonitorOrganizationFieldsProps = {
    offices: Pick<EducationServicesOffice, 'id' | 'name'>[];
    errors: FormDataErrors<CreateSchoolFormData>;
};

export function EducationMonitorOrganizationFields({
    offices,
    errors,
}: EducationMonitorOrganizationFieldsProps) {
    const [selectedOfficeId, setSelectedOfficeId] = React.useState<string>('');

    if (offices.length === 0) {
        return null;
    }

    return (
        <Field className="col-span-full">
            <Label
                htmlFor="education_services_office_id"
                hasError={!!errors.education_services_office_id}
                required
            >
                مكتب الخدمات التعليمية
            </Label>

            <Select
                name="education_services_office_id"
                value={selectedOfficeId}
                onValueChange={setSelectedOfficeId}
            >
                <SelectTrigger
                    id="education_services_office_id"
                    hasError={!!errors.education_services_office_id}
                >
                    <SelectValue placeholder="اختر مكتب الخدمات التعليمية" />
                </SelectTrigger>
                <SelectContent>
                    <SelectGroup>
                        {offices.map((office) => (
                            <SelectItem
                                key={office.id}
                                value={office.id.toString()}
                            >
                                {office.name}
                            </SelectItem>
                        ))}
                    </SelectGroup>
                </SelectContent>
            </Select>

            <InputError message={errors.education_services_office_id} />
        </Field>
    );
}
