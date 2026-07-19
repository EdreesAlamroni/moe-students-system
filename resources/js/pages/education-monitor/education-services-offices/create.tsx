import React, { useState } from 'react'

import { Form, Head, Link } from "@inertiajs/react";

import { latitudeInputConstraints, libyanPhoneNumberInputConstraints, longitudeInputConstraints } from "@/lib/input-constraints";

import MainContainer from "@/components/ui/structure/main-container";
import { Card, CardDescription, CardFooter, CardFormContent, CardHeader, CardTitle } from "@/components/ui/structure/card";
import { FormLayout } from "@/components/ui/structure/form-layout";

import RequiredFieldsNote from "@/components/ui/display/required-fields-note";

import Field from "@/components/ui/controls/field";
import { Label } from "@/components/ui/controls/label";
import { Input } from "@/components/ui/controls/input";
import { Checkbox } from "@/components/ui/controls/checkbox";
import InputError from "@/components/ui/controls/input-error";

import ValidationErrors from "@/components/ui/alerts/validation-errors";

import { Button } from "@/components/ui/actions/button";
import { CreateButton } from "@/components/ui/actions/submit-button";

import { LocationPicker } from "@/components/ui/maps/location-picker";

import { ReplyIcon } from "lucide-react";

import { create, index, store } from "@/routes/education-monitor/education-services-offices";

export default function Create() {
    const [addLocationToMap, setAddLocationToMap] = useState<boolean>(false);

    return (
        <>
            <Head title="إضافة مكتب خدمات تعليمية جديد" />

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
                                name="add_location_to_map"
                                value={addLocationToMap ? "1" : "0"}
                            />

                            <section>
                                <Card>
                                    <CardHeader className="border-b">
                                        <CardTitle>إضافة مكتب خدمات تعليمية جديد</CardTitle>
                                        <CardDescription>
                                            <RequiredFieldsNote />
                                        </CardDescription>
                                    </CardHeader>

                                    <CardFormContent>
                                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <Field className="col-span-full">
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
                                                    hasError={!!errors.name}
                                                    autoComplete="off"
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
                                                    hasError={!!errors.phone_number}
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
                                                    hasError={!!errors.whatsapp_phone_number}
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
                                                            required
                                                            autoComplete="off"
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
                                                            required
                                                            autoComplete="off"
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
            title: 'مكاتب الخدمات التعليمية',
            href: index.url(),
        },
        {
            title: 'إضافة مكتب خدمات تعليمية جديد',
            href: create.url(),
        },
    ],
});
