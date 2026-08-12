import type { FormDataErrors } from '@inertiajs/core';

/** Shared shape for user create/edit form fields and validation errors. */
export type UserFormValues = {
    name?: string;
    username?: string;
    email?: string | null;
    password?: string;
    password_confirmation?: string;
    roles?: string;
    scope?: string;
    warehouse_id?: string;
    education_monitor_id?: string;
    education_services_office_id?: string;
    school_id?: string;
    school_period_ids?: string;
};

export type UserFormErrors = FormDataErrors<UserFormValues>;
