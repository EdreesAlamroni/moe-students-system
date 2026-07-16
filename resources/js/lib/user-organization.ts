import type { UserOrganizationContext } from "@/types/auth";

export type OrganizationDisplay = {
    label: string;
    name: string;
    parent?: {
        label: string;
        name: string;
    };
};

export function resolveOrganizationDisplay(
    organization?: UserOrganizationContext | null,
): OrganizationDisplay | null {
    if (!organization) {
        return null;
    }

    switch (organization.type) {
        case "warehouse":
            return {
                label: "المخزن",
                name: organization.organization.warehouse.name,
            };
        case "education_monitor":
            return {
                label: "المُراقبة",
                name: organization.organization.education_monitor.name,
            };
        case "education_services_office":
            return {
                label: "مكتب الخدمات التعليمية",
                name: organization.organization.education_services_office.name,
                parent: organization.organization.education_monitor
                    ? {
                        label: "المُراقبة",
                        name: organization.organization.education_monitor.name,
                    }
                    : undefined,
            };
        case "school":
            return {
                label: "المدرسة",
                name: organization.organization.school.name,
                parent: organization.organization.education_monitor
                    ? {
                        label: "المُراقبة",
                        name: organization.organization.education_monitor.name,
                    }
                    : undefined,
            };
        default:
            return null;
    }
}
