import React, { useCallback, useEffect, useMemo, useRef, useState, createContext } from "react";
import { Block, BlockDefinition } from "./components/Blocks/Definition";
import { ContainerId, findContainer, useFormBuilderStore } from "./stores/block.store";
import { createBlockFromTemplate } from "./utilities/block.utiles";
import { BLOCK_COMPONENTS } from "./components/BlockRegistry";
import { Empty } from "./components/Empty";

import {
    DndContext,
    DragStartEvent,
    DragEndEvent,
    DragOverEvent,
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

import { SortableContext, verticalListSortingStrategy } from "@dnd-kit/sortable";
import { DropZone, SortableBlock } from "./components/Blocks/ChildrenSorter";
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

/** Bloc amené dans une autre liste : après le bloc survolé si son centre est plus bas que celui de ce dernier. */
const isDroppedAfter = ({active, over}: DragOverEvent | DragEndEvent): boolean => {
    const dropped = active.rect.current.translated;
    return !!over && !!dropped && dropped.top + dropped.height / 2 > over.rect.top + over.rect.height / 2;
};

export const FormBuilder = ({onChange, json = [], categories, types}: Props) => {
    const allowedTypes = useMemo(() => resolveAllowedTypes(categories, types), [categories, types]);
    const {blocks, addBlock, setBlocks, moveBlock} = useFormBuilderStore();
    const [activeId, setActiveId] = useState<UniqueIdentifier | null>(null);
    const [activeSize, setActiveSize] = useState<{ width: number; height: number } | null>(null);
    // Juste après un changement de liste, on garde la cible précédente le temps que la mise en page se stabilise
    // (sinon le bloc oscille entre les deux listes). Même parade que l'exemple multi-conteneurs de dnd-kit.
    const lastOverId = useRef<UniqueIdentifier | null>(null);
    const recentlyMoved = useRef(false);
    const detectCollisions: CollisionDetection = useCallback((args) => {
        if (recentlyMoved.current && lastOverId.current !== null) {
            return [{id: lastOverId.current}];
        }
        const collisions = collisionDetection(args);
        lastOverId.current = collisions[0]?.id ?? null;
        return collisions;
    }, []);

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
        requestAnimationFrame(() => {
            recentlyMoved.current = false;
        });
    }, [blocks]);

    const ids = useMemo(() => blocks.map((b) => b.id), [blocks]);

    // Arbre avant le glisser : le bloc change de liste en cours de route, Échap doit tout remettre en place.
    const blocksBeforeDrag = useRef<Block[] | null>(null);

    const onDragStart = (event: DragStartEvent) => {
        setActiveId(event.active.id);
        blocksBeforeDrag.current = blocks;

        const el = document.querySelector<HTMLElement>(`[data-block-id="${String(event.active.id)}"]`);
        if (el) {
            const rect = el.getBoundingClientRect();
            setActiveSize({width: rect.width, height: rect.height});
        } else {
            setActiveSize(null);
        }
    };

    /** Le bloc change de liste dès le survol d'un de ses blocs : la liste d'arrivée s'écarte pour lui faire de la place. */
    const onDragOver = (event: DragOverEvent) => {
        const {active, over} = event;
        if (!over || active.id === over.id) return;

        // Une zone n'est la cible que d'une liste vide : son état vide reste affiché, le bloc n'y entre qu'au lâcher.
        const data = over.data.current;
        if (data?.zone) return;

        const to = data?.containerId as ContainerId | undefined;
        const from = findContainer(useFormBuilderStore.getState().blocks, active.id);
        if (to === undefined || from === undefined || to === from) return;

        recentlyMoved.current = true;
        moveBlock(active.id, {overId: over.id, after: isDroppedAfter(event)});
    };

    const onDragEnd = (event: DragEndEvent) => {
        const {active, over} = event;

        if (over && active.id !== over.id) {
            const data = over.data.current;
            if (data?.zone) {
                moveBlock(active.id, {containerId: data.containerId});
            } else {
                moveBlock(active.id, {overId: over.id, after: isDroppedAfter(event)});
            }
        }

        setActiveId(null);
        setActiveSize(null);
    };

    const onDragCancel = () => {
        if (blocksBeforeDrag.current) {
            useFormBuilderStore.setState({blocks: blocksBeforeDrag.current});
        }
        setActiveId(null);
        setActiveSize(null);
    };

    return (
        <AllowedTypesContext.Provider value={allowedTypes}>
        <DndContext
            sensors={sensors}
            collisionDetection={detectCollisions}
            measuring={{droppable: {strategy: MeasuringStrategy.Always}}}
            onDragStart={onDragStart}
            onDragOver={onDragOver}
            onDragEnd={onDragEnd}
            onDragCancel={onDragCancel}
        >
            <DropZone containerId={null} className={`border border-dashed border-border rounded-lg ${blocks.length === 0 ? "" : "py-20 pr-20 pl-40 space-y-4"}`}>
                {blocks.length === 0 ? (
                    <Empty onPick={handleAdd}/>
                ) : (
                    <SortableContext items={ids} strategy={verticalListSortingStrategy}>
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
                )}
            </DropZone>

            <DragOverlay dropAnimation={null}>
                {activeId && activeSize ? (
                    <div
                        className="bg-primary/10"
                        style={{
                            width: activeSize.width,
                            height: activeSize.height,
                            borderRadius: 8,
                            background: "",
                            opacity: .6,
                            boxShadow:
                                "0 4px 14px rgba(0,0,0,0.12), 0 2px 6px rgba(0,0,0,0.08)",
                        }}
                    />
                ) : null}
            </DragOverlay>
        </DndContext>
        </AllowedTypesContext.Provider>
    );
};

export default FormBuilder;
