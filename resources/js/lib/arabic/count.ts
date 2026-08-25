import formatNumber from "@/components/shared/dashboard/format-number";

export type ArabicCountForms = {
    zero?: string;
    one: string;
    two: string;
    few: string;
    other: string;
};

export function normalizeCount(count: number): number {
    return Math.max(0, Math.trunc(count));
}

export function formatArabicCount(count: number, forms: ArabicCountForms): string {
    const normalizedCount = normalizeCount(count);

    if (normalizedCount === 0) {
        return forms.zero ?? `${formatNumber(normalizedCount)} ${forms.other}`;
    }

    if (normalizedCount === 1) {
        return forms.one;
    }

    if (normalizedCount === 2) {
        return forms.two;
    }

    if (normalizedCount <= 10) {
        return `${formatNumber(normalizedCount)} ${forms.few}`;
    }

    return `${formatNumber(normalizedCount)} ${forms.other}`;
}
