import type { PropsWithChildren } from 'react';

import { Link } from '@inertiajs/react';

import { useNavItemActive } from '@/hooks/use-nav-item-active';
import { useNavigation } from '@/hooks/use-navigation';

import { cn, toUrl } from '@/lib/utils';

import { Icon } from '@/components/ui/display/icon';
import { Separator } from '@/components/ui/structure/separator';

import Heading from '@/components/ui/display/heading';

import { Button } from '@/components/ui/actions/button';

export default function AccountSettingsLayout({ children }: PropsWithChildren) {
    const { account } = useNavigation();
    const isNavItemActive = useNavItemActive();

    return (
        <div className="px-4 py-6">
            <Heading
                title="إعدادات الحساب"
                description="إدارة الملف الشخصي وإعدادات الحساب"
            />

            <div className="flex flex-col lg:flex-row lg:space-x-12">
                <aside className="w-full max-w-xl lg:w-48">
                    <nav
                        className="flex flex-col space-y-1 space-x-0"
                        aria-label="إعدادات الحساب"
                    >
                        {account.tabs.map((item) => (
                            <Button
                                key={toUrl(item.href)}
                                size="sm"
                                variant="ghost"
                                asChild
                                className={cn('w-full justify-start text-sm font-medium', {
                                    'bg-muted': isNavItemActive(item),
                                })}
                            >
                                <Link href={item.href}>
                                    <Icon iconNode={item.icon} />
                                    {item.title}
                                </Link>
                            </Button>
                        ))}
                    </nav>
                </aside>

                <Separator className="my-6 lg:hidden" />

                <div className="flex-1 md:max-w-2xl">
                    <section className="max-w-xl space-y-12">
                        {children}
                    </section>
                </div>
            </div>
        </div>
    );
}
