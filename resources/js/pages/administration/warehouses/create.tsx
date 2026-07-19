import React, { useState } from 'react'

import { Form, Head, Link } from "@inertiajs/react";

import { latitudeInputConstraints, longitudeInputConstraints } from "@/lib/input-constraints";

import type { EducationMonitor } from "@/types";

import MainContainer from "@/components/ui/structure/main-container";
import { Card, CardFormContent, CardDescription, CardHeader, CardTitle, CardFooter } from "@/components/ui/structure/card";
import { FormLayout } from "@/components/ui/structure/form-layout";

import RequiredFieldsNote from "@/components/ui/display/required-fields-note";

import Field from "@/components/ui/controls/field";
import { Label } from "@/components/ui/controls/label";
import { Input } from "@/components/ui/controls/input";
import { EmptyOptionsInput } from "@/components/ui/controls/empty-options-input";
import { Checkbox } from "@/components/ui/controls/checkbox";
import { MultiSelect } from "@/components/ui/controls/multi-select";
import InputError from "@/components/ui/controls/input-error";

import ValidationErrors from "@/components/ui/alerts/validation-errors";

import { Button } from "@/components/ui/actions/button";
import { CreateButton } from "@/components/ui/actions/submit-button";

import { LocationPicker } from "@/components/ui/maps/location-picker";

import { ReplyIcon } from "lucide-react";

import { index, create, store } from "@/routes/administration/warehouses";


type PageProps = {
    monitors: EducationMonitor[];
}

export default function Create({ monitors }: PageProps) {
    const [selectedMonitorIds, setSelectedMonitorIds] = useState<string[]>([]);
    const [addLocationToMap, setAddLocationToMap] = useState<boolean>(false);

    return (
        <>
            <Head title="إضافة مخزن جديد" />

            <MainContainer>
                <Form
                    {...store.form()}
                    disableWhileProcessing
                >
                    {({ processing, errors }) => (
                        <FormLayout>
                            <ValidationErrors errors={errors} />

                            <input
                                type="hidden"
                                name="education_monitor_ids"
                                value={JSON.stringify(selectedMonitorIds.map(Number))}
                            />
                            <input
                                type="hidden"
                                name="add_location_to_map"
                                value={addLocationToMap ? "1" : "0"}
                            />

                            <section>
                                <Card>
                                    <CardHeader className="border-b">
                                        <CardTitle>إضافة مخزن جديد</CardTitle>
                                        <CardDescription>
                                            <RequiredFieldsNote />
                                        </CardDescription>
                                    </CardHeader>

                                    <CardFormContent>
                                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <Field>
                                                <Label
                                                    htmlFor="name"
                                                    hasError={!!errors.name}
                                                    required
                                                >
                                                    اسم المخزن
                                                </Label>

                                                <Input
                                                    id="name"
                                                    type="text"
                                                    name="name"
                                                    hasError={!!errors.name}
                                                    autoComplete="off"
                                                    required
                                                />

                                                <InputError message={errors.name} />
                                            </Field>

                                            <Field>
                                                <Label
                                                    htmlFor="education_monitor_ids"
                                                    hasError={!!errors.education_monitor_ids}
                                                    required
                                                >
                                                    المُراقبات
                                                </Label>

                                                {monitors.length > 0 ? (
                                                    <MultiSelect
                                                        id="education_monitor_ids"
                                                        options={monitors}
                                                        onValueChange={(value: string[]) => {
                                                            setSelectedMonitorIds(value);
                                                        }}
                                                        placeholder="اختر المُراقبات"
                                                        aria-invalid={!!errors.education_monitor_ids}
                                                    />
                                                ) : (
                                                    <EmptyOptionsInput
                                                        id="education_monitor_ids"
                                                        placeholder="لا توجد مُراقبات متاحة للاختيار"
                                                        aria-invalid={!!errors.education_monitor_ids}
                                                    />
                                                )}

                                                <InputError message={errors.education_monitor_ids} />
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
                                                    hasError={!!errors.address}
                                                    autoComplete="off"
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

                                                    <Label
                                                        htmlFor="add_location_to_map"
                                                        style={{ fontWeight: '500' }}
                                                    >
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
                                                            hasError={!!errors.latitude}
                                                            className="font-mono"
                                                            autoComplete="off"
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
                                                            hasError={!!errors.longitude}
                                                            className="font-mono"
                                                            autoComplete="off"
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
                                            <Link href={index.url()}>
                                                <ReplyIcon />
                                                <span>إلغاء الأمر</span>
                                            </Link>
                                        </Button>

                                        <CreateButton
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

Create.layout = () => ({
    breadcrumbs: [
        {
            title: 'المخازن',
            href: index.url(),
        },
        {
            title: 'إضافة مخزن جديد',
            href: create.url(),
        },
    ],
});
