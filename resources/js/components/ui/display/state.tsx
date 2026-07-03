import { cn } from "@/lib/utils";

import { ModelState } from "@/types";

function StateIndicator({ state, className = "", childClassName = "" }: {
    state: string;
    className?: string;
    childClassName?: string;
}) {
    return (
        <div className={cn(
            `inline-flex items-center justify-center w-4 h-4 mt-[2px] p-1 rounded-full state-indicator--${state}`,
            className,
        )}>
            <div className={cn(
                `block w-[0.35rem] h-[0.35rem] rounded-full bg-current`,
                childClassName
            )}></div>
        </div>
    );
}

function StatePill({ state, className = "" }: {
    state: Pick<ModelState, "label" | "uiClasses">;
    className?: string;
}) {
    return (
        <div className={cn(
            state.uiClasses,
            className,
        )}>
            <span>{state.label}</span>
        </div>
    );
}

export { StateIndicator, StatePill };
