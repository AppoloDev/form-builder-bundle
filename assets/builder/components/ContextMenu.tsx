import { ReactElement, ReactNode } from "react";
import { Popover } from "@base-ui/react/popover";
import { type Icon, TrashIcon } from "@phosphor-icons/react";
import { Button } from "./ui/button";
import { t } from "../i18n";

interface ContextMenuProps {
    /** Bouton qui ouvre le menu ; le menu s'ancre sur lui. */
    trigger: ReactElement;
    open: boolean;
    onOpenChange: (open: boolean) => void;
    title?: string;
    icon?: Icon;
    onDelete: () => void;
    children: ReactNode;
}

/** Menu d'options d'un bloc « à la Tally » : en-tête, options, puis actions. */
export const ContextMenu = ({trigger, open, onOpenChange, title, icon: Icon, onDelete, children}: ContextMenuProps) => (
    <Popover.Root open={open} onOpenChange={onOpenChange}>
        <Popover.Trigger render={trigger}/>
        <Popover.Portal>
            <Popover.Positioner side="bottom" align="start" sideOffset={6} className="z-50">
                <Popover.Popup
                    className="w-72 max-h-[min(70vh,32rem)] overflow-y-auto rounded-xl bg-popover text-sm text-popover-foreground shadow-md ring-1 ring-foreground/10 outline-none">
                    {title && (
                        <div className="flex items-center gap-2 border-b border-border px-3 py-2.5 font-medium">
                            {Icon && <Icon className="size-4 text-muted-foreground" aria-hidden="true"/>}
                            <Popover.Title className="text-sm">{title}</Popover.Title>
                        </div>
                    )}

                    <div className="space-y-3 px-3 py-3">
                        {children}
                    </div>

                    <div className="border-t border-border p-1">
                        <Button
                            type="button"
                            variant="ghost"
                            className="w-full justify-start text-destructive hover:text-destructive"
                            onClick={onDelete}
                        >
                            <TrashIcon/> {t("block.delete")}
                        </Button>
                    </div>
                </Popover.Popup>
            </Popover.Positioner>
        </Popover.Portal>
    </Popover.Root>
);
