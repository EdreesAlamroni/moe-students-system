import { Head, Link } from '@inertiajs/react';
import type { LucideIcon } from 'lucide-react';
import {
    ArrowLeftIcon,
    BuildingIcon,
    LandmarkIcon,
    SchoolIcon,
    ShieldCheckIcon,
    UserRoundCogIcon,
    WarehouseIcon,
} from 'lucide-react';

import AppLogoIcon from '@/components/layouts/app-logo-icon';
import { Badge } from '@/components/ui/display/badge';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/structure/card';
import { Separator } from '@/components/ui/structure/separator';
import { cn } from '@/lib/utils';
import { login as administrationLogin } from '@/routes/administration';
import { login as educationMonitorLogin } from '@/routes/education-monitor';
import { login as educationServicesOfficeLogin } from '@/routes/education-services-office';
import { login as schoolLogin } from '@/routes/school';
import { login as warehouseLogin } from '@/routes/warehouse';

type Portal = {
    key: string;
    title: string;
    description: string;
    href: string;
    icon: LucideIcon;
};

const portals: Portal[] = [
    {
        key: 'administration',
        title: 'الإدارة',
        description: 'إدارة البيانات المركزية، المستخدمين، المدارس، والمراجع الأساسية للمنظومة.',
        href: administrationLogin.url(),
        icon: UserRoundCogIcon,
    },
    {
        key: 'warehouse',
        title: 'المخزن',
        description: 'متابعة توزيع الكتب المدرسية وإدارة المخزون عبر المراقبات والمدارس.',
        href: warehouseLogin.url(),
        icon: WarehouseIcon,
    },
    {
        key: 'education-monitor',
        title: 'المُراقبة',
        description: 'متابعة المدارس ومكاتب الخدمات التعليمية ضمن نطاق المراقبة التعليمية.',
        href: educationMonitorLogin.url(),
        icon: LandmarkIcon,
    },
    {
        key: 'education-services-office',
        title: 'مكتب الخدمات التعليمية',
        description: 'إدارة المدارس والطلاب والعمليات اليومية ضمن نطاق مكتب الخدمات التعليمية.',
        href: educationServicesOfficeLogin.url(),
        icon: BuildingIcon,
    },
    {
        key: 'school',
        title: 'المدرسة',
        description: 'إدارة شؤون المدرسة، الطلاب، الفصول الدراسية، والسجلات الأكاديمية.',
        href: schoolLogin.url(),
        icon: SchoolIcon,
    },
];

function PortalCard({ portal }: { portal: Portal }) {
    const Icon = portal.icon;

    return (
        <Link
            href={portal.href}
            prefetch
            className={cn(
                'group block h-full rounded-none outline-none',
                'focus-visible:ring-2 focus-visible:ring-ring/40 focus-visible:ring-offset-2 focus-visible:ring-offset-background',
            )}
            aria-label={`تسجيل الدخول إلى لوحة تحكم ${portal.title}`}
        >
            <Card
                className={cn(
                    'relative h-full justify-between overflow-hidden bg-card/90 backdrop-blur-sm',
                    'transition-all duration-200 ease-out',
                    'group-hover:-translate-y-0.5 group-hover:bg-card group-hover:shadow-lg group-hover:ring-primary/40',
                    'group-focus-visible:-translate-y-0.5 group-focus-visible:shadow-lg group-focus-visible:ring-primary/40',
                )}
            >
                <div
                    className="absolute inset-x-0 top-0 h-0.5 origin-right scale-x-0 bg-linear-to-l from-primary via-primary to-[oklch(0.72_0.1_82)] transition-transform duration-300 ease-out group-hover:scale-x-100 group-focus-visible:scale-x-100"
                    aria-hidden
                />

                <CardHeader className="gap-4">
                    <div className="flex items-start gap-4">
                        <span
                            className={cn(
                                'inline-flex size-11 shrink-0 items-center justify-center',
                                'bg-primary/8 text-primary ring-1 ring-primary/10',
                                'transition-all duration-200',
                                'group-hover:bg-primary group-hover:text-primary-foreground group-hover:ring-primary/20',
                                'group-focus-visible:bg-primary group-focus-visible:text-primary-foreground',
                            )}
                            aria-hidden
                        >
                            <Icon className="size-5 stroke-[1.5]" />
                        </span>

                        <div className="min-w-0 flex-1 space-y-1.5">
                            <CardTitle className="text-sm tracking-wider normal-case">
                                {portal.title}
                            </CardTitle>
                            <CardDescription className="text-sm leading-relaxed">
                                {portal.description}
                            </CardDescription>
                        </div>
                    </div>
                </CardHeader>

                <CardContent>
                    <span className="inline-flex items-center gap-1.5 text-xs font-semibold tracking-wider text-primary uppercase">
                        <span>تسجيل الدخول</span>
                        <ArrowLeftIcon className="size-3.5 transition-transform duration-200 group-hover:-translate-x-1 group-focus-visible:-translate-x-1" />
                    </span>
                </CardContent>
            </Card>
        </Link>
    );
}

