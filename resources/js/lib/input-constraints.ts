/**
 * IME-safe input props that validate typing and paste without blocking navigation or shortcuts.
 *
 * @example
 * ```tsx
 * import { usernameInputConstraints } from '@/lib/input-constraints';
 *
 * <input {...usernameInputConstraints()} />
 * ```
 *
 * @example Custom constraints
 * ```tsx
 * import { createInputConstraints } from '@/lib/input-constraints';
 *
 * const digitsOnly = createInputConstraints({
 *   inputMode: 'numeric',
 *   pattern: '^\\d*$',
 *   isAllowedChar: (char) => /\d/.test(char),
 *   sanitizeValue: (value) => value.replace(/\D+/g, ''),
 * });
 * ```
 */

import type React from 'react';

type TextInputElement = HTMLInputElement | HTMLTextAreaElement;

export type InputConstraintProps = Pick<
    React.InputHTMLAttributes<TextInputElement>,
    | 'inputMode'
    | 'pattern'
    | 'onKeyDown'
    | 'onPaste'
    | 'onBeforeInput'
    | 'onCompositionStart'
    | 'onCompositionEnd'
>;

export type InputConstraintOptions = {
    inputMode: NonNullable<React.InputHTMLAttributes<TextInputElement>['inputMode']>;
    /** HTML pattern attribute. Use full-string anchors, e.g. `^\\d*$`. */
    pattern: string;
    /** Return `true` to allow a single character at the current selection. */
    isAllowedChar: (char: string, currentValue: string, selectionStart: number, selectionEnd: number) => boolean;
    /** Normalize an entire string after paste or IME composition. */
    sanitizeValue: (value: string) => string;
    /**
     * Optional transform for a before-input event. When a string is returned,
     * the default insertion is prevented and the returned text is inserted instead.
     */
    transformBeforeInput?: (
        data: string,
        currentValue: string,
        selectionStart: number,
        selectionEnd: number,
    ) => string | null | undefined;
};

export type DecimalInputConstraintOptions = {
    decimals?: number;
    min?: number;
    max?: number;
    /** Allow `-` even when `min >= 0`. */
    allowNegative?: boolean;
    /** Shorthand for `decimals: 0`. */
    allowDecimal?: boolean;
};

const NON_TEXT_KEYS = new Set([
    'Backspace',
    'Delete',
    'Tab',
    'Enter',
    'Escape',
    'ArrowLeft',
    'ArrowRight',
    'ArrowUp',
    'ArrowDown',
    'Home',
    'End',
]);

const LIBYAN_MOBILE_PREFIXES = ['091', '092', '093', '094', '095'] as const;

function getSelectionRange(input: TextInputElement): { start: number; end: number } {
    const start = input.selectionStart ?? input.value.length;
    const end = input.selectionEnd ?? start;

    return { start, end };
}

function buildProspectiveValue(
    currentValue: string,
    char: string,
    selectionStart: number,
    selectionEnd: number,
): string {
    return currentValue.slice(0, selectionStart) + char + currentValue.slice(selectionEnd);
}

function isNavigationOrShortcutKey(event: React.KeyboardEvent<TextInputElement>): boolean {
    if (event.ctrlKey || event.metaKey || event.altKey) {
        return true;
    }

    return NON_TEXT_KEYS.has(event.key) || event.key.length !== 1;
}

function insertTextAtSelection(
    input: TextInputElement,
    text: string,
    selectionStart: number,
    selectionEnd: number,
): void {
    try {
        if ('setRangeText' in input) {
            input.setRangeText(text, selectionStart, selectionEnd, 'end');

            return;
        }
    } catch {
        // Fall back to manual insertion below.
    }

    const before = input.value.slice(0, selectionStart);
    const after = input.value.slice(selectionEnd);

    input.value = before + text + after;

    const caret = before.length + text.length;

    try {
        input.setSelectionRange(caret, caret);
    } catch {
        // Ignore selection errors on unsupported inputs.
    }

    input.dispatchEvent(new Event('input', { bubbles: true }));
}

