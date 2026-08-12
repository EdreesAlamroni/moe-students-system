import { DetailField } from '@/components/ui/display/detail-field';
import { DetailLabel } from '@/components/ui/display/detail-label';
import { DetailValue } from '@/components/ui/display/detail-value';

export type UserContextDetailItem = {
    label: string;
    value?: string | null;
    mono?: boolean;
    className?: string;
};

type UserContextDetailsProps = {
    items: UserContextDetailItem[];
};

export default function UserContextDetails({ items }: UserContextDetailsProps) {
    return (
        <>
            {items.map((item) => (
                <DetailField key={item.label} className={item.className}>
                    <DetailLabel>{item.label}</DetailLabel>
                    <DetailValue
                        value={item.value}
                        className={item.mono ? 'font-mono' : undefined}
                    />
                </DetailField>
            ))}
        </>
    );
}
