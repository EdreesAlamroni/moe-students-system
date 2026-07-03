/**
 * Determine whether a route name matches one or more Laravel-style patterns.
 *
 * Mirrors {@see \Illuminate\Http\Request::routeIs()} wildcard semantics
 * (e.g. `school.students.*` matches `school.students` and any sub-name).
 */
export function matchesRoute(
    patterns: string | string[],
    routeName: string | null | undefined,
): boolean {
    if (! routeName) {
        return false;
    }

    const list = Array.isArray(patterns) ? patterns : [patterns];

    return list.some((pattern) => {
        if (pattern === routeName) {
            return true;
        }

        if (pattern.endsWith('.*')) {
            const prefix = pattern.slice(0, -2);

            return routeName === prefix || routeName.startsWith(`${prefix}.`);
        }

        return false;
    });
}
