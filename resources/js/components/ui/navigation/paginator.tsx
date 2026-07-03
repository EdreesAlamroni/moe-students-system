import {
    Pagination,
    PaginationContent,
    PaginationEllipsis,
    PaginationItem,
    PaginationLink,
    PaginationNext,
    PaginationPrevious,
} from "@/components/ui/navigation/pagination";

import { type PaginationLink as DataPaginationLink, type PaginationMeta } from "@/types";

type PaginatorProps = {
    links: DataPaginationLink[];
    meta: PaginationMeta;
}

export function Paginator({ links, meta }: PaginatorProps) {
    // strip first/last (built-in “Previous” & “Next”)
    const pageLinks = links.slice(1, -1);

    return (
        <Pagination>
            <PaginationContent>
                {/* Previous */}
                <PaginationItem>
                    <PaginationPrevious
                        href={meta.prev_page_url ?? "#"}
                        className={!meta.prev_page_url ? "opacity-50 pointer-events-none" : ""}
                    />
                </PaginationItem>

                {/* The “real” pages + ellipses */}
                {pageLinks.map((link: DataPaginationLink, id: number) => (
                    <PaginationItem key={id}>
                        {link.url ? (
                            <PaginationLink
                                href={link.url}
                                className="font-mono"
                                isActive={link.active}
                                dangerouslySetInnerHTML={{ __html: link.label }}
                            />
                        ) : (
                            <PaginationEllipsis />
                        )}
                    </PaginationItem>
                ))}

                {/* Next */}
                <PaginationItem>
                    <PaginationNext
                        href={meta.next_page_url ?? "#"}
                        className={!meta.next_page_url ? "opacity-50 pointer-events-none" : ""}
                    />
                </PaginationItem>
            </PaginationContent>
        </Pagination>
    )
}
