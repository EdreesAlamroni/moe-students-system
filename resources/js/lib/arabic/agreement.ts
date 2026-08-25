import type { ArabicCountForms } from "@/lib/arabic/count";
import { normalizeCount } from "@/lib/arabic/count";

export type ArabicGrammaticalCase = "nominative" | "accusative" | "genitive";

export type ArabicAgreementSet = Record<ArabicGrammaticalCase, ArabicCountForms>;

export function formatArabicAgreement(
    count: number,
    forms: ArabicCountForms,
): string {
    const normalizedCount = normalizeCount(count);

    if (normalizedCount === 0) {
        return forms.zero ?? forms.other;
    }

    if (normalizedCount === 1) {
        return forms.one;
    }

    if (normalizedCount === 2) {
        return forms.two;
    }

    if (normalizedCount <= 10) {
        return forms.few;
    }

    return forms.other;
}

export function formatArabicAgreementForCase(
    count: number,
    agreementSet: ArabicAgreementSet,
    grammaticalCase: ArabicGrammaticalCase,
): string {
    return formatArabicAgreement(count, agreementSet[grammaticalCase]);
}

export type ArabicVerbForms = {
    zero?: string;
    one: string;
    two: string;
    other: string;
};

export function verbPastThirdPerson(count: number, forms: ArabicVerbForms): string {
    const normalizedCount = normalizeCount(count);

    if (normalizedCount === 0) {
        return forms.zero ?? forms.other;
    }

    if (normalizedCount === 1) {
        return forms.one;
    }

    if (normalizedCount === 2) {
        return forms.two;
    }

    return forms.other;
}
