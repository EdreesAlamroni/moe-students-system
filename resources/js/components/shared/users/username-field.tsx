import { useRef } from 'react';

import { generateUsername } from '@/lib/generate-username';

import Field from '@/components/ui/controls/field';
import { Label } from '@/components/ui/controls/label';
import UsernameInput, { GenerateUsernameButton } from '@/components/ui/controls/username-input';
import InputError from '@/components/ui/controls/input-error';

type UsernameFieldProps = {
    error?: string;
    defaultValue?: string;
    className?: string;
};

export default function UsernameField({
    error,
    defaultValue,
    className,
}: UsernameFieldProps) {
    const inputRef = useRef<HTMLInputElement>(null);

    function handleGenerate(): void {
        const input = inputRef.current;

        if (!input) {
            return;
        }

        input.value = generateUsername();
        input.dispatchEvent(new Event('input', { bubbles: true }));
        input.focus();
    }

    return (
        <Field className={className}>
            <div className="flex items-center justify-between gap-3">
                <Label
                    htmlFor="username"
                    hasError={!!error}
                    required
                >
                    اسم المُستخدم
                </Label>

                <GenerateUsernameButton onGenerate={handleGenerate} />
            </div>

            <UsernameInput
                id="username"
                ref={inputRef}
                defaultValue={defaultValue}
                hasError={!!error}
            />

            <InputError message={error} />
        </Field>
    );
}
