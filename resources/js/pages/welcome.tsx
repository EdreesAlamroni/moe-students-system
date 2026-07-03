import { Head, Link } from "@inertiajs/react";

export default function Welcome() {
    const links = [
        { label: "الإدارة", link: "/administration/login" },
        { label: "المخزن", link: "/warehouse/login" },
        { label: "المُراقبة", link: "/education-monitor/login" },
        { label: "مكتب الخدمات التعليمية", link: "/education-services-office/login" },
        { label: "المدرسة", link: "/school/login" },
    ];

    return (
        <>
            <Head title="الرئيسية" />

            <div className="flex min-h-screen flex-col items-center bg-[#FDFDFC] p-6 text-[#1b1b18] lg:justify-center lg:p-8">
                <div className="flex w-full items-center justify-center opacity-100 transition-opacity duration-750 lg:grow starting:opacity-0">
                    <main className="flex w-full max-w-[335px] flex-col-reverse lg:max-w-4xl lg:flex-row">
                        <div className="flex-1 rounded-br-lg rounded-bl-lg bg-white p-6 pb-12 text-[13px] leading-[20px] border border-e-0 lg:rounded-tr-lg lg:rounded-bl-none lg:p-20">
                            <h1 className="mb-2.5 font-medium text-base">الحكومة الليبية - وزارة التربية والتعليم</h1>

                            <p className="mb-4 text-[#706f6c] text-[0.95rem]">
                                نظام إدارة بيانات وزارة التربية والتعليم
                            </p>

                            <ul className="flex flex-col gap-1 lg:mb-6 text-[0.90rem]">
                                {links.map((link, index) => (
                                    <li key={index} className="relative flex items-center gap-4 py-2 before:absolute before:h-7 before:top-1/2 last-of-type:before:top-0 before:bottom-0 before:right-[0.4rem] before:border-r before:border-[#e3e3e0]">
                                        <span className="relative bg-white py-1">
                                            <span className="flex h-3.5 w-3.5 items-center justify-center rounded-full border border-[#e3e3e0] bg-[#FDFDFC] shadow-[0px_0px_1px_0px_rgba(0,0,0,0.03),0px_1px_2px_0px_rgba(0,0,0,0.06)]">
                                                <span className="h-1.5 w-1.5 rounded-full bg-[#dbdbd7]" />
                                            </span>
                                        </span>
                                        <span>
                                            لوحة تحكم
                                            <Link
                                                href={link.link}
                                                className="ms-1 inline-flex items-center space-x-1 font-medium text-[#394F77] underline underline-offset-4"
                                            >
                                                <span>{link.label}</span>
                                                <svg width={10} height={11} viewBox="0 0 10 11" fill="none" xmlns="http://www.w3.org/2000/svg" className="h-2.5 w-2.5">
                                                    <path d="M7.70833 6.95834V2.79167H3.54167M2.5 8L7.5 3.00001" stroke="currentColor" strokeLinecap="square" />
                                                </svg>
                                            </Link>
                                        </span>
                                    </li>
                                ))}
                            </ul>
                        </div>

                        <div className="relative -mb-px aspect-335/376 w-full shrink-0 overflow-hidden rounded-t-lg bg-[#394F77] lg:mb-0 lg:-ml-px lg:aspect-auto lg:w-[438px] lg:rounded-t-none lg:rounded-l-lg">
                            <img src="/assets/images/logo.png" alt="وزارة التربية والتعليم بالحكومة الليبية" />
                            <div className="absolute inset-0 rounded-t-lg border lg:rounded-t-none lg:rounded-l-lg" />
                        </div>
                    </main>
                </div>
                <div className="hidden h-14.5 lg:block"></div>
            </div>
        </>
    );
}
