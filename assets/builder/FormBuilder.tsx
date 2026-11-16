import React, { useEffect, useMemo, useState, createContext } from "react";
import { createPortal } from "react-dom";
import { Block, BlockDefinition } from "./components/Blocks/Definition";
import { MoveTarget, useFormBuilderStore } from "./stores/block.store";
import { createBlockFromTemplate } from "./utilities/block.utiles";
import { BLOCK_COMPONENTS } from "./components/BlockRegistry";
import { Empty } from "./components/Empty";

import {
    DndContext,
    DragStartEvent,
    DragEndEvent,
    DragMoveEvent,
    DragOverlay,
    MeasuringStrategy,
    PointerSensor,
    useSensor,
    useSensors,
    closestCenter,
    pointerWithin,
    CollisionDetection,
    UniqueIdentifier,
} from "@dnd-kit/core";

import { SortableContext } from "@dnd-kit/sortable";
import { DropZone, noShiftStrategy, SortableBlock } from "./components/Blocks/ChildrenSorter";
import { AddButton } from "./components/AddButton";
import { AllowedTypesContext } from "./components/AllowedTypesContext";
import { resolveAllowedTypes } from "./utilities/allowedTypes";

type Props = {
    onChange: (blocks: Block[]) => void;
    json: Block[];
    /** Catégories de champs proposées à l'ajout (absent = toutes). */
    categories?: string[];
    /** Types de champs proposés à l'ajout, en plus des catégories (absent = tous). */
    types?: string[];
};

export const DragHandleContext = createContext<{
    attributes?: Record<string, any>;
    listeners?: Record<string, any>;
    setActivatorNodeRef?: (node: HTMLElement | null) => void;
    isDragging?: boolean;
}>({});

/**
 * Les listes sont imbriquées (groupes, répétables, règles de condition) : on retient la cible la plus profonde
 * sous le pointeur. Sur la zone d'une liste (entre deux blocs), on prend le bloc le plus proche de cette liste.
 * Le bloc déplacé et son contenu sont exclus : on ne dépose pas un groupe en lui-même.
 */
const collisionDetection: CollisionDetection = (args) => {
    const activeNode = args.droppableContainers.find((c) => c.id === args.active.id)?.node.current;
    const droppableContainers = args.droppableContainers.filter(
        (c) => c.id === args.active.id || !activeNode?.contains(c.node.current)
    );
    const scoped = {...args, droppableContainers};

    const area = (id: UniqueIdentifier) => {
        const rect = args.droppableRects.get(id);
        return rect ? rect.width * rect.height : Infinity;
    };
    const isZone = (id: UniqueIdentifier) => String(id).startsWith("zone:");
    // À surface égale, la zone (intérieur d'un groupe) l'emporte sur le bloc qui la porte.
    const [deepest] = pointerWithin(scoped).sort(
        (a, b) => area(a.id) - area(b.id) || Number(isZone(b.id)) - Number(isZone(a.id))
    );
    if (!deepest) return closestCenter(scoped);

    const zone = droppableContainers.find((c) => c.id === deepest.id)?.data.current;
    if (zone?.zone) {
        const items = droppableContainers.filter(
            (c) => !c.data.current?.zone && c.data.current?.containerId === zone.containerId
        );
        if (items.length > 0) return closestCenter({...scoped, droppableContainers: items});
    }

    return [deepest];
};

/**
 * Où le bloc serait déposé : à côté du bloc survolé (après si son centre est plus bas que celui de ce dernier),
 * ou dans une liste vide (zone). `null` : sur lui-même ou hors de toute liste.
 */
const dropTargetOf = ({active, over}: DragMoveEvent | DragEndEvent): MoveTarget | null => {
    if (!over || active.id === over.id) return null;
    if (over.data.current?.zone) return {containerId: over.data.current.containerId};
    const dropped = active.rect.current.translated;
    return {overId: over.id, after: !!dropped && dropped.top + dropped.height / 2 > over.rect.top + over.rect.height / 2};
};

