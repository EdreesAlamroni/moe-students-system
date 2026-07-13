import React from 'react'

import { Form, Head, Link } from "@inertiajs/react";

import type { CanPermissions, Paginated, User } from "@/types";

import MainContainer from "@/components/ui/structure/main-container";

import { Card, CardContent, CardFooter, CardHeader, CardTableContent, CardTitle } from "@/components/ui/structure/card";
import ActionsSection from "@/components/ui/structure/actions-section";

import { Table, TableHeader, TableBody, TableRow, TableHead, TableCell, TableCellActions } from "@/components/ui/display/table";
import EmptyState from "@/components/ui/display/empty-state";
import { Icon } from "@/components/ui/display/icon";

import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger } from "@/components/ui/navigation/dropdown-menu";

import { Input } from "@/components/ui/controls/input";

import { Button } from "@/components/ui/actions/button";
import ViewDetailsLink from "@/components/ui/actions/view-details-link";

import { Paginator } from "@/components/ui/navigation/paginator";

import FunnelIcon from "@/components/ui/icons/funnel-icon";
import { ListIcon, PlusIcon, RefreshCcwIcon, SearchIcon } from "lucide-react";

import { create, index, show } from "@/routes/administration/users";

type UserProps = User & {
    canAny: boolean;
    can: CanPermissions;
}

type PageProps = {
    users: Paginated<UserProps>;
    filter: {
        name?: string;
    }
    scopes: {
        label: string;
        value: string;
        icon: string;
    }[];
    canAny: boolean;
    can: CanPermissions;
}

export default function Index({ users, filter, scopes, canAny, can }: PageProps) {
    const { data, links, ...meta } = users;

    const hasFilter = Object.values(filter).some((value) => value);

    const hasPagination = data.length > 0 && meta.last_page > 1;

    return (
        <MainContainer>
            <Head title="المُستخدمين" />

            {canAny && (
                <ActionsSection>
                    {can.create && (
                        <DropdownMenu>
                            <DropdownMenuTrigger asChild>
                                <Button>
                                    <PlusIcon />
                                    <span>إضافة مُستخدم جديد</span>
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end">
                                {scopes.map((item, index) => (
                                    <>
                                        <DropdownMenuItem key={index} asChild>
                                            <Link href={create.url({ scope: item.value })}>
                                                <Icon iconNode={item.icon} className="text-foreground" />
                                                <span>{item.label}</span>
                                            </Link>
                                        </DropdownMenuItem>
                                        {(index !== (scopes.length - 1)) && <DropdownMenuSeparator />}
                                    </>
                                ))}
                            </DropdownMenuContent>
                        </DropdownMenu>
                    )}
                </ActionsSection>
            )}

            <section>
                <Form
                    action={index.url()}
                    method="GET"
                >
                    <Card>
                        <CardHeader className="border-b">
                            <CardTitle className="flex items-center text-sm gap-x-1.5">
                                <div className="flex items-center gap-x-3">
                                    <FunnelIcon />
                                    <span>فرز النتائج</span>
                                </div>
                                <span className="font-mono">({meta.total})</span>
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                                <Input
                                    type="text"
                                    name="filter[name]"
                                    value={filter.name}
                                    placeholder="الاسم"
                                />
                            </div>
                        </CardContent>
                        <CardFooter className="border-t">
                            <div className="flex items-center gap-x-3">
                                <Button
                                    type="submit"
                                    variant="default"
                                >
                                    <SearchIcon />
                                    <span>بحث</span>
                                </Button>
                                <Button
                                    type="reset"
                                    variant="outline"
                                    asChild
                                >
                                    <Link href={index.url()}>
                                        <RefreshCcwIcon />
                                        <span>مسح حقول الفلتر</span>
                                    </Link>
                                </Button>
                            </div>
                        </CardFooter>
                    </Card>
                </Form>
            </section>

            <section>
                <Card>
                    <CardHeader className="border-b">
                        <CardTitle>
                            <ListIcon />
                            <span>المُستخدمين</span>
                        </CardTitle>
                    </CardHeader>
                    {data.length > 0 ? (
                        <CardTableContent>
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead scope="col" className="font-mono w-24">#</TableHead>
                                        <TableHead scope="col">الاسم</TableHead>
                                        <TableHead scope="col">اسم المُستخدم</TableHead>
                                        <TableHead scope="col">النطاق</TableHead>
                                        <TableHead scope="col" />
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {data.map((user: UserProps, index: number) => (
                                        <TableRow key={user.uuid}>
                                            <TableCell className="font-mono">{index + 1}</TableCell>
                                            <TableCell>{user.name}</TableCell>
                                            <TableCell>{user.username}</TableCell>
                                            <TableCell>{user.scope.name}</TableCell>
                                            <TableCellActions>
                                                {user.canAny && (
                                                    <>
                                                        {user.can.view && (
                                                            <ViewDetailsLink
                                                                href={show.url({ user: user })}
                                                            />
                                                        )}
                                                    </>
                                                )}
                                            </TableCellActions>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        </CardTableContent>
                    ) : (
                        <CardContent>
                            <EmptyState
                                hasFilter={hasFilter}
                            />
                        </CardContent>
                    )}
                    {hasPagination && (
                        <CardFooter className="border-t">
                            <Paginator
                                links={links}
                                meta={meta}
                            />
                        </CardFooter>
                    )}
                </Card>
            </section>
        </MainContainer>
    )
}

Index.layout = () => ({
    breadcrumbs: [
        {
            title: 'المُستخدمين',
            href: index.url(),
        },
    ],
});
