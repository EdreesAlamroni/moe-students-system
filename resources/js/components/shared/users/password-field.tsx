import { useRef, useState } from 'react';

import { generatePassword } from '@/lib/generate-password';

import Field from '@/components/ui/controls/field';
import { Label } from '@/components/ui/controls/label';
import PasswordInput, { GeneratePasswordButton } from '@/components/ui/controls/password-input';
import InputError from '@/components/ui/controls/input-error';

type PasswordFieldProps = {
    passwordError?: string;
    passwordConfirmationError?: string;
    className?: string;
};

function setInputValue(input: HTMLInputElement, value: string): void {
    input.value = value;
    input.dispatchEvent(new Event('input', { bubbles: true }));
}

export default function PasswordField({
    passwordError,
    passwordConfirmationError,
    className,
}: PasswordFieldProps) {
    const passwordRef = useRef<HTMLInputElement>(null);
    const passwordConfirmationRef = useRef<HTMLInputElement>(null);
    const [isPasswordVisible, setIsPasswordVisible] = useState(false);

    function handleGenerate(): void {
        const password = generatePassword();
        const passwordInput = passwordRef.current;
        const passwordConfirmationInput = passwordConfirmationRef.current;

        if (passwordInput) {
            setInputValue(passwordInput, password);
        }

        if (passwordConfirmationInput) {
            setInputValue(passwordConfirmationInput, password);
        }

        setIsPasswordVisible(true);
        passwordInput?.focus();
    }

    return (
        <>
            <Field className={className}>
                <div className="flex items-center justify-between gap-3">
                    <Label
                        htmlFor="password"
                        hasError={!!passwordError}
                        required
                    >
                        كلمة المرور
                    </Label>

                    <GeneratePasswordButton onGenerate={handleGenerate} />
                </div>

                <PasswordInput
                    id="password"
                    ref={passwordRef}
                    name="password"
                    autoComplete="new-password"
                    className="not-placeholder-shown:font-mono"
                    hasError={!!passwordError}
                    visible={isPasswordVisible}
                    onVisibleChange={setIsPasswordVisible}
                    required
                />

                <InputError message={passwordError} />
            </Field>

            <Field className={className}>
                <Label
                    htmlFor="password_confirmation"
                    hasError={!!passwordConfirmationError}
                    required
                >
                    تأكيد كلمة المرور
                </Label>

                <PasswordInput
                    id="password_confirmation"
                    ref={passwordConfirmationRef}
                    name="password_confirmation"
                    autoComplete="new-password"
                    className="not-placeholder-shown:font-mono"
                    hasError={!!passwordConfirmationError}
                    visible={isPasswordVisible}
                    onVisibleChange={setIsPasswordVisible}
                    required
                />

                <InputError message={passwordConfirmationError} />
            </Field>
        </>
    );
}
