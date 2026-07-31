import React from 'react';

import type { FormDataErrors } from '@inertiajs/core';

import type { EducationMonitor, EducationServicesOffice, CreateSchoolFormData } from '@/types';

import Field from '@/components/ui/controls/field';
import { EmptyOptionsInput } from '@/components/ui/controls/empty-options-input';
import InputError from '@/components/ui/controls/input-error';
import { Label } from '@/components/ui/controls/label';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/controls/select';

type MonitorWithOffices = Pick<EducationMonitor, 'id' | 'name'> & {
    offices: Pick<EducationServicesOffice, 'id' | 'name'>[];
};

type AdministrationOrganizationFieldsProps = {
    monitors: MonitorWithOffices[];
    errors: FormDataErrors<CreateSchoolFormData>;
};

export function AdministrationOrganizationFields({
    monitors,
    errors,
}: AdministrationOrganizationFieldsProps) {
    const [selectedMonitorId, setSelectedMonitorId] = React.useState<string>('');
    const [selectedOfficeId, setSelectedOfficeId] = React.useState<string>('');

    const availableOffices = React.useMemo(() => {
        if (!selectedMonitorId) {
            return [];
        }

        return monitors.find((monitor) => monitor.id.toString() === selectedMonitorId)?.offices ?? [];
    }, [monitors, selectedMonitorId]);

    const handleMonitorChange = (value: string) => {
        setSelectedMonitorId(value);
        setSelectedOfficeId('');
    };

    return (
        <>
            <Field>
                <Label
                    htmlFor="education_monitor_id"
                    hasError={!!errors.education_monitor_id}
                    required
                >
                    المُراقبة
                </Label>

                {monitors.length > 0 ? (
                    <Select
                        name="education_monitor_id"
                        value={selectedMonitorId}
                        onValueChange={handleMonitorChange}
                    >
                        <SelectTrigger
                            id="education_monitor_id"
                            hasError={!!errors.education_monitor_id}
                        >
                            <SelectValue placeholder="اختر المُراقبة" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectGroup>
                                {monitors.map((monitor) => (
                                    <SelectItem
                                        key={monitor.id}
                                        value={monitor.id.toString()}
                                    >
                                        {monitor.name}
                                    </SelectItem>
                                ))}
                            </SelectGroup>
                        </SelectContent>
                    </Select>
                ) : (
                    <EmptyOptionsInput
                        id="education_monitor_id"
                        placeholder="لا توجد مُراقبات متاحة للاختيار"
                        aria-invalid={!!errors.education_monitor_id}
                    />
                )}

                <InputError message={errors.education_monitor_id} />
            </Field>

            <Field>
                <Label
                    htmlFor="education_services_office_id"
                    hasError={!!errors.education_services_office_id}
                    required={availableOffices.length > 0}
                >
                    مكتب الخدمات التعليمية
                </Label>

                {selectedMonitorId ? (
                    availableOffices.length > 0 ? (
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
                                    {availableOffices.map((office) => (
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
                    ) : (
                        <EmptyOptionsInput
                            id="education_services_office_id"
                            placeholder="لا توجد مكاتب خدمات تعليمية متاحة للاختيار"
                            aria-invalid={!!errors.education_services_office_id}
                        />
                    )
                ) : (
                    <EmptyOptionsInput
                        id="education_services_office_id"
                        placeholder="يرجى اختيار المُراقبة أولاً"
                        aria-invalid={!!errors.education_services_office_id}
                    />
                )}

                <InputError message={errors.education_services_office_id} />
            </Field>
        </>
    );
}
