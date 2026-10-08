import React, { ReactElement, ReactNode, useState, MouseEvent, useCallback, useContext } from "react";
import { Tooltip } from "../Tooltip";
import { UniqueIdentifier } from "@dnd-kit/core";
import { ContextMenu, ContextMenuItem } from "../ContextMenu";
import { useBlockOperations } from "../../hooks/useBlockOperations";
import { DragHandleContext } from "../../FormBuilder";
import { Button } from "../ui/button";
import { GripVertical, SquarePen, Trash } from "lucide-react";
import { BlockType, blockDefinitions } from "./Definition";

interface EditableBlockProps {
    id: UniqueIdentifier;
    type?: BlockType;
    editionItems?: ReactElement | ReactElement[];
    children: ReactNode;
    onDelete?: () => void;
    className?: string;
    preview?: boolean;
}

export const EditableBlock = (
    {
        id,
        type,
        editionItems = [],
        children,
        onDelete,
        className = "",
        preview = false,
    }: EditableBlockProps) => {
    const typeLabel = type ? blockDefinitions[type]?.title : undefined;
    const [contextMenuVisible, setContextMenuVisible] = useState(false);

    const {handleRemove} = useBlockOperations(id);

    const handleOpenContextMenu = useCallback((e: MouseEvent) => {
        e.preventDefault();
        e.stopPropagation();

        setContextMenuVisible(true);
    }, []);

    const handleCloseContextMenu = useCallback(() => {
        setContextMenuVisible(false);
    }, []);

    const handleDelete = useCallback(() => {
        onDelete?.();
        handleRemove();
        handleCloseContextMenu();
    }, [onDelete, handleRemove, handleCloseContextMenu]);

    const items = Array.isArray(editionItems) ? editionItems : [editionItems];

    const {attributes, listeners, setActivatorNodeRef} = useContext(DragHandleContext);

    if (preview) {
        return <>{children}</>;
    }

    return (
        <div className={`group relative ${className}`}>
            {/* Invisible bridge so the cursor doesn't lose hover crossing the gap to the floating toolbar */}
            <div className="absolute top-0 right-full h-full w-24" aria-hidden="true"/>

            <div className="absolute right-full mr-2 z-10 flex items-center gap-0.5 p-0.5 opacity-0 transition-opacity pointer-events-none group-hover:opacity-100 group-hover:pointer-events-auto">
                {items.length > 0 && (
                    <Tooltip content="Paramètres">
                        <Button
                            variant="ghost"
                            size="icon-sm"
                            onClick={handleOpenContextMenu}
                            aria-label="Ouvrir les paramètres"
                        >
                            <SquarePen />
                        </Button>
                    </Tooltip>
                )}

                <Tooltip content="Supprimer">
                    <Button
                        variant="ghost"
                        size="icon-sm"
                        className="text-destructive"
                        onClick={handleDelete}
                        aria-label="Supprimer le bloc"
                    >
                        <Trash />
                    </Button>
                </Tooltip>

                <Tooltip content="Déplacer le bloc">
                    <Button
                        variant="ghost"
                        size="icon-sm"
                        ref={setActivatorNodeRef}
                        {...(attributes || {})}
                        {...(listeners || {})}
                        className="cursor-grab active:cursor-grabbing"
                    >
                        <GripVertical />
                    </Button>
                </Tooltip>
            </div>

            {children}

            {items.length > 0 && (
                <ContextMenu
                    visible={contextMenuVisible}
                    onClose={handleCloseContextMenu}
                    title={typeLabel ? `Configuration du champ — ${typeLabel}` : undefined}
                >
                    {items.map((item, index) => (
                        <ContextMenuItem key={item.key || index}>{item}</ContextMenuItem>
                    ))}
                </ContextMenu>
            )}
        </div>
    );
};
