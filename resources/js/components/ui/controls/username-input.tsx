import { useState } from 'react';

import type { ComponentProps, Ref } from 'react';

import { cn } from '@/lib/utils';
import { usernameInputConstraints } from '@/lib/input-constraints';

import { Input } from '@/components/ui/controls/input';

import { DicesIcon } from 'lucide-react';

type UsernameInputProps = Omit<ComponentProps<'input'>, 'type' | 'name'> & {
    ref?: Ref<HTMLInputElement>;
    hasError?: boolean;
    name?: string;
};

type GenerateUsernameButtonProps = {
    onGenerate: () => void;
    className?: string;
    disabled?: boolean;
};

export function GenerateUsernameButton({
    onGenerate,
    className,
    disabled = false,
}: GenerateUsernameButtonProps) {
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
            aria-label="توليد اسم مُستخدم عشوائي"
            title="توليد اسم مُستخدم عشوائي مكوّن من 8 أحرف وأرقام"
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

export default function UsernameInput({
    className,
    ref,
    hasError = false,
    ...props
}: UsernameInputProps) {
    return (
        <Input
            ref={ref}
            type="text"
            name="username"
            className={cn('not-placeholder-shown:font-mono', className)}
            hasError={hasError}
            autoComplete="username"
            spellCheck={false}
            required
            {...usernameInputConstraints()}
            {...props}
        />
    );
}
