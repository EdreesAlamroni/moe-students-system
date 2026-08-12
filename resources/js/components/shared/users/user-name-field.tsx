import Field from '@/components/ui/controls/field';
import { Label } from '@/components/ui/controls/label';
import { Input } from '@/components/ui/controls/input';
import InputError from '@/components/ui/controls/input-error';

type UserNameFieldProps = {
    error?: string;
    defaultValue?: string;
    className?: string;
    inputClassName?: string;
};

export default function UserNameField({
    error,
    defaultValue,
    className,
    inputClassName,
}: UserNameFieldProps) {
    return (
        <Field className={className}>
            <Label
                htmlFor="name"
                hasError={!!error}
                required
            >
                الاسم
            </Label>

            <Input
                id="name"
                type="text"
                name="name"
                className={inputClassName}
                defaultValue={defaultValue}
                hasError={!!error}
                autoComplete="name"
                required
            />

            <InputError message={error} />
        </Field>
    );
}
