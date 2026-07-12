import React, { useMemo, useState } from 'react'

import { Form, Head, Link } from "@inertiajs/react";

import { latitudeInputConstraints, libyanPhoneNumberInputConstraints, longitudeInputConstraints } from "@/lib/input-constraints";

import type { EducationMonitor, Municipal } from "@/types";

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

import { edit, index, show, update } from "@/routes/administration/education-monitors";

type MunicipalOption = Pick<Municipal, "id" | "name">;

type PageProps = {
    monitor: EducationMonitor;
    municipals: MunicipalOption[];
}

export default function Edit({ monitor, municipals }: PageProps) {
    const [selectedMunicipalId, setSelectedMunicipalId] = useState<string | undefined>(
        monitor.municipal_id?.toString(),
    );
    const [addLocationToMap, setAddLocationToMap] = useState<boolean>(monitor.add_location_to_map === true);

    const selectedMunicipal = useMemo(() => {
        if (!selectedMunicipalId) {
            return null;
        }

        return municipals.find((municipal) => municipal.id.toString() === selectedMunicipalId) ?? null;
    }, [municipals, selectedMunicipalId]);

    const generatedName = selectedMunicipal
        ? `مُراقبة التّربية والتّعليم ${selectedMunicipal.name}`
        : monitor.name;

    return (
        <>
            <Head title="تعديل بيانات المُراقبة" />

            <MainContainer>
                <Form
                    {...update.form({ monitor: monitor })}
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
                                        <CardTitle>تعديل بيانات المُراقبة</CardTitle>
                                        <CardDescription>
                                            <RequiredFieldsNote />
                                        </CardDescription>
                                    </CardHeader>

                                    <CardFormContent>
                                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <Field>
                                                <Label
                                                    htmlFor="municipal_id"
                                                    hasError={!!errors.municipal_id}
                                                    required
                                                >
                                                    البلدية
                                                </Label>

                                                {municipals.length > 0 ? (
                                                    <Select
                                                        name="municipal_id"
                                                        value={selectedMunicipalId}
                                                        onValueChange={setSelectedMunicipalId}
                                                    >
                                                        <SelectTrigger
                                                            id="municipal_id"
                                                            hasError={!!errors.municipal_id}
                                                        >
                                                            <SelectValue placeholder="اختر البلدية" />
                                                        </SelectTrigger>
                                                        <SelectContent>
                                                            <SelectGroup>
                                                                {municipals.map((municipal) => (
                                                                    <SelectItem
                                                                        key={municipal.id}
                                                                        value={municipal.id.toString()}
                                                                    >
                                                                        {municipal.name}
                                                                    </SelectItem>
                                                                ))}
                                                            </SelectGroup>
                                                        </SelectContent>
                                                    </Select>
                                                ) : (
                                                    <EmptyOptionsInput
                                                        id="municipal_id"
                                                        placeholder="لا توجد بلديات متاحة للاختيار"
                                                        aria-invalid={!!errors.municipal_id}
                                                    />
                                                )}

                                                <InputError message={errors.municipal_id} />
                                            </Field>

                                            <Field>
                                                <Label htmlFor="name">
                                                    اسم المُراقبة
                                                </Label>

                                                <Input
                                                    id="name"
                                                    type="text"
                                                    value={generatedName}
                                                    placeholder={selectedMunicipalId ? "اسم المُراقبة" : "يرجى اختيار البلدية أولاً"}
                                                    disabled
                                                />
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
                                                    defaultValue={monitor.phone_number ?? ""}
                                                    hasErrors={!!errors.phone_number}
                                                    className="font-mono"
                                                    placeholder="0912345678"
                                                    autoComplete="off"
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
                                                    defaultValue={monitor.whatsapp_phone_number ?? ""}
                                                    hasErrors={!!errors.whatsapp_phone_number}
                                                    className="font-mono"
                                                    placeholder="0912345678"
                                                    autoComplete="off"
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
                                                    defaultValue={monitor.address ?? ""}
                                                    hasErrors={!!errors.address}
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
                                                            initialLatitude={monitor.latitude}
                                                            initialLongitude={monitor.longitude}
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
                                                            defaultValue={monitor.latitude ?? ""}
                                                            hasErrors={!!errors.latitude}
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
                                                            defaultValue={monitor.longitude ?? ""}
                                                            hasErrors={!!errors.longitude}
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
                                            <Link href={show.url({ monitor: monitor })}>
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
            title: 'المُراقبات',
            href: index.url(),
        },
        {
            title: 'عرض بيانات المُراقبة',
            href: show.url({ monitor: props.monitor }),
        },
        {
            title: 'تعديل بيانات المُراقبة',
            href: edit.url({ monitor: props.monitor }),
        },
    ],
});
