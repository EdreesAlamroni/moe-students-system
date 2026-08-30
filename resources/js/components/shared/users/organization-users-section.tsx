import React from "react";

import type { OrganizationUser } from "@/types/auth";

import { Card, CardContent, CardHeader, CardTableContent, CardTitle } from "@/components/ui/structure/card";

import { Icon } from "@/components/ui/display/icon";
import { Table, TableBody, TableCell, TableCellNullableValue, TableHead, TableHeader, TableRow } from "@/components/ui/display/table";
import EmptyState from "@/components/ui/display/empty-state";

import { Eye, EyeOff } from "lucide-react";

function InitialPasswordCell({ value }: { value: string | null }) {
    const [visible, setVisible] = React.useState(false);

    if (value === null) {
        return <TableCellNullableValue value="لا توجد" />;
    }

    return (
        <div className="flex items-center gap-2">
            <span className="font-mono tracking-widest">{visible ? value : "••••••••"}</span>
            <button
                type="button"
                onClick={() => setVisible((current) => !current)}
                className="inline-flex size-8 shrink-0 items-center justify-center rounded-md text-muted-foreground transition-colors hover:bg-muted hover:text-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                aria-label={visible ? "إخفاء كلمة المرور الأولية" : "إظهار كلمة المرور الأولية"}
            >
                {visible ? <EyeOff className="size-4" /> : <Eye className="size-4" />}
            </button>
        </div>
    );
}

type OrganizationUsersSectionProps = {
    users: OrganizationUser[];
};

export default function OrganizationUsersSection({ users }: OrganizationUsersSectionProps) {
    const showPeriod = users.some((user) => user.academic_period_label);

    return (
        <section>
            <Card>
                <CardHeader>
                    <CardTitle>
                        <Icon iconNode="UsersIcon" />
                        <div className="flex items-center gap-x-1.5">
                            <span>المُستخدمين</span>
                            <span className="font-mono">({users.length})</span>
                        </div>
                    </CardTitle>
                </CardHeader>
                {users.length > 0 ? (
                    <CardTableContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead scope="col" className="w-24 font-mono">#</TableHead>
                                    <TableHead scope="col">الاسم</TableHead>
                                    <TableHead scope="col">اسم المُستخدم</TableHead>
                                    {showPeriod && (
                                        <TableHead scope="col">الفترة الدراسية</TableHead>
                                    )}
                                    <TableHead scope="col">كلمة المرور الأولية</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {users.map((user, index) => (
                                    <TableRow key={user.id}>
                                        <TableCell className="font-mono">{index + 1}</TableCell>
                                        <TableCell>{user.name}</TableCell>
                                        <TableCell className="font-mono tracking-widest">{user.username}</TableCell>
                                        {showPeriod && (
                                            <TableCell>
                                                <TableCellNullableValue value={user.academic_period_label} />
                                            </TableCell>
                                        )}
                                        <TableCell>
                                            <InitialPasswordCell value={user.initial_password} />
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </CardTableContent>
                ) : (
                    <CardContent>
                        <EmptyState />
                    </CardContent>
                )}
            </Card>
        </section>
    );
}
