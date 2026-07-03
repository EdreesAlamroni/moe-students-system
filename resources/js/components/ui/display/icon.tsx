import { type ComponentType } from "react";

import { cn } from "@/lib/utils";

import {
    type LucideProps,
    LucideIcon,
    LayoutGridIcon,
    CalendarRangeIcon,
    GraduationCapIcon,
    BookTextIcon,
    WarehouseIcon,
    LandmarkIcon,
    BuildingIcon,
    SchoolIcon,
    LibraryBigIcon,
    PresentationIcon,
    UsersIcon,
    UserPlusIcon,
    UserXIcon,
    ListOrderedIcon,
    TableOfContentsIcon,
    ContactIcon,
    ClockIcon,
    CalendarDaysIcon,
    BarChart3Icon,
    SearchIcon,
    UserRoundCogIcon,
    MapPinnedIcon,
    ClipboardList,
    SettingsIcon,
    LogOutIcon,
    CircleIcon,
    ShuffleIcon,
    UserIcon,
    ShieldIcon,
} from "lucide-react";

const lucideIconsMap: Record<string, LucideIcon> = {
    LayoutGridIcon,
    CalendarRangeIcon,
    GraduationCapIcon,
    BookTextIcon,
    WarehouseIcon,
    LandmarkIcon,
    BuildingIcon,
    SchoolIcon,
    LibraryBigIcon,
    PresentationIcon,
    UsersIcon,
    UserPlusIcon,
    UserXIcon,
    ListOrderedIcon,
    TableOfContentsIcon,
    ClockIcon,
    CalendarDaysIcon,
    ContactIcon,
    BarChart3Icon,
    SearchIcon,
    UserRoundCogIcon,
    MapPinnedIcon,
    ClipboardList,
    SettingsIcon,
    LogOutIcon,
    CircleIcon,
    ShuffleIcon,
    UserIcon,
    ShieldIcon,
};

interface IconProps extends Omit<LucideProps, "ref"> {
    iconNode?: ComponentType<LucideProps> | string | null;
}

export function Icon({ iconNode: icon, className, ...props }: IconProps) {
    const IconComponent = typeof icon === "string" ? lucideIconsMap[icon] : icon;

    if (!IconComponent) {
        return <CircleIcon className="inline-block w-4 h-4 bg-sidebar-accent rounded-sm" />;
    }

    return <IconComponent className={cn("h-4 w-4", className)} {...props} />;
}