export default function Welcome() {
    return (
        <>
            <Head title="الرئيسية" />

            <div className="relative flex min-h-svh flex-col overflow-hidden bg-background text-foreground">
                <div
                    className="pointer-events-none absolute inset-0"
                    aria-hidden
                >
                    <div className="absolute inset-0 bg-muted/70" />
                    <div className="absolute end-[-10%] -top-32 size-[28rem] rounded-full bg-primary/[0.07] blur-3xl" />
                    <div className="absolute -start-24 top-1/3 size-[22rem] rounded-full bg-[oklch(0.72_0.1_82/0.12)] blur-3xl" />
                    <div className="absolute inset-0 bg-[radial-gradient(circle_at_1px_1px,var(--color-border)_1px,transparent_0)] bg-size-[22px_22px] opacity-40" />
                    <div className="absolute inset-x-0 top-0 h-px bg-linear-to-l from-transparent via-primary/25 to-transparent" />
                </div>

                <div className="relative flex min-h-svh flex-col opacity-100 transition-opacity duration-700 starting:opacity-0">
                    <header className="px-6 pt-6 sm:px-8 sm:pt-8 lg:px-12">
                        <div className="mx-auto flex w-full max-w-6xl flex-col items-center text-center">
                            <Link
                                href="/"
                                className={cn(
                                    'group/logo flex flex-col items-center gap-3 rounded-none outline-none sm:gap-4',
                                    'focus-visible:ring-2 focus-visible:ring-ring/40 focus-visible:ring-offset-2 focus-visible:ring-offset-background',
                                )}
                                aria-label="الصفحة الرئيسية — وزارة التربية والتعليم"
                            >
                                <div className="relative">
                                    <div
                                        className="absolute -inset-6 rounded-full bg-primary/[0.04] blur-2xl transition-opacity duration-300 group-hover/logo:opacity-100"
                                        aria-hidden
                                    />
                                    <AppLogoIcon className="relative h-24 w-auto object-contain sm:h-28 md:h-32" />
                                </div>

                                <div className="space-y-1.5 sm:space-y-2">
                                    <p className="text-xs font-semibold tracking-[0.22em] text-primary uppercase sm:text-sm">
                                        الحكومة الليبية
                                    </p>
                                    <h1 className="text-2xl font-semibold tracking-tight text-balance text-foreground sm:text-3xl md:text-[2.15rem]">
                                        وزارة التربية والتعليم
                                    </h1>
                                </div>
                            </Link>

                            <div
                                className="mt-4 h-px w-14 bg-[oklch(0.72_0.1_82)] sm:mt-5 sm:w-16"
                                aria-hidden
                            />
                        </div>
                    </header>

                    <main
                        id="main-content"
                        className="mx-auto flex w-full max-w-6xl flex-1 flex-col px-6 pt-7 pb-10 sm:px-8 sm:pt-8 sm:pb-12 lg:px-12"
                    >
                        <section
                            className="mx-auto max-w-2xl text-center"
                            aria-labelledby="system-heading"
                        >
                            <Badge
                                variant="secondary"
                                className="mb-3 h-auto px-3 py-1 text-[0.7rem] tracking-[0.18em]"
                            >
                                بوابة الدخول
                            </Badge>

                            <h2
                                id="system-heading"
                                className="text-lg font-semibold tracking-tight text-balance text-foreground sm:text-xl md:text-2xl"
                            >
                                نظام إدارة بيانات وزارة التربية والتعليم
                            </h2>

                            <p className="mx-auto mt-2.5 max-w-xl text-sm leading-relaxed text-pretty text-muted-foreground">
                                اختر لوحة التحكم للمتابعة إلى صفحة تسجيل الدخول
                                وإدارة العمليات التعليمية بسهولة وأمان.
                            </p>
                        </section>

                        <Separator className="mx-auto mt-8 max-w-xs bg-border/80" />

                        <section
                            className="mt-8"
                            aria-labelledby="portals-heading"
                        >
                            <div className="mb-5 flex flex-col items-center gap-1.5 text-center">
                                <h2
                                    id="portals-heading"
                                    className="text-sm font-semibold tracking-[0.18em] text-foreground uppercase"
                                >
                                    لوحات التحكم
                                </h2>
                                <p className="max-w-md text-sm text-muted-foreground">
                                    حدد الجهة التابع لها للانتقال إلى صفحة
                                    تسجيل الدخول الخاصة بها.
                                </p>
                            </div>

                            <ul className="mx-auto grid max-w-5xl grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-6">
                                {portals.map((portal, index) => (
                                    <li
                                        key={portal.key}
                                        className={cn(
                                            'lg:col-span-2',
                                            index === 3 && 'lg:col-start-2',
                                            index === portals.length - 1 &&
                                            'sm:col-span-2 sm:mx-auto sm:w-full sm:max-w-[calc(50%-0.5rem)] lg:col-span-2 lg:mx-0 lg:max-w-none',
                                        )}
                                    >
                                        <PortalCard portal={portal} />
                                    </li>
                                ))}
                            </ul>
                        </section>
                    </main>

                    <footer className="border-t border-border/70 bg-card/40 px-6 py-6 backdrop-blur-sm sm:px-8 lg:px-12">
                        <div className="mx-auto flex w-full max-w-6xl flex-col items-center justify-between gap-3 text-center sm:flex-row sm:text-start">
                            <div className="flex items-center gap-2 text-xs text-muted-foreground sm:text-sm">
                                <ShieldCheckIcon
                                    className="size-4 shrink-0 text-primary/70"
                                    aria-hidden
                                />
                                <span>
                                    منصة رسمية آمنة لوزارة التربية والتعليم
                                </span>
                            </div>

                            <p className="text-xs text-muted-foreground space-x-1">
                                <span>©</span>
                                <span className="font-mono">{new Date().getFullYear()}</span>
                                <span>وزارة التربية والتعليم — الحكومة الليبية</span>
                            </p>
                        </div>
                    </footer>
                </div>
            </div>
        </>
    );
}
