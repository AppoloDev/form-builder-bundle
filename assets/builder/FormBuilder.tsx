import React, { useEffect, useMemo, useState, createContext } from "react";
import { Block, BlockDefinition } from "./components/Blocks/Definition";
import { useFormBuilderStore } from "./stores/block.store";
import { createBlockFromTemplate } from "./utilities/block.utiles";
import { BLOCK_COMPONENTS } from "./components/BlockRegistry";
import { Empty } from "./components/Empty";

import {
    DndContext,
    DragStartEvent,
    DragEndEvent,
    DragOverlay,
    PointerSensor,
    useSensor,
    useSensors,
    closestCenter,
    UniqueIdentifier,
} from "@dnd-kit/core";

import {
    SortableContext,
    useSortable,
    verticalListSortingStrategy,
    arrayMove,
} from "@dnd-kit/sortable";

import { CSS } from "@dnd-kit/utilities";
import { AddMenu } from "./components/AddMenu";

type Props = {
    onChange: (blocks: Block[]) => void;
    json: Block[];
};

export const DragHandleContext = createContext<{
    attributes?: Record<string, any>;
    listeners?: Record<string, any>;
    setActivatorNodeRef?: (node: HTMLElement | null) => void;
    isDragging?: boolean;
}>({});

const SortableItem: React.FC<{ id: UniqueIdentifier; children: React.ReactNode }> = (
    {
        id,
        children,
    }) => {
    const {
        attributes,
        listeners,
        setNodeRef,
        setActivatorNodeRef,
        transform,
        transition,
        isDragging,
    } = useSortable({id});

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
            >
                {children}
            </div>
        </DragHandleContext.Provider>
    );
};

export const FormBuilder = ({onChange, json = []}: Props) => {
    const {blocks, addBlock, setBlocks} = useFormBuilderStore();
    const [activeId, setActiveId] = useState<UniqueIdentifier | null>(null);
    const [activeSize, setActiveSize] = useState<{ width: number; height: number } | null>(null);

    const sensors = useSensors(
        useSensor(PointerSensor, {
            activationConstraint: {
                distance: 2,
            },
        })
    );

    const handleAddAt = (afterIndex: number, def: BlockDefinition, overrides?: Record<string, any>) => {
        const newBlock = createBlockFromTemplate(def, overrides);
        addBlock(newBlock, afterIndex + 1);
    };

    useEffect(() => {
        setBlocks(json);
    }, []);

    useEffect(() => {
        onChange(blocks);
    }, [blocks]);

    const ids = useMemo(() => blocks.map((b) => b.id), [blocks]);

    const onDragStart = (event: DragStartEvent) => {
        setActiveId(event.active.id);

        const el = document.querySelector<HTMLElement>(`[data-block-id="${String(event.active.id)}"]`);
        if (el) {
            const rect = el.getBoundingClientRect();
            setActiveSize({width: rect.width, height: rect.height});
        } else {
            setActiveSize(null);
        }
    };

    const onDragEnd = (event: DragEndEvent) => {
        const {active, over} = event;

        if (over && active.id !== over.id) {
            const oldIndex = blocks.findIndex((b) => b.id === active.id);
            const newIndex = blocks.findIndex((b) => b.id === over.id);

            if (oldIndex !== -1 && newIndex !== -1) {
                const reordered = arrayMove(blocks, oldIndex, newIndex);
                setBlocks(reordered);
            }
        }

        setActiveId(null);
        setActiveSize(null);
    };

    const onDragCancel = () => {
        setActiveId(null);
        setActiveSize(null);
    };

    return (
        <DndContext
            sensors={sensors}
            collisionDetection={closestCenter}
            onDragStart={onDragStart}
            onDragEnd={onDragEnd}
            onDragCancel={onDragCancel}
        >
            <div className="border border-dashed border-border rounded-lg py-4 pr-4 pl-28 space-y-4">
                {blocks.length === 0 ? (
                    <Empty onPick={(def, overrides) => handleAddAt(-1, def, overrides)}/>
                ) : (
                    <SortableContext items={ids} strategy={verticalListSortingStrategy}>
                        {blocks.map((block, idx) => {
                            const Component = BLOCK_COMPONENTS[block.type];
                            if (!Component) {
                                console.error(`Composant non trouvé pour le type: ${block.type}`);
                                return null;
                            }
                            return (
                                <SortableItem key={block.id} id={block.id}>
                                    <div className="group relative">

                                        <div
                                            className="absolute -right-3 -bottom-3 z-10">
                                            <AddMenu
                                                onPick={(def, overrides) => handleAddAt(idx, def, overrides)}
                                                placeholder="Rechercher un type…"
                                            >
                                                <button
                                                    type="button"
                                                    className="rounded-full border border-input bg-primary shadow-sm p-2 hover:bg-primary/80 cursor-pointer transition-colors"
                                                    title="Ajouter un bloc"
                                                >
                                                    <svg width="16" height="16" viewBox="0 0 24 24"
                                                         className="text-primary-foreground">
                                                        <path fill="currentColor"
                                                              d="M11 11V5h2v6h6v2h-6v6h-2v-6H5v-2z"/>
                                                    </svg>
                                                </button>
                                            </AddMenu>

                                        </div>

                                        <Component {...block} />
                                    </div>
                                </SortableItem>
                            )
                                ;
                        })}
                    </SortableContext>
                )}
            </div>

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
    );
};

export default FormBuilder;