function hasAtMostOne(value: string, character: string): boolean {
    return value.split(character).length <= 2;
}

function isValidSignedDecimalDraft(
    next: string,
    options: { allowNegative?: boolean; maxDecimalPlaces?: number } = {},
): boolean {
    const allowNegative = options.allowNegative ?? true;
    const signPattern = allowNegative ? '-?' : '';

    if (!new RegExp(`^${signPattern}\\d*(?:\\.\\d*)?$`).test(next)) {
        return false;
    }

    if (!hasAtMostOne(next, '-')) {
        return false;
    }

    if (next.includes('-') && next.indexOf('-') !== 0) {
        return false;
    }

    if (!allowNegative && next.includes('-')) {
        return false;
    }

    if (!hasAtMostOne(next, '.')) {
        return false;
    }

    if (options.maxDecimalPlaces !== undefined) {
        const decimalIndex = next.indexOf('.');

        if (decimalIndex !== -1 && next.length - decimalIndex - 1 > options.maxDecimalPlaces) {
            return false;
        }
    }

    return true;
}

function isWithinAbsoluteBound(next: string, maxAbsolute: number): boolean {
    if (!/^-?\d+(?:\.\d*)?$/.test(next)) {
        return true;
    }

    const value = Number(next);

    return Number.isNaN(value) || Math.abs(value) <= maxAbsolute;
}

function sanitizeSignedDecimalValue(value: string, maxAbsolute: number): string {
    const cleaned = (value ?? '').replace(/[^0-9.-]+/g, '');
    const isNegative = cleaned.startsWith('-');
    const withoutSigns = cleaned.replace(/-/g, '');
    const firstDecimal = withoutSigns.indexOf('.');
    const normalizedDecimals = firstDecimal === -1
        ? withoutSigns
        : withoutSigns.slice(0, firstDecimal + 1) + withoutSigns.slice(firstDecimal + 1).replace(/\./g, '');
    const composed = `${isNegative ? '-' : ''}${normalizedDecimals}`;

    if (composed === '-' || composed === '-.' || composed === '.') {
        return '';
    }

    const numericValue = Number(composed);

    if (Number.isNaN(numericValue)) {
        return '';
    }

    let clamped = Math.min(maxAbsolute, Math.max(-maxAbsolute, numericValue));

    if (Math.abs(clamped) === maxAbsolute) {
        clamped = Math.sign(clamped) * maxAbsolute;
    }

    if (Object.is(clamped, -0)) {
        clamped = 0;
    }

    return String(clamped);
}

function uppercaseAllowedCharacters(
    value: string,
    allowedCharacter: RegExp,
): string | null {
    const transformed = [...value]
        .map((char) => (allowedCharacter.test(char) ? char.toUpperCase() : char))
        .join('');

    return transformed !== value ? transformed : null;
}

function createCharacterClassConstraints(
    characterClass: string,
    options?: { uppercase?: boolean },
): InputConstraintProps {
    const allowedCharacter = new RegExp(`^[${characterClass}]$`);
    const disallowedCharacters = new RegExp(`[^${characterClass}]+`, 'g');

    return createInputConstraints({
        inputMode: 'text',
        pattern: `^[${characterClass}]*$`,
        isAllowedChar: (char) => allowedCharacter.test(char),
        sanitizeValue: (value) => {
            const sanitized = value.replace(disallowedCharacters, '');

            return options?.uppercase ? sanitized.toUpperCase() : sanitized;
        },
        transformBeforeInput: options?.uppercase
            ? (data) => uppercaseAllowedCharacters(data, allowedCharacter)
            : undefined,
    });
}

