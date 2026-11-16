import { PointerEvent, ReactElement, ReactNode, createContext, useState, useCallback, useContext } from "react";
import { Tooltip } from "../Tooltip";
import { UniqueIdentifier } from "@dnd-kit/core";
import { ContextMenu } from "../ContextMenu";
import { useBlockOperations } from "../../hooks/useBlockOperations";
import { useFormBuilderStore } from "../../stores/block.store";
import { DragHandleContext } from "../../FormBuilder";
import { Button } from "../ui/button";
import { type Icon, DotsSixVerticalIcon, PlusIcon, TrashIcon } from "@phosphor-icons/react";
import { BlockType, blockDefinitions } from "./Definition";
import { t } from "../../i18n";
import { AddMenu } from "../AddMenu";
import { createBlockFromTemplate } from "../../utilities/block.utiles";

/**
 * Retrait (px) du contenu par rapport au bloc qui porte la colonne d'actions : un bloc enfant d'une condition
 * (bordure + pl-4 = 20 px) repousse ses actions d'autant pour qu'elles restent dans la colonne de celles du parent,
 * hors de la bordure. Cumulé à chaque condition imbriquée.
 */
export const ActionsInsetContext = createContext(0);

/** Écart entre la colonne d'actions et le contenu (mr-2 de la barre d'outils). */
export const ACTIONS_GAP = 8;

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
    /** Actions du menu d'options, au-dessus de « Supprimer ». */
    actions?: ReactNode;
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
        actions,
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
    const inset = useContext(ActionsInsetContext);
    const selected = useFormBuilderStore((state) => state.selectedId === id);
    const select = useFormBuilderStore((state) => state.select);
    const insertBlockAfter = useFormBuilderStore((state) => state.insertBlockAfter);

    const handle = (
        <Button
            variant="ghost"
            size="icon-sm"
            ref={setActivatorNodeRef}
            {...(attributes || {})}
            {...(listeners || {})}
            className="cursor-grab active:cursor-grabbing"
            data-block-handle=""
            aria-label={t(items.length > 0 ? "block.handle" : "block.move")}
            // Sélection dès l'appui (avant tout glisser), sans écraser l'écouteur de dnd-kit.
            onPointerDown={(e: PointerEvent) => {
                listeners?.onPointerDown?.(e);
                select(id);
            }}
        >
            <DotsSixVerticalIcon/>
        </Button>
    );

    return (
        <div className={`fb-block relative ${className}`}>
            {/* Barre d'outils visible quand CE bloc est survolé (sélecteur enfant direct) : `group-hover` la ferait
                apparaître aussi quand un bloc parent est survolé, donc toutes celles des blocs d'un groupe. */}
            {/* Invisible bridge so the cursor doesn't lose hover crossing the gap to the floating toolbar */}
            <div className="absolute top-0 right-full h-full" style={{width: 96 + inset}} aria-hidden="true"/>

            <div style={{marginRight: ACTIONS_GAP + inset}} className="absolute right-full top-0 z-10 flex items-center gap-0.5 opacity-0 transition-opacity pointer-events-none [.fb-block:hover:not(:has([data-row-actions]:hover))>&]:opacity-100 [.fb-block:hover:not(:has([data-row-actions]:hover))>&]:pointer-events-auto has-[[data-popup-open]]:opacity-100">
                <Tooltip content={t("block.addBelow")}>
                    <AddMenu onPick={(def) => insertBlockAfter(id, createBlockFromTemplate(def))}>
                        <Button variant="ghost" size="icon-sm" aria-label={t("block.addBelow")}>
                            <PlusIcon/>
                        </Button>
                    </AddMenu>
                </Tooltip>

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

                {/* Poignée : glisser pour déplacer (dnd-kit, seuil de 2 px), cliquer pour ouvrir les paramètres. */}
                <Tooltip content={t(items.length > 0 ? "block.handle" : "block.move")}>
                    {items.length > 0 ? (
                        <ContextMenu
                            open={menuOpen}
                            onOpenChange={setMenuOpen}
                            title={title ?? definition?.title}
                            icon={icon ?? definition?.icon}
                            onDelete={handleDelete}
                            actions={actions}
                            trigger={handle}
                        >
                            {items}
                        </ContextMenu>
                    ) : handle}
                </Tooltip>
            </div>

            {children}

            {/* Voile bleu par-dessus tout le contenu du bloc (champs d'un groupe compris), sans bloquer la saisie. */}
            {selected && (
                <div aria-hidden="true" className="absolute -inset-2 z-[5] rounded-md bg-blue-500/10 pointer-events-none"/>
            )}
        </div>
    );
};
