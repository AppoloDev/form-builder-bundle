import React, { createContext, useContext, useMemo } from "react";
import { UniqueIdentifier, useDroppable } from "@dnd-kit/core";
import { SortableContext, useSortable, verticalListSortingStrategy } from "@dnd-kit/sortable";
import { CSS } from "@dnd-kit/utilities";
import { Block, BlockId } from "./Definition";
import { BLOCK_COMPONENTS } from "../BlockRegistry";
import { DragHandleContext } from "../../FormBuilder";
import type { ContainerId } from "../../stores/block.store";

/**
 * Vrai sous les champs d'une règle de condition (y compris dans un groupe imbriqué) : ces champs sont masqués tant que
 * la condition n'est pas remplie, ils ne peuvent donc pas être obligatoires (cf. ConditionalFieldType côté PHP).
 */
const ConditionalChildrenContext = createContext(false);

const FollowUpRenderer: React.FC<{ child: Block; conditional: boolean }> = ({child, conditional}) => {
    const Comp = BLOCK_COMPONENTS[child.type];
    if (!Comp) return null;
    // Un groupe ou un répétable garde « Requis » et les conditions de ses champs. Les champs d'une règle de
    // condition n'ont ni l'un ni l'autre : ils ne sont jamais obligatoires et les conditions ne s'imbriquent pas.
    return <Comp {...child} useContionnalField={!conditional} isChildBlock={conditional} />;
};

/**
 * Bloc déplaçable. Un seul `DndContext` (FormBuilder) couvre tout l'arbre : `containerId` indique la liste du bloc,
 * pour pouvoir l'amener dans une autre (groupe, répétable, règle de condition, racine).
 */
export const SortableBlock: React.FC<{
    id: UniqueIdentifier;
    containerId: ContainerId;
    children: React.ReactNode;
}> = ({id, containerId, children}) => {
    const {
        attributes,
        listeners,
        setNodeRef,
        setActivatorNodeRef,
        transform,
        transition,
        isDragging,
    } = useSortable({id, data: {containerId}});

    const style: React.CSSProperties = {
        transform: CSS.Transform.toString(transform),
        transition,
        opacity: isDragging ? 0.6 : 1,
    };

    return (
        <DragHandleContext.Provider
            value={{attributes, listeners, setActivatorNodeRef, isDragging}}
        >
            <div
                ref={setNodeRef}
                style={style}
                data-block-id={String(id)}
                className="relative"
            >
                {children}
            </div>
        </DragHandleContext.Provider>
    );
};

/** Zone de dépôt d'une liste : reçoit un bloc lâché entre ses éléments ou dans la liste vide. */
export const DropZone: React.FC<{
    containerId: ContainerId;
    className?: string;
    children: React.ReactNode;
}> = ({containerId, className = "", children}) => {
    const {setNodeRef, isOver} = useDroppable({id: `zone:${containerId ?? "root"}`, data: {zone: true, containerId}});

    return (
        <div ref={setNodeRef} className={`${className} ${isOver ? "ring-2 ring-primary/40" : ""}`}>
            {children}
        </div>
    );
};

export const ChildrenSorter: React.FC<{
    childrenBlocks: Block[];
    /** Groupe, répétable ou règle de condition qui contient ces blocs. */
    containerId: BlockId;
    /** Enfants d'une règle de condition : champs jamais obligatoires. */
    conditional?: boolean;
}> = ({childrenBlocks, containerId, conditional = false}) => {
    const inheritedConditional = useContext(ConditionalChildrenContext);
    const isConditional = conditional || inheritedConditional;
    const childIds = useMemo(() => childrenBlocks.map((c) => c.id), [childrenBlocks]);

    return (
        <ConditionalChildrenContext.Provider value={isConditional}>
            <SortableContext items={childIds} strategy={verticalListSortingStrategy}>
                <div className={conditional ? "flex flex-col gap-3" : "flex flex-col gap-5"}>
                    {childrenBlocks.map((child) => (
                        <SortableBlock key={child.id} id={child.id} containerId={containerId}>
                            <FollowUpRenderer child={child} conditional={isConditional}/>
                        </SortableBlock>
                    ))}
                </div>
            </SortableContext>
        </ConditionalChildrenContext.Provider>
    );
};
