import { Head } from '@inertiajs/react';

import { SecurityPageContent } from '@/components/account-settings/security-form';

export default function Security() {
    return (
        <>
            <Head title="إعدادات الحماية والأمان" />

            <h1 className="sr-only">إعدادات الحماية والأمان</h1>

            <SecurityPageContent />
        </>
    );
}
