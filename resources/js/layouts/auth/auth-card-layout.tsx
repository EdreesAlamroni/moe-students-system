import type { PropsWithChildren } from 'react';

import { Link } from '@inertiajs/react';

import { welcome } from '@/routes';

import AppLogoIcon from '@/components/layouts/app-logo-icon';

import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/structure/card';

export default function AuthCardLayout({
    children,
    title,
    description,
}: PropsWithChildren<{
    name?: string;
    title?: string;
    description?: string;
}>) {
    return (
        <div className="flex min-h-svh flex-col items-center justify-center gap-6 bg-muted p-6 md:p-10">
            <div className="flex w-full max-w-md flex-col gap-3">
                <Link
                    href={welcome.url()}
                    className="flex items-center gap-2 self-center font-medium"
                >
                    <div className="flex h-24 w-24 items-center justify-center md:h-28 md:w-28">
                        <AppLogoIcon className="size-auto fill-current text-black" />
                    </div>
                </Link>

                <div className="flex flex-col gap-6">
                    <Card>
                        <CardHeader className="px-10 pt-6 pb-0 text-center">
                            <CardTitle className="text-lg">{title}</CardTitle>
                            <CardDescription>{description}</CardDescription>
                        </CardHeader>
                        <CardContent className="px-10 py-6">
                            {children}
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    );
}
