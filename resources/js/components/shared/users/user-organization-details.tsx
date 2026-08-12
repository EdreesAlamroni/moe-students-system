import { DetailField } from '@/components/ui/display/detail-field';
import { DetailLabel } from '@/components/ui/display/detail-label';
import { DetailValue } from '@/components/ui/display/detail-value';

import type { OrganizationDisplay } from '@/lib/user-organization';

type UserOrganizationDetailsProps = {
    organization: OrganizationDisplay | null;
    isSchoolUser?: boolean;
    /**
     * `full` — hierarchical dashboards (show parent/org; for school users only show parent).
     * `simple` — warehouse/school dashboards (single org detail field).
     */
    mode?: 'full' | 'simple';
};

export default function UserOrganizationDetails({
    organization,
    isSchoolUser = false,
    mode = 'full',
}: UserOrganizationDetailsProps) {
    if (!organization) {
        return null;
    }

    if (mode === 'simple') {
        return (
            <DetailField>
                <DetailLabel>{organization.label}</DetailLabel>
                <DetailValue value={organization.name} />
            </DetailField>
        );
    }

    return (
        <>
            {!isSchoolUser && (
                <>
                    {organization.parent && (
                        <DetailField>
                            <DetailLabel>{organization.parent.label}</DetailLabel>
                            <DetailValue value={organization.parent.name} />
                        </DetailField>
                    )}

                    <DetailField>
                        <DetailLabel>{organization.label}</DetailLabel>
                        <DetailValue value={organization.name} />
                    </DetailField>
                </>
            )}

            {isSchoolUser && organization.parent && (
                <DetailField>
                    <DetailLabel>{organization.parent.label}</DetailLabel>
                    <DetailValue value={organization.parent.name} />
                </DetailField>
            )}
        </>
    );
}
