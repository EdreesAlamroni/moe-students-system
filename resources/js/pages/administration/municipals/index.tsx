import React from 'react'

import { Form, Head, Link } from "@inertiajs/react";

import type { Municipal, Paginated } from "@/types";

import MainContainer from "@/components/ui/structure/main-container";

import { Card, CardContent, CardFooter, CardHeader, CardTableContent, CardTitle } from "@/components/ui/structure/card";

import { Table, TableHeader, TableBody, TableRow, TableHead, TableCell, TableCellNullableValue } from "@/components/ui/display/table";
import EmptyState from "@/components/ui/display/empty-state";

import { Input } from "@/components/ui/controls/input";

import { Button } from "@/components/ui/actions/button";

import { Paginator } from "@/components/ui/navigation/paginator";

import FunnelIcon from "@/components/ui/icons/funnel-icon";
import { ListIcon, RefreshCcwIcon, SearchIcon } from "lucide-react";

import { index } from "@/routes/administration/municipals";

type PageProps = {
    municipals: Paginated<Municipal>;
    filter: {
        name?: string;
    }
}

export default function Index({ municipals, filter }: PageProps) {

    const { data, links, ...meta } = municipals;

    const hasFilter = Object.values(filter).some((value) => value);

    const hasPagination = data.length > 0 && meta.last_page > 1;

    return (
        <>
            <Head title="البلديات" />

            <MainContainer>
                <section>
                    <Form
                        {...index.form()}
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
                                        autoComplete="off"
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
                                <span>البلديات</span>
                            </CardTitle>
                        </CardHeader>
                        {data.length > 0 ? (
                            <CardTableContent>
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead scope="col" className="font-mono w-24">#</TableHead>
                                            <TableHead scope="col" className="text-center">الاسم</TableHead>
                                            <TableHead scope="col" className="text-center">عدد المدارس</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {data.map((municipal: Municipal, index: number) => (
                                            <TableRow key={municipal.uuid}>
                                                <TableCell className="font-mono">{index + 1}</TableCell>
                                                <TableCell className="text-center">{municipal.name}</TableCell>
                                                <TableCell className="text-center">
                                                    <TableCellNullableValue className="font-mono" value={municipal.schools_count} fallback={0} />
                                                </TableCell>
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
        </>
    )
}

Index.layout = () => ({
    breadcrumbs: [
        {
            title: 'البلديات',
            href: index.url(),
        },
    ],
});
