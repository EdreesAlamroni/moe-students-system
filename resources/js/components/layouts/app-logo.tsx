import { useSidebar } from "@/components/layouts/sidebar/sidebar";

export default function AppLogo() {
    const { open } = useSidebar()

    return (
        <>
            <div className={`flex aspect-square items-center justify-center transition-all duration-300 ${open ? "size-10" : "size-8"}`}>
                <img src="/assets/images/logo.png" alt="الشعار" className="size-auto fill-current text-white" />
            </div>
            <div className="flex-1 text-start text-sm mb-[2px]">
                <span className="truncate leading-none font-semibold">نظام وزارة التربية والتعليم</span>
            </div>
        </>
    );
}
