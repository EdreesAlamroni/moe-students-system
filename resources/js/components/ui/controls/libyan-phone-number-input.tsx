import React from 'react'

import { cn } from '@/lib/utils'
import { libyanPhoneNumberInputConstraints } from '@/lib/input-constraints'

import { Input } from '@/components/ui/controls/input'

type LibyanPhoneNumberInputProps = React.ComponentProps<typeof Input>;

export default function LibyanPhoneNumberInput({
    className,
    hasError = false,
    title = "الرجاء إدخال رقم هاتف ليبي بصيغة صحيحة",
    autoComplete = "off",
    ...props
}: LibyanPhoneNumberInputProps) {

    return (
        <Input
            type="tel"
            className={cn("font-mono", className)}
            autoComplete={autoComplete}
            hasError={hasError}
            title={title}
            {...libyanPhoneNumberInputConstraints()}
            {...props}
        />
    )
}

