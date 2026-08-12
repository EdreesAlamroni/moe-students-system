import React from 'react';

import type { EducationMonitor, Enum, Warehouse } from '@/types';
import type { SchoolWithPeriods } from '@/components/shared/users/school-user-period-fieldset';
import type { UserFormErrors } from '@/components/shared/users/user-form-types';

import UserContextDetails from '@/components/shared/users/user-context-details';
import SchoolUserPeriodFieldset, { useSchoolPeriodAssignment } from '@/components/shared/users/school-user-period-fieldset';

import Field from '@/components/ui/controls/field';
import { Label } from '@/components/ui/controls/label';
import { EmptyOptionsInput } from '@/components/ui/controls/empty-options-input';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/controls/select';
import InputError from '@/components/ui/controls/input-error';

type AdministrationUserOrganizationFieldsProps = {
    scope: Enum;
    warehouses: Warehouse[];
    monitors: EducationMonitor[];
    errors: UserFormErrors;
};

export default function AdministrationUserOrganizationFields({
    scope,
    warehouses,
    monitors,
    errors,
}: AdministrationUserOrganizationFieldsProps) {
    const isWarehouse = scope.id === 'warehouse';
    const isEducationMonitor = scope.id === 'education_monitor';
    const isEducationServicesOffice = scope.id === 'education_services_office';
    const isSchool = scope.id === 'school';
    const needsMonitor = isEducationMonitor || isEducationServicesOffice || isSchool;

    const [selectedWarehouseId, setSelectedWarehouseId] = React.useState('');
    const [selectedMonitorId, setSelectedMonitorId] = React.useState('');
    const [selectedOfficeId, setSelectedOfficeId] = React.useState('');

    const availableSchools = React.useMemo((): SchoolWithPeriods[] => {
        if (!isSchool || !selectedMonitorId) {
            return [];
        }

        const monitor = monitors.find(
            (item) => item.id.toString() === selectedMonitorId,
        );

        return (monitor?.schools ?? []) as SchoolWithPeriods[];
    }, [isSchool, monitors, selectedMonitorId]);

    const {
        selectedSchoolId,
        selectedPeriodIds,
        handleSchoolChange,
        togglePeriod,
    } = useSchoolPeriodAssignment({ schools: availableSchools });

    const handleMonitorChange = (value: string) => {
        setSelectedMonitorId(value);
        setSelectedOfficeId('');
        handleSchoolChange('');
    };

    const availableOffices = React.useMemo(() => {
        if (!isEducationServicesOffice || !selectedMonitorId) {
            return [];
        }

        const monitor = monitors.find(
            (item) => item.id.toString() === selectedMonitorId,
        );

        return monitor?.offices ?? [];
    }, [isEducationServicesOffice, monitors, selectedMonitorId]);

    return (
        <>
            <UserContextDetails
                items={[
                    {
                        label: 'النطاق',
                        value: scope.name,
                        className: isWarehouse || isEducationMonitor ? undefined : 'col-span-full',
                    },
                ]}
            />

            {isWarehouse && (
                <Field>
                    <Label
                        htmlFor="warehouse_id"
                        hasError={!!errors.warehouse_id}
                        required
                    >
                        المخزن
                    </Label>

                    {warehouses.length > 0 ? (
                        <Select
                            name="warehouse_id"
                            value={selectedWarehouseId}
                            onValueChange={setSelectedWarehouseId}
                        >
                            <SelectTrigger
                                id="warehouse_id"
                                hasError={!!errors.warehouse_id}
                            >
                                <SelectValue placeholder="اختر المخزن" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectGroup>
                                    {warehouses.map((warehouse) => (
                                        <SelectItem
                                            key={warehouse.id}
                                            value={warehouse.id.toString()}
                                        >
                                            {warehouse.name}
                                        </SelectItem>
                                    ))}
                                </SelectGroup>
                            </SelectContent>
                        </Select>
                    ) : (
                        <EmptyOptionsInput
                            id="warehouse_id"
                            placeholder="لا توجد مخازن متاحة للاختيار"
                            aria-invalid={!!errors.warehouse_id}
                        />
                    )}

                    <InputError message={errors.warehouse_id} />
                </Field>
            )}

            {needsMonitor && (
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
            )}

            {isEducationServicesOffice && (
                <Field>
                    <Label
                        htmlFor="education_services_office_id"
                        hasError={!!errors.education_services_office_id}
                        required
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
            )}

            {isSchool && (
                <SchoolUserPeriodFieldset
                    schools={availableSchools}
                    selectedSchoolId={selectedSchoolId}
                    selectedPeriodIds={selectedPeriodIds}
                    onSchoolChange={handleSchoolChange}
                    onPeriodToggle={togglePeriod}
                    errors={errors}
                    enabled={!!selectedMonitorId}
                    disabledPlaceholder="لا توجد مدارس متاحة للاختيار"
                />
            )}
        </>
    );
}