function createSignedDecimalInputConstraints(
    maxAbsolute: number,
    pattern: string,
): InputConstraintProps {
    return createInputConstraints({
        inputMode: 'decimal',
        pattern,
        isAllowedChar: (char, currentValue, selectionStart, selectionEnd) => {
            if (!/[0-9.-]/.test(char)) {
                return false;
            }

            const next = buildProspectiveValue(currentValue, char, selectionStart, selectionEnd);

            if (!isValidSignedDecimalDraft(next)) {
                return false;
            }

            return isWithinAbsoluteBound(next, maxAbsolute);
        },
        sanitizeValue: (value) => sanitizeSignedDecimalValue(value, maxAbsolute),
    });
}

function isValidLibyanPhoneDigits(digits: string): boolean {
    if (digits.length === 0) {
        return true;
    }

    if (digits.length === 1) {
        return digits === '0';
    }

    if (digits.length === 2) {
        return digits === '09';
    }

    if (digits.length === 3) {
        return (LIBYAN_MOBILE_PREFIXES as readonly string[]).includes(digits);
    }

    return digits.length <= 10;
}

function normalizeLibyanPhoneDigits(digits: string): string {
    if (digits.startsWith('002189') && digits.length >= 13) {
        return (`0${digits.slice(5, 14)}`).slice(0, 10);
    }

    if (digits.startsWith('2189') && digits.length >= 12) {
        return (`0${digits.slice(3, 12)}`).slice(0, 10);
    }

    if (/^(91|92|93|94|95)\d{7}$/.test(digits)) {
        return `0${digits}`;
    }

    return digits.slice(0, 10);
}

function createInputConstraints(options: InputConstraintOptions): InputConstraintProps {
    const { inputMode, pattern, isAllowedChar, sanitizeValue, transformBeforeInput } = options;

    let composing = false;

    const onCompositionStart: React.CompositionEventHandler<TextInputElement> = () => {
        composing = true;
    };

    const onCompositionEnd: React.CompositionEventHandler<TextInputElement> = (event) => {
        const { value } = event.currentTarget;
        const sanitized = sanitizeValue(value);

        if (sanitized !== value) {
            event.currentTarget.value = sanitized;
        }

        composing = false;
    };

    const onBeforeInput: React.FormEventHandler<TextInputElement> = (event) => {
        const input = event.currentTarget;
        const nativeEvent = event.nativeEvent as InputEvent;

        if (nativeEvent.isComposing || composing) {
            return;
        }

        const inputType = nativeEvent.inputType;

        if (inputType && (inputType.startsWith('insertFrom') || inputType === 'insertReplacementText')) {
            return;
        }

        const data = nativeEvent.data ?? '';

        if (!data) {
            return;
        }

        const { start, end } = getSelectionRange(input);

        if (transformBeforeInput) {
            const replacement = transformBeforeInput(data, input.value, start, end);

            if (typeof replacement === 'string') {
                event.preventDefault();
                insertTextAtSelection(input, replacement, start, end);

                return;
            }
        }

        for (const char of data) {
            if (!isAllowedChar(char, input.value, start, end)) {
                event.preventDefault();

                return;
            }
        }
    };

    const onKeyDown: React.KeyboardEventHandler<TextInputElement> = (event) => {
        if (isNavigationOrShortcutKey(event) || composing) {
            return;
        }

        const input = event.currentTarget;
        const { start, end } = getSelectionRange(input);

        if (transformBeforeInput) {
            const replacement = transformBeforeInput(event.key, input.value, start, end);

            if (typeof replacement === 'string') {
                event.preventDefault();
                insertTextAtSelection(input, replacement, start, end);

                return;
            }
        }

        if (!isAllowedChar(event.key, input.value, start, end)) {
            event.preventDefault();
        }
    };

    const onPaste: React.ClipboardEventHandler<TextInputElement> = (event) => {
        const input = event.currentTarget;
        const pasted = event.clipboardData.getData('text');

        if (!pasted) {
            return;
        }

        event.preventDefault();

        const { start, end } = getSelectionRange(input);

        insertTextAtSelection(input, sanitizeValue(pasted), start, end);
    };

    return {
        inputMode,
        pattern,
        onKeyDown,
        onPaste,
        onBeforeInput,
        onCompositionStart,
        onCompositionEnd,
    };
}

