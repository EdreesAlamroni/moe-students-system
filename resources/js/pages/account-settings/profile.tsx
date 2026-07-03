import { Head } from '@inertiajs/react';

import { ProfilePageContent } from '@/components/account-settings/profile-form';

export default function Profile() {
    return (
        <>
            <Head title="إعدادات الملف الشخصي" />

            <h1 className="sr-only">إعدادات الملف الشخصي</h1>

            <ProfilePageContent />
        </>
    );
}
