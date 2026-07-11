import React, { useState } from 'react'

import { Form, Head, Link } from "@inertiajs/react";

import { latitudeInputConstraints, libyanPhoneNumberInputConstraints, longitudeInputConstraints } from "@/lib/input-constraints";

import type { EducationMonitor, EducationServicesOffice } from "@/types";

import MainContainer from "@/components/ui/structure/main-container";
import { Card, CardDescription, CardFooter, CardFormContent, CardHeader, CardTitle } from "@/components/ui/structure/card";
import { FormLayout } from "@/components/ui/structure/form-layout";

import RequiredFieldsNote from "@/components/ui/display/required-fields-note";

import Field from "@/components/ui/controls/field";
import { Label } from "@/components/ui/controls/label";
import { Input } from "@/components/ui/controls/input";
import { EmptyOptionsInput } from "@/components/ui/controls/empty-options-input";
import { Checkbox } from "@/components/ui/controls/checkbox";
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/controls/select";
import InputError from "@/components/ui/controls/input-error";

import ValidationErrors from "@/components/ui/alerts/validation-errors";

import { Button } from "@/components/ui/actions/button";
import { UpdateButton } from "@/components/ui/actions/submit-button";

import { LocationPicker } from "@/components/ui/maps/location-picker";

import { ReplyIcon } from "lucide-react";

import { edit, index, show, update } from "@/routes/administration/education-services-offices";

type MonitorOption = Pick<EducationMonitor, "id" | "name">;

type PageProps = {
    office: EducationServicesOffice;
    monitors: MonitorOption[];
}