/** Letters, digits, hyphens, and underscores. */
function codeSlugInputConstraints(): InputConstraintProps {
    return createCharacterClassConstraints('A-Za-z0-9-_');
}

/** Letters, digits, and underscores. Values are uppercased on paste. */
function codeInputConstraints(): InputConstraintProps {
    return createCharacterClassConstraints('A-Za-z0-9_', { uppercase: true });
}

/** Letters, digits, and underscores. */
function usernameInputConstraints(): InputConstraintProps {
    return createCharacterClassConstraints('A-Za-z0-9_');
}

/** Libyan mobile number: `0(91|92|93|94|95)` followed by seven digits. */
function libyanPhoneNumberInputConstraints(): InputConstraintProps {
    return createInputConstraints({
        inputMode: 'tel',
        pattern: '^0(?:91|92|93|94|95)\\d{7}$',
        isAllowedChar: (char, currentValue, selectionStart, selectionEnd) => {
            if (!/\d/.test(char)) {
                return false;
            }

            const next = buildProspectiveValue(currentValue, char, selectionStart, selectionEnd);
            const digits = next.replace(/\D/g, '');

            return isValidLibyanPhoneDigits(digits);
        },
        sanitizeValue: (value) => normalizeLibyanPhoneDigits(value.replace(/\D+/g, '')),
    });
}

/** Twelve-digit Libyan national ID starting with `1` or `2`. */
function libyanNationalIdInputConstraints(): InputConstraintProps {
    return createInputConstraints({
        inputMode: 'numeric',
        pattern: '^[12]\\d{11}$',
        isAllowedChar: (char, currentValue, selectionStart, selectionEnd) => {
            if (!/\d/.test(char)) {
                return false;
            }

            const next = buildProspectiveValue(currentValue, char, selectionStart, selectionEnd);
            const digits = next.replace(/\D/g, '');

            if (digits.length > 12) {
                return false;
            }

            return digits.length === 0 || digits[0] === '1' || digits[0] === '2';
        },
        sanitizeValue: (value) => {
            const digits = (value ?? '').replace(/\D+/g, '');

            if (!digits) {
                return '';
            }

            if (digits[0] === '1' || digits[0] === '2') {
                return digits.slice(0, 12);
            }

            const firstValidIndex = digits.search(/[12]/);

            return firstValidIndex === -1 ? '' : digits.slice(firstValidIndex, firstValidIndex + 12);
        },
    });
}

/** Latitude between -90 and 90 with optional decimals. */
function latitudeInputConstraints(): InputConstraintProps {
    return createSignedDecimalInputConstraints(
        90,
        '^-?(?:[0-8]?\\d(?:\\.\\d+)?|90(?:\\.0+)?)$',
    );
}

/** Longitude between -180 and 180 with optional decimals. */
function longitudeInputConstraints(): InputConstraintProps {
    return createSignedDecimalInputConstraints(
        180,
        '^-?(?:(?:[0-9]?\\d|1[0-7]\\d)(?:\\.\\d+)?|180(?:\\.0+)?)$',
    );
}

/**
 * Decimal input with configurable precision and bounds.
 *
 * @example `decimalInputConstraints()` — two decimal places, min 0
 * @example `decimalInputConstraints(0)` — integers only
 * @example `decimalInputConstraints({ decimals: 3, min: 0 })`
 * @example `decimalInputConstraints({ min: 0, max: 100 })` — percentage
 */
