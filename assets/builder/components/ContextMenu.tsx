import { ReactNode } from "react";
import { Dialog, DialogContent, DialogHeader, DialogTitle } from "./ui/dialog";

interface ContextMenuProps {
    visible: boolean;
    onClose: () => void;
    children: ReactNode;
    title?: string;
}

export const ContextMenu = (
    {
        visible,
        onClose,
        children,
        title = "Configuration du champ"
    }: ContextMenuProps) => {
    return (
        <Dialog open={visible} onOpenChange={(open) => {
            if (!open) onClose();
        }}>
            <DialogContent className="max-h-[80vh] overflow-y-auto">
                <DialogHeader>
                    <DialogTitle>{title}</DialogTitle>
                </DialogHeader>

                <div className="space-y-4">
                    {children}
                </div>
            </DialogContent>
        </Dialog>
    );
};

interface ContextMenuItemProps {
    children: ReactNode;
    className?: string;
}

export const ContextMenuItem = ({children, className = ""}: ContextMenuItemProps) => {
    return (
        <div className={`${className}`}>
            {children}
        </div>
    );
};
