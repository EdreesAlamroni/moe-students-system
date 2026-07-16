import React from "react";

import { cn } from "@/lib/utils";

import type { RoleGroup } from "@/types/auth";

import { Label } from "@/components/ui/controls/label";
import { Checkbox } from "@/components/ui/controls/checkbox";

import { ShieldIcon } from "lucide-react";


type GroupedRolesFieldsetProps = {
    groupedRoles: Record<string, RoleGroup> | RoleGroup[];
    selectedRoles: number[];
    allRolesChecked: boolean;
    someRolesChecked: boolean;
    onToggleAllRoles: (checked: boolean | "indeterminate") => void;
    onToggleRole: (roleId: number, checked: boolean) => void;
    isGroupAllChecked: (group: RoleGroup) => boolean;
    isGroupSomeChecked: (group: RoleGroup) => boolean;
    onToggleGroupRoles: (group: RoleGroup, checked: boolean | "indeterminate") => void;
    hasError?: boolean;
    emptyTitle?: string;
    emptyDescription?: string;
};

function normalizeRoleGroups(
    groupedRoles: Record<string, RoleGroup> | RoleGroup[]
): Array<[string, RoleGroup]> {
    if (Array.isArray(groupedRoles)) {
        return groupedRoles.map(function (group: RoleGroup, index: number): [string, RoleGroup] {
            return [String(index), group];
        });
    }

    return Object.entries(groupedRoles);
}

function RolesEmptyState({
    title,
    description,
}: {
    title: string;
    description: string;
}) {
    return (
        <div className="rounded-none border border-dashed bg-muted/30 px-6 py-10 text-center">
            <ShieldIcon className="mx-auto mb-3 size-8 text-muted-foreground" />
            <p className="text-sm font-medium text-foreground">{title}</p>
            <p className="mx-auto mt-2 max-w-md text-xs leading-relaxed text-muted-foreground">
                {description}
            </p>
        </div>
    );
}

export default function GroupedRolesFieldset({
    groupedRoles,
    selectedRoles,
    allRolesChecked,
    someRolesChecked,
    onToggleAllRoles,
    onToggleRole,
    isGroupAllChecked,
    isGroupSomeChecked,
    onToggleGroupRoles,
    hasError = false,
    emptyTitle = "لا توجد أدوار أو صلاحيات متاحة",
    emptyDescription = "لم يتم إعداد أي أدوار لهذا النطاق بعد. يُرجى التواصل مع مسؤول النظام لإعداد الصلاحيات قبل إنشاء المُستخدم.",
}: GroupedRolesFieldsetProps) {
    const roleGroups = normalizeRoleGroups(groupedRoles);
    const hasRoles = roleGroups.length > 0 && roleGroups.some(function ([, group]) {
        return group.roles.length > 0;
    });

    return (
        <fieldset className="space-y-4" aria-required="true" aria-invalid={hasError || undefined}>
            <legend
                className={cn(
                    "relative mb-1 text-sm font-medium tracking-wide uppercase select-none",
                    "after:absolute after:content-['*'] after:-top-1 after:ms-1 after:align-middle after:text-sm after:font-normal after:text-destructive",
                    hasError && "text-destructive",
                )}
            >
                الأدوار والصلاحيات
            </legend>

            {!hasRoles ? (
                <RolesEmptyState
                    title={emptyTitle}
                    description={emptyDescription}
                />
            ) : (
                <>
                    <div className="flex items-center gap-x-2 border-b pb-3 mt-2">
                        <Checkbox
                            id="checkbox-all-roles"
                            checked={allRolesChecked ? true : someRolesChecked ? "indeterminate" : false}
                            onCheckedChange={onToggleAllRoles}
                            aria-invalid={hasError}
                        />
                        <Label htmlFor="checkbox-all-roles">
                            تحديد جميع الأدوار والصلاحيات
                        </Label>
                    </div>

                    <div className="grid grid-cols-1 gap-8">
                        {roleGroups.map(function ([key, group]: [string, RoleGroup]) {
                            const groupId = `role-group-${key}`;
                            const groupAll = isGroupAllChecked(group);
                            const groupSome = isGroupSomeChecked(group);

                            return (
                                <section key={key} aria-labelledby={groupId} className="space-y-4 border p-4">
                                    <div className="flex items-center justify-between border-b pb-2.5">
                                        <h3 id={groupId} className="text-sm font-medium leading-none">
                                            {group.label}
                                        </h3>

                                        {group.roles.length > 0 && (
                                            <div className="flex items-center gap-x-2">
                                                <Checkbox
                                                    id={`${groupId}-all`}
                                                    checked={groupAll ? true : groupSome ? "indeterminate" : false}
                                                    onCheckedChange={function (checked) {
                                                        onToggleGroupRoles(group, checked);
                                                    }}
                                                />
                                                <Label htmlFor={`${groupId}-all`}>
                                                    تحديد الكل
                                                </Label>
                                            </div>
                                        )}
                                    </div>

                                    {group.roles.length > 0 ? (
                                        <ul className="grid grid-cols-1 gap-3 lg:grid-cols-2 xl:grid-cols-3">
                                            {group.roles.map(function (role) {
                                                const inputId = `role-${role.id}`;

                                                return (
                                                    <li key={role.id} className="flex items-center gap-x-2">
                                                        <Checkbox
                                                            id={inputId}
                                                            value={role.id}
                                                            checked={selectedRoles.includes(role.id)}
                                                            onCheckedChange={function (checked: boolean | "indeterminate"): void {
                                                                onToggleRole(role.id, checked === true);
                                                            }}
                                                            aria-invalid={hasError}
                                                        />
                                                        <Label htmlFor={inputId}>
                                                            {role.label}
                                                        </Label>
                                                    </li>
                                                );
                                            })}
                                        </ul>
                                    ) : (
                                        <p className="text-sm text-muted-foreground">
                                            لا توجد صلاحيات في هذه المجموعة.
                                        </p>
                                    )}
                                </section>
                            );
                        })}
                    </div>
                </>
            )}
        </fieldset>
    );
}
