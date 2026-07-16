import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/structure/card";

import type { RoleGroup } from "@/types/auth";

import { CheckCircle2Icon, ShieldIcon } from "lucide-react";

type GroupedRolesShowCardProps = {
    roles: Record<string, RoleGroup> | RoleGroup[];
    emptyTitle?: string;
    emptyDescription?: string;
};

function normalizeRoleGroups(
    roles: Record<string, RoleGroup> | RoleGroup[]
): Array<[string, RoleGroup]> {
    if (Array.isArray(roles)) {
        return roles.map(function (group: RoleGroup, index: number): [string, RoleGroup] {
            return [String(index), group];
        });
    }

    return Object.entries(roles);
}

export default function GroupedRolesShowCard({
    roles,
    emptyTitle = "لا توجد أدوار مُخصصة",
    emptyDescription = "لم يتم تعيين أي أدوار أو صلاحيات لهذا المُستخدم بعد. يمكنك تعديل بيانات المُستخدم لإضافة الصلاحيات المناسبة.",
}: GroupedRolesShowCardProps) {
    const roleGroups = normalizeRoleGroups(roles);
    const hasRoles = roleGroups.length > 0 && roleGroups.some(function ([, group]) {
        return group.roles.length > 0;
    });

    return (
        <section>
            <Card>
                <CardHeader className="border-b">
                    <CardTitle className="text-sm">الأدوار والصلاحيات</CardTitle>
                </CardHeader>
                <CardContent>
                    {hasRoles ? (
                        <div className="grid grid-cols-1 gap-8">
                            {roleGroups.map(function ([key, group]: [string, RoleGroup]) {
                                const groupId = `role-group-${key}`;

                                return (
                                    <section key={groupId} aria-labelledby={groupId} className="space-y-4">
                                        <div className="flex items-center justify-between border-b pb-2.5">
                                            <h3 id={groupId} className="text-sm font-medium leading-none">
                                                {group.label}
                                            </h3>
                                        </div>

                                        {group.roles.length > 0 ? (
                                            <ul className="grid grid-cols-1 gap-3 lg:grid-cols-2 xl:grid-cols-3">
                                                {group.roles.map(function (role) {
                                                    return (
                                                        <li key={role.id} className="flex items-center gap-x-2">
                                                            <CheckCircle2Icon className="size-4 shrink-0 fill-primary text-white" />
                                                            <span className="text-sm font-normal">{role.label}</span>
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
                    ) : (
                        <div className="rounded-none border border-dashed bg-muted/30 px-6 py-10 text-center">
                            <ShieldIcon className="mx-auto mb-3 size-8 text-muted-foreground" />
                            <p className="text-sm font-medium text-foreground">{emptyTitle}</p>
                            <p className="mx-auto mt-2 max-w-md text-xs leading-relaxed text-muted-foreground">
                                {emptyDescription}
                            </p>
                        </div>
                    )}
                </CardContent>
            </Card>
        </section>
    );
}
