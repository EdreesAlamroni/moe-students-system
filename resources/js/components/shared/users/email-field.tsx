import Field from '@/components/ui/controls/field';
import { Label } from '@/components/ui/controls/label';
import { Input } from '@/components/ui/controls/input';
import InputError from '@/components/ui/controls/input-error';

import { cn } from '@/lib/utils';

export const isEmailFieldVisible = false;

type EmailFieldProps = {
    error?: string;
    defaultValue?: string;
    className?: string;
};

export default function EmailField({
    error,
    defaultValue,
    className,
}: EmailFieldProps) {
    return (
        <Field className={cn(className, !isEmailFieldVisible && 'hidden')}>
            <Label
                htmlFor="email"
                hasError={!!error}
            >
                البريد الإلكتروني
            </Label>

            <Input
                id="email"
                type="email"
                name="email"
                defaultValue={defaultValue}
                hasError={!!error}
                autoComplete="email"
            />

            <InputError message={error} />
        </Field>
    );
}
