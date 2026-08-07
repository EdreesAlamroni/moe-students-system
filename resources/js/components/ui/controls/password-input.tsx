import { useState } from 'react';

import type { ComponentProps, Ref } from 'react';

import { cn } from '@/lib/utils';

import { Input } from '@/components/ui/controls/input';

import { DicesIcon, Eye, EyeOff } from 'lucide-react';

type PasswordInputProps = Omit<ComponentProps<'input'>, 'type'> & {
    ref?: Ref<HTMLInputElement>;
    hasError?: boolean;
    visible?: boolean;
    onVisibleChange?: (visible: boolean) => void;
};

type GeneratePasswordButtonProps = {
    onGenerate: () => void;
    className?: string;
    disabled?: boolean;
};

export function GeneratePasswordButton({
    onGenerate,
    className,
    disabled = false,
}: GeneratePasswordButtonProps) {
    const [isAnimating, setIsAnimating] = useState(false);

    function handleClick(): void {
        if (disabled || isAnimating) {
            return;
        }

        setIsAnimating(true);
        onGenerate();

        window.setTimeout(() => {
            setIsAnimating(false);
        }, 450);
    }

    return (
        <button
            type="button"
            onClick={handleClick}
            disabled={disabled}
            aria-label="توليد كلمة مرور عشوائية"
            title="توليد كلمة مرور عشوائية مكوّنة من 8 أرقام"
            className={cn(
                'group/generate inline-flex shrink-0 items-center gap-1.5 rounded-none border border-transparent',
                'px-2 py-0 text-[11px] font-semibold tracking-wide uppercase',
                'text-muted-foreground transition-all duration-200',
                'hover:border-primary/15 hover:bg-primary/5 hover:text-primary',
                'focus-visible:border-ring focus-visible:ring-2 focus-visible:ring-ring/30 focus-visible:outline-none',
                'disabled:pointer-events-none disabled:opacity-50',
                className,
            )}
        >
            <span
                className={cn(
                    'flex size-3 items-center justify-center text-muted-foreground transition-all duration-300',
                    'group-hover/generate:text-primary',
                    isAnimating && 'text-primary',
                )}
            >
                <DicesIcon
                    className={cn(
                        'size-3 transition-transform duration-300 ease-out',
                        isAnimating && 'rotate-180 scale-110',
                    )}
                />
            </span>
            <span>توليد عشوائي</span>
        </button>
    );
}

export default function PasswordInput({
    className,
    ref,
    hasError = false,
    visible,
    onVisibleChange,
    ...props
}: PasswordInputProps) {
    const [internalVisible, setInternalVisible] = useState(false);
    const isControlled = visible !== undefined;
    const showPassword = isControlled ? visible : internalVisible;

    function toggleVisible(): void {
        const nextVisible = !showPassword;

        if (isControlled) {
            onVisibleChange?.(nextVisible);

            return;
        }

        setInternalVisible(nextVisible);
    }

    return (
        <div className="relative">
            <Input
                type={showPassword ? 'text' : 'password'}
                className={cn('pe-10 not-placeholder-shown:font-mono', showPassword && 'tracking-widest', className)}
                ref={ref}
                hasError={hasError}
                {...props}
            />
            <button
                type="button"
                onClick={toggleVisible}
                className="absolute inset-y-0 inset-e-0 flex items-center rounded-r-md px-3 text-muted-foreground hover:text-foreground focus-visible:ring-[3px] focus-visible:ring-ring focus-visible:outline-none"
                aria-label={showPassword ? 'إخفاء كلمة المرور' : 'إظهار كلمة المرور'}
                tabIndex={-1}
            >
                {showPassword ? (
                    <EyeOff className="size-4" />
                ) : (
                    <Eye className="size-4" />
                )}
            </button>
        </div>
    );
}
