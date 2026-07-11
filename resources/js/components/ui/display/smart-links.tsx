import React from "react";

import { cn } from "@/lib/utils";

export type SmartLinkType = "phone" | "whatsapp" | "email" | "url";

const DEFAULT_TITLES: Record<SmartLinkType, string> = {
    phone: "إضغط للإتصال",
    whatsapp: "إضغط للدردشة على واتساب",
    email: "إضغط للمراسلة",
    url: "إضغط لفتح الرابط",
};

// ——— Helpers ———
function inferType(value: string): SmartLinkType {
    const val = value.toLowerCase();

    if (/^\s*mailto:/.test(val) || /^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(val)) {
        return "email";
    }

    if (/^\s*tel:/.test(val) || /^[+]?[\d\s().-]{3,}$/.test(val)) {
        return "phone";
    }

    if (
        /^(whatsapp:\/\/)/.test(val) ||
        /^(https?:\/\/)?(www\.)?wa\.me\//.test(val) ||
        /^(https?:\/\/)?([a-z0-9-]+\.)?whatsapp\.com\//.test(val)
    ) {
        return "whatsapp";
    }

    return "url";
}

function normalizePhone(value: string): string {
    const trimmed = value.trim();

    if (trimmed.toLowerCase().startsWith("tel:")) {
        return `tel:${trimmed.slice(4).replace(/[\s()-]+/g, "")}`;
    }

    const cleaned = trimmed.replace(/[\s()-]+/g, "");

    return `tel:${cleaned}`;
}

function normalizeWhatsapp(value: string): string {
    const trimmed = value.trim();

    const digits = trimmed.replace(/\D+/g, "");

    return digits ? `https://wa.me/${digits}` : "https://wa.me/";
}

function normalizeEmail(value: string): string {
    const trimmed = value.trim();

    if (trimmed.toLowerCase().startsWith("mailto:")) {
        return `mailto:${trimmed.slice(7)}`;
    }

    return `mailto:${trimmed}`;
}

function ensureProtocol(url: string): string {
    const trimmed = url.trim();

    if (/^(https?:)?\/\//i.test(trimmed)) {
        // If protocol-relative //example.com, coerce to https://
        return trimmed.startsWith("http") ? trimmed : `https:${trimmed}`;
    }

    try {
        new URL(trimmed);
        return trimmed;
    } catch {
        return `https://${trimmed}`;
    }
}

function buildHrefByType(value: string, type: SmartLinkType): string {
    switch (type) {
        case "phone":
            return normalizePhone(value);
        case "whatsapp":
            return normalizeWhatsapp(value);
        case "email":
            return normalizeEmail(value);
        case "url":
        default:
            return ensureProtocol(value);
    }
}

export type SmartLinkProps = {
    value?: string;
    fallback?: React.ReactNode;
    title?: string;
    type?: SmartLinkType;
    className?: string;
} & Omit<React.AnchorHTMLAttributes<HTMLAnchorElement>, "href" | "children">;

/**
 * SmartLink: one component to render phone, email, or URL.
 * - Infers type when not provided
 * - Normalizes href (tel:, mailto:, https://)
 * - Blocks click when empty; shows fallback
 * - For URLs, defaults to target="_blank" rel="noopener noreferrer"
 */
export function SmartLink({
    value,
    fallback = "-",
    title,
    type: forcedType,
    className,
    onClick,
    target,
    rel,
    tabIndex,
    ...props
}: SmartLinkProps): React.ReactElement {
    const raw = (value ?? "").trim();
    const isFilled = raw.length > 0;

    const type: SmartLinkType = forcedType ?? (isFilled ? inferType(raw) : "url");
    const computedTitle = title ?? DEFAULT_TITLES[type];
    const href = isFilled ? buildHrefByType(raw, type) : "#";

    const handleClick: React.MouseEventHandler<HTMLAnchorElement> = (event) => {
        if (!isFilled) {
            event.preventDefault();
            event.stopPropagation();
            return;
        }
        onClick?.(event);
    };

    const isExternalUrl = type === "url";
    const computedTarget = isExternalUrl ? (target ?? "_blank") : target;
    const computedRel = isExternalUrl ? (rel ?? "noopener noreferrer") : rel;

    return (
        <a
            href={href}
            title={computedTitle}
            tabIndex={isFilled ? tabIndex : -1}
            aria-disabled={!isFilled || undefined}
            onClick={handleClick}
            target={computedTarget}
            rel={computedRel}
            className={cn(
                "focus:!ring-0",
                className,
                isFilled ? "hover:underline" : "cursor-default",
            )}
            {...props}
        >
            {isFilled ? raw : fallback}
        </a>
    );
}

// Public wrappers matching previous components' defaults/behavior

export type BaseLinkProps = Omit<SmartLinkProps, "type"> & { value?: string; title?: string };

export function ExternalLink({
    value,
    title = "إضغط لفتح الرابط",
    ...props
}: BaseLinkProps): React.ReactElement {
    return (
        <SmartLink value={value} title={title} type="url" {...props} />
    );
}

export function PhoneNumberLink({
    value,
    title = "إضغط للإتصال",
    className = "",
    ...props
}: BaseLinkProps): React.ReactElement {
    return (
        <SmartLink value={value} title={title} type="phone" className={cn("font-mono", className)} dir="ltr" {...props} />
    );
}

export function WhatsappLink({
    value,
    title = "إضغط للدردشة على واتساب",
    className = "",
    ...props
}: BaseLinkProps): React.ReactElement {
    return (
        <SmartLink value={value} title={title} type="whatsapp" className={cn("font-mono", className)} dir="ltr" {...props} />
    );
}

export function EmailLink({
    value,
    title = "إضغط للمراسلة",
    ...props
}: BaseLinkProps): React.ReactElement {
    return (
        <SmartLink value={value} title={title} type="email" {...props} />
    );
}