function decimalInputConstraints(
    options?: number | DecimalInputConstraintOptions,
): InputConstraintProps {
    const resolvedOptions = typeof options === 'number' ? { decimals: options } : (options ?? {});
    const rawDecimals = Math.max(0, Math.floor(resolvedOptions.decimals ?? 2));
    const decimals = resolvedOptions.allowDecimal === false ? 0 : rawDecimals;
    const min = resolvedOptions.min ?? 0;
    const max = resolvedOptions.max ?? Number.MAX_SAFE_INTEGER;
    const allowNegative = resolvedOptions.allowNegative === true || min < 0;
    const signPattern = allowNegative ? '-?' : '';
    const decimalPattern = decimals > 0 ? `(?:\\.\\d{0,${decimals}})?` : '';
    const pattern = `^${signPattern}\\d+${decimalPattern}$`;

    return createInputConstraints({
        inputMode: decimals > 0 ? 'decimal' : 'numeric',
        pattern,
        isAllowedChar: (char, currentValue, selectionStart, selectionEnd) => {
            const allowedCharacters = decimals === 0 ? /[0-9-]/ : /[0-9.-]/;

            if (!allowedCharacters.test(char)) {
                return false;
            }

            const next = buildProspectiveValue(currentValue, char, selectionStart, selectionEnd);

            if (char === '.') {
                const integerCandidate = Number(next.replace(/\./g, ''));

                if (!Number.isNaN(integerCandidate) && integerCandidate >= max) {
                    return false;
                }
            }

            if (!isValidSignedDecimalDraft(next, { allowNegative, maxDecimalPlaces: decimals })) {
                return false;
            }

            if (!new RegExp(`^${signPattern}\\d+(?:\\.\\d*)?$`).test(next)) {
                return true;
            }

            const numericValue = Number(next);

            if (Number.isNaN(numericValue)) {
                return true;
            }

            return numericValue <= max && numericValue >= min;
        },
        sanitizeValue: (value) => {
            const raw = value ?? '';
            const isNegative = allowNegative && raw.trim().startsWith('-');
            const cleaned = raw.replace(/[^0-9.-]+/g, '').replace(/-/g, '');
            const firstDecimal = cleaned.indexOf('.');
            const normalizedDecimals = firstDecimal === -1
                ? cleaned
                : cleaned.slice(0, firstDecimal + 1) + cleaned.slice(firstDecimal + 1).replace(/\./g, '');
            const composed = `${isNegative ? '-' : ''}${normalizedDecimals}`;

            if (composed === '-' || composed === '.' || composed === '-.') {
                return '';
            }

            let numericValue = Number(composed);

            if (Number.isNaN(numericValue)) {
                return '';
            }

            numericValue = Math.min(max, Math.max(min, numericValue));

            if (decimals > 0) {
                const factor = 10 ** decimals;
                numericValue = Math.round(numericValue * factor) / factor;
            } else {
                numericValue = Math.round(numericValue);
            }

            if (Object.is(numericValue, -0)) {
                numericValue = 0;
            }

            return String(numericValue);
        },
        transformBeforeInput: (data, currentValue, selectionStart, selectionEnd) => {
            if (data !== '.' || decimals === 0) {
                return null;
            }

            const isEmpty = currentValue.length === 0;
            const isLeadingMinus = allowNegative && currentValue === '-';
            const atEnd = selectionStart === currentValue.length && selectionEnd === selectionStart;

            if (!atEnd || (!isEmpty && !isLeadingMinus)) {
                return null;
            }

            return isLeadingMinus ? '-0.' : '0.';
        },
    });
}

/** Uppercase English letters and digits. Rejects Arabic numerals, symbols, and whitespace. */
function passportNumberInputConstraints(): InputConstraintProps {
    return createCharacterClassConstraints('A-Za-z0-9', { uppercase: true });
}

export {
    createInputConstraints,
    codeSlugInputConstraints,
    codeInputConstraints,
    usernameInputConstraints,
    libyanPhoneNumberInputConstraints,
    libyanNationalIdInputConstraints,
    passportNumberInputConstraints,
    latitudeInputConstraints,
    longitudeInputConstraints,
    decimalInputConstraints,
}
