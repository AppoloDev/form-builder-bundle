import { ReactElement, ReactNode, useState, useCallback, useContext } from "react";
import { Tooltip } from "../Tooltip";
import { UniqueIdentifier } from "@dnd-kit/core";
import { ContextMenu } from "../ContextMenu";
import { useBlockOperations } from "../../hooks/useBlockOperations";
import { DragHandleContext } from "../../FormBuilder";
import { Button } from "../ui/button";
import { type Icon, DotsSixVerticalIcon, PencilSimpleIcon, TrashIcon } from "@phosphor-icons/react";
import { BlockType, blockDefinitions } from "./Definition";
import { t } from "../../i18n";

interface EditableBlockProps {
    id: UniqueIdentifier;
    type?: BlockType;
    editionItems?: ReactElement | ReactElement[];
    children: ReactNode;
    onDelete?: () => void;
    className?: string;
    /** En-tête du menu d'options, à la place de celui du type (ex. « Titre 2 » pour un titre de niveau 2). */
    title?: string;
    icon?: Icon;
}

export const EditableBlock = (
    {
        id,
        type,
        editionItems = [],
        children,
        onDelete,
        className = "",
        title,
        icon,
    }: EditableBlockProps) => {
    const definition = type ? blockDefinitions[type] : undefined;
    const [menuOpen, setMenuOpen] = useState(false);

    const {handleRemove} = useBlockOperations(id);

    const handleDelete = useCallback(() => {
        onDelete?.();
        handleRemove();
        setMenuOpen(false);
    }, [onDelete, handleRemove]);

    // Le libellé s'édite directement dans le bloc ; « Requis » en tête du menu, comme Tally.
    const items = (Array.isArray(editionItems) ? editionItems : [editionItems])
        .filter((item) => item.key !== "label")
        .sort((a, b) => Number(b.key === "required") - Number(a.key === "required"));

    const {attributes, listeners, setActivatorNodeRef} = useContext(DragHandleContext);

    return (
        <div className={`fb-block relative ${className}`}>
            {/* Barre d'outils visible quand CE bloc est survolé (sélecteur enfant direct) : `group-hover` la ferait
                apparaître aussi quand un bloc parent est survolé, donc toutes celles des blocs d'un groupe. */}
            {/* Invisible bridge so the cursor doesn't lose hover crossing the gap to the floating toolbar */}
            <div className="absolute top-0 right-full h-full w-24" aria-hidden="true"/>

            <div className="absolute right-full top-1/2 -translate-y-1/2 mr-2 z-10 flex items-center gap-0.5 p-0.5 opacity-0 transition-opacity pointer-events-none [.fb-block:hover>&]:opacity-100 [.fb-block:hover>&]:pointer-events-auto has-[[data-popup-open]]:opacity-100">
                {items.length > 0 && (
                    <Tooltip content={t("block.settings")}>
                        <ContextMenu
                            open={menuOpen}
                            onOpenChange={setMenuOpen}
                            title={title ?? definition?.title}
                            icon={icon ?? definition?.icon}
                            onDelete={handleDelete}
                            trigger={
                                <Button variant="ghost" size="icon-sm" aria-label={t("block.openSettings")}>
                                    <PencilSimpleIcon/>
                                </Button>
                            }
                        >
                            {items}
                        </ContextMenu>
                    </Tooltip>
                )}

                <Tooltip content={t("block.delete")}>
                    <Button
                        variant="ghost"
                        size="icon-sm"
                        className="text-destructive"
                        onClick={handleDelete}
                        aria-label={t("block.deleteAria")}
                    >
                        <TrashIcon />
                    </Button>
                </Tooltip>

                <Tooltip content={t("block.move")}>
                    <Button
                        variant="ghost"
                        size="icon-sm"
                        ref={setActivatorNodeRef}
                        {...(attributes || {})}
                        {...(listeners || {})}
                        className="cursor-grab active:cursor-grabbing"
                    >
                        <DotsSixVerticalIcon />
                    </Button>
                </Tooltip>
            </div>

            {children}
        </div>
    );
};
