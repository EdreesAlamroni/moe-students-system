import React from "react";

import type { RoleGroup } from "@/types/auth";

function flattenRoleIds(groupedRoles: Record<string, RoleGroup> | RoleGroup[]): number[] {
    const groups = Array.isArray(groupedRoles)
        ? groupedRoles
        : Object.values(groupedRoles);

    return groups.flatMap((group) => group.roles.map((role) => role.id));
}

export function useGroupedRolesSelection(
    groupedRoles: Record<string, RoleGroup> | RoleGroup[],
    initialSelected: number[] = [],
) {
    const [selectedRoles, setSelectedRoles] = React.useState<number[]>(initialSelected);

    const allRoleIds = React.useMemo(
        () => flattenRoleIds(groupedRoles),
        [groupedRoles],
    );

    const allRolesChecked = allRoleIds.length > 0 && selectedRoles.length === allRoleIds.length;
    const someRolesChecked = selectedRoles.length > 0 && selectedRoles.length < allRoleIds.length;

    const toggleRole = (roleId: number, checked: boolean): void => {
        setSelectedRoles((prev) => (checked ? [...prev, roleId] : prev.filter((id) => id !== roleId)));
    };

    const toggleAllRoles = (checked: boolean | "indeterminate"): void => {
        setSelectedRoles(checked === true ? allRoleIds : []);
    };

    const getGroupRoleIds = React.useCallback((group: RoleGroup): number[] => {
        return group.roles.map((role) => role.id);
    }, []);

    const isGroupAllChecked = React.useCallback(
        (group: RoleGroup): boolean => {
            const ids = getGroupRoleIds(group);

            return ids.length > 0 && ids.every((id) => selectedRoles.includes(id));
        },
        [getGroupRoleIds, selectedRoles],
    );

    const isGroupSomeChecked = React.useCallback(
        (group: RoleGroup): boolean => {
            const ids = getGroupRoleIds(group);
            const hasSome = ids.some((id) => selectedRoles.includes(id));

            return hasSome && !isGroupAllChecked(group);
        },
        [getGroupRoleIds, isGroupAllChecked, selectedRoles],
    );

    const toggleGroupRoles = (group: RoleGroup, checked: boolean | "indeterminate"): void => {
        const ids = getGroupRoleIds(group);

        setSelectedRoles((prev) => {
            if (checked === true) {
                return Array.from(new Set([...prev, ...ids]));
            }

            const toRemove = new Set(ids);

            return prev.filter((id) => !toRemove.has(id));
        });
    };

    return {
        selectedRoles,
        allRolesChecked,
        someRolesChecked,
        toggleRole,
        toggleAllRoles,
        isGroupAllChecked,
        isGroupSomeChecked,
        toggleGroupRoles,
    };
}