export default function Edit({ office, monitors }: PageProps) {
    const [selectedMonitorId, setSelectedMonitorId] = useState<string | undefined>(
        office.education_monitor_id?.toString(),
    );
    const [addLocationToMap, setAddLocationToMap] = useState<boolean>(office.add_location_to_map === true);

    return (
        <>
            <Head title="تعديل بيانات مكتب الخدمات التعليمية" />

            <MainContainer>
                <Form
                    {...update.form({ office: office })}
                    disableWhileProcessing
                >
                    {({ processing, errors }) => (
                        <FormLayout>
                            <ValidationErrors errors={errors} />

                            <input
                                type="hidden"
                                name="add_location_to_map"
                                value={addLocationToMap ? "1" : "0"}
                            />

                            <section>
                                <Card>
                                    <CardHeader className="border-b">
                                        <CardTitle>تعديل بيانات مكتب الخدمات التعليمية</CardTitle>
                                        <CardDescription>
                                            <RequiredFieldsNote />
                                        </CardDescription>
                                    </CardHeader>

                                    <CardFormContent>
                                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                                                        onValueChange={setSelectedMonitorId}
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
                                                    htmlFor="name"
                                                    hasError={!!errors.name}
                                                    required
                                                >
                                                    اسم مكتب الخدمات التعليمية
                                                </Label>

                                                <Input
                                                    id="name"
                                                    type="text"
                                                    name="name"
                                                    defaultValue={office.name}
                                                    hasErrors={!!errors.name}
                                                    required
                                                />

                                                <InputError message={errors.name} />
                                            </Field>

                                            <Field>
                                                <Label
                                                    htmlFor="phone_number"
                                                    hasError={!!errors.phone_number}
                                                >
                                                    رقم الهاتف
                                                </Label>

                                                <Input
                                                    id="phone_number"
                                                    type="text"
                                                    name="phone_number"
                                                    defaultValue={office.phone_number ?? ""}
                                                    hasErrors={!!errors.phone_number}
                                                    className="font-mono"
                                                    placeholder="0912345678"
                                                    {...libyanPhoneNumberInputConstraints()}
                                                />

                                                <InputError message={errors.phone_number} />
                                            </Field>

                                            <Field>
                                                <Label
                                                    htmlFor="whatsapp_phone_number"
                                                    hasError={!!errors.whatsapp_phone_number}
                                                >
                                                    رقم هاتف الواتساب
                                                </Label>

                                                <Input
                                                    id="whatsapp_phone_number"
                                                    type="text"
                                                    name="whatsapp_phone_number"
                                                    defaultValue={office.whatsapp_phone_number ?? ""}
                                                    hasErrors={!!errors.whatsapp_phone_number}
                                                    className="font-mono"
                                                    placeholder="0912345678"
                                                    {...libyanPhoneNumberInputConstraints()}
                                                />

                                                <InputError message={errors.whatsapp_phone_number} />
                                            </Field>

                                            <Field className="col-span-full">
                                                <Label
                                                    htmlFor="address"
                                                    hasError={!!errors.address}
                                                >
                                                    العنوان
                                                </Label>

                                                <Input
                                                    id="address"
                                                    type="text"
                                                    name="address"
                                                    defaultValue={office.address ?? ""}
                                                    hasErrors={!!errors.address}
                                                />

                                                <InputError message={errors.address} />
                                            </Field>

                                            <Field className="col-span-full">
                                                <div className="flex items-center gap-x-3">
                                                    <Checkbox
                                                        id="add_location_to_map"
                                                        checked={addLocationToMap}
                                                        onCheckedChange={(checked) => {
                                                            setAddLocationToMap(checked === true);
                                                        }}
                                                    />

                                                    <Label htmlFor="add_location_to_map">
                                                        إضافة الموقع على الخريطة
                                                    </Label>
                                                </div>

                                                <InputError message={errors.add_location_to_map} />
                                            </Field>

                                            {addLocationToMap && (
                                                <>
                                                    <Field className="col-span-full">
                                                        <Label hasError={!!errors.latitude || !!errors.longitude}>
                                                            تحديد الموقع على الخريطة
                                                        </Label>

                                                        <LocationPicker
                                                            latitudeInputId="latitude"
                                                            longitudeInputId="longitude"
                                                            initialLatitude={office.latitude}
                                                            initialLongitude={office.longitude}
                                                        />

                                                        <InputError message={errors.latitude ?? errors.longitude} />
                                                    </Field>

                                                    <Field>
                                                        <Label
                                                            htmlFor="latitude"
                                                            hasError={!!errors.latitude}
                                                            required
                                                        >
                                                            خط العرض
                                                        </Label>

                                                        <Input
                                                            id="latitude"
                                                            type="text"
                                                            name="latitude"
                                                            defaultValue={office.latitude ?? ""}
                                                            hasErrors={!!errors.latitude}
                                                            className="font-mono"
                                                            required
                                                            {...latitudeInputConstraints()}
                                                        />

                                                        <InputError message={errors.latitude} />
                                                    </Field>

                                                    <Field>
                                                        <Label
                                                            htmlFor="longitude"
                                                            hasError={!!errors.longitude}
                                                            required
                                                        >
                                                            خط الطول
                                                        </Label>

                                                        <Input
                                                            id="longitude"
                                                            type="text"
                                                            name="longitude"
                                                            defaultValue={office.longitude ?? ""}
                                                            hasErrors={!!errors.longitude}
                                                            className="font-mono"
                                                            required
                                                            {...longitudeInputConstraints()}
                                                        />

                                                        <InputError message={errors.longitude} />
                                                    </Field>
                                                </>
                                            )}
                                        </div>
                                    </CardFormContent>
                                    <CardFooter className="justify-end gap-x-4 border-t">
                                        <Button variant="outline" className="flex items-center gap-x-2" asChild>
                                            <Link href={show.url({ office: office })}>
                                                <ReplyIcon />
                                                <span>إلغاء الأمر</span>
                                            </Link>
                                        </Button>

                                        <UpdateButton
                                            processing={processing}
                                        />
                                    </CardFooter>
                                </Card>
                            </section>
                        </FormLayout>
                    )}
                </Form>
            </MainContainer>
        </>
    )
}

Edit.layout = (props: PageProps) => ({
    breadcrumbs: [
        {
            title: 'مكاتب الخدمات التعليمية',
            href: index.url(),
        },
        {
            title: 'عرض بيانات مكتب الخدمات التعليمية',
            href: show.url({ office: props.office }),
        },
        {
            title: 'تعديل بيانات مكتب الخدمات التعليمية',
            href: edit.url({ office: props.office }),
        },
    ],
});