export const FormBuilder = ({onChange, json = [], categories, types}: Props) => {
    const allowedTypes = useMemo(() => resolveAllowedTypes(categories, types), [categories, types]);
    const {blocks, addBlock, setBlocks, moveBlock} = useFormBuilderStore();
    // Barre d'insertion, dans <body> au-dessus du calque de glisser (sinon la copie du bloc déplacé la recouvre),
    // sous les infobulles.
    const [bar, setBar] = useState<{ top: number; left: number; width: number } | null>(null);
    const [activeId, setActiveId] = useState<UniqueIdentifier | null>(null);
    // Copie du DOM du bloc déplacé, affichée sous le pointeur (une copie React dupliquerait ses zones de dépôt).
    const [activePreview, setActivePreview] = useState<HTMLElement | null>(null);
    const sensors = useSensors(
        useSensor(PointerSensor, {
            activationConstraint: {
                distance: 2,
            },
        })
    );

    const handleAdd = (def: BlockDefinition) => addBlock(createBlockFromTemplate(def));

    useEffect(() => {
        setBlocks(json);
    }, []);

    useEffect(() => {
        onChange(blocks);
    }, [blocks]);

    // Sélection (clic sur une poignée) : un clic ailleurs ou Échap la retire.
    useEffect(() => {
        const clear = () => useFormBuilderStore.getState().select(null);
        const onPointerDown = (e: PointerEvent) => {
            if (!(e.target as Element).closest?.("[data-block-handle]")) clear();
        };
        const onKeyDown = (e: KeyboardEvent) => e.key === "Escape" && clear();
        document.addEventListener("pointerdown", onPointerDown);
        document.addEventListener("keydown", onKeyDown);
        return () => {
            document.removeEventListener("pointerdown", onPointerDown);
            document.removeEventListener("keydown", onKeyDown);
        };
    }, []);

    const ids = useMemo(() => blocks.map((b) => b.id), [blocks]);

    const onDragStart = (event: DragStartEvent) => {
        setActiveId(event.active.id);

        const el = document.querySelector<HTMLElement>(`[data-block-id="${String(event.active.id)}"]`);
        if (el) {
            const clone = el.cloneNode(true) as HTMLElement;
            clone.removeAttribute("data-block-id");
            clone.removeAttribute("style");
            clone.style.width = `${el.getBoundingClientRect().width}px`;
            setActivePreview(clone);
        } else {
            setActivePreview(null);
        }
    };

    /** Seule la barre d'insertion suit le pointeur : l'arbre n'est modifié qu'au lâcher. */
    const onDragMove = (event: DragMoveEvent) => {
        const target = dropTargetOf(event);
        const el = target && "overId" in target
            ? document.querySelector<HTMLElement>(`[data-block-id="${String(target.overId)}"]`)
            : null;
        if (!target || !("overId" in target) || !el) return setBar(null);

        // Au milieu de l'espace avec le bloc voisin, quel que soit l'écart de la liste (racine, groupe, condition).
        const rect = el.getBoundingClientRect();
        const sibling = target.after ? el.nextElementSibling : el.previousElementSibling;
        const near = sibling?.hasAttribute("data-block-id") ? sibling.getBoundingClientRect() : null;
        const top = target.after
            ? (near ? (rect.bottom + near.top) / 2 : rect.bottom + 4)
            : (near ? (near.bottom + rect.top) / 2 : rect.top - 4);
        setBar({top, left: rect.left, width: rect.width});
    };

    const onDragEnd = (event: DragEndEvent) => {
        const target = dropTargetOf(event);
        if (target) moveBlock(event.active.id, target);
        onDragCancel();
    };

    const onDragCancel = () => {
        setActiveId(null);
        setActivePreview(null);
        setBar(null);
    };

    return (
        <AllowedTypesContext.Provider value={allowedTypes}>
        {/* Entrée dans un champ du builder ne soumet pas le formulaire hôte (soumission implicite). Les menus et
            fenêtres en portail remontent aussi ici (arbre React). Zones multilignes et boutons non concernés. */}
        <div className="contents" onKeyDown={(e) => e.key === "Enter" && e.target instanceof HTMLInputElement && e.preventDefault()}>
        <DndContext
            sensors={sensors}
            collisionDetection={collisionDetection}
            measuring={{droppable: {strategy: MeasuringStrategy.Always}}}
            onDragStart={onDragStart}
            onDragMove={onDragMove}
            onDragEnd={onDragEnd}
            onDragCancel={onDragCancel}
        >
            <DropZone containerId={null} className={`border border-dashed border-border rounded-lg ${blocks.length === 0 ? "" : "py-20 pr-20 pl-40"}`}>
                {blocks.length === 0 ? (
                    <Empty onPick={handleAdd}/>
                ) : (
                    <div className="mx-auto max-w-2xl space-y-8">
                    <SortableContext items={ids} strategy={noShiftStrategy}>
                        {blocks.map((block) => {
                            const Component = BLOCK_COMPONENTS[block.type];
                            if (!Component) {
                                console.error(`Composant non trouvé pour le type: ${block.type}`);
                                return null;
                            }
                            return (
                                <SortableBlock key={block.id} id={block.id} containerId={null}>
                                    <Component {...block} />
                                </SortableBlock>
                            );
                        })}
                        <AddButton onPick={handleAdd}/>
                    </SortableContext>
                    </div>
                )}
            </DropZone>

            {/* Calques : bloc déplacé (40) < barre d'insertion (45) < infobulles (z-50). */}
            <DragOverlay dropAnimation={null} zIndex={40}>
                {activeId && activePreview ? (
                    <div
                        className="pointer-events-none opacity-60"
                        ref={(node) => node?.replaceChildren(activePreview)}
                    />
                ) : null}
            </DragOverlay>
            {bar && createPortal(
                <div
                    aria-hidden="true"
                    className="fixed z-[45] h-1 -translate-y-1/2 rounded-full bg-blue-500 pointer-events-none"
                    style={bar}
                />,
                document.body
            )}
        </DndContext>
        </div>
        </AllowedTypesContext.Provider>
    );
};

export default FormBuilder;
