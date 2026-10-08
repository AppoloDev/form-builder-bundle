import React, { useCallback, useMemo, useState } from "react";
import {
    DndContext,
    DragStartEvent,
    DragEndEvent,
    DragOverlay,
    PointerSensor,
    UniqueIdentifier,
    useSensor,
    useSensors,
    closestCenter
} from "@dnd-kit/core";
import {
    SortableContext,
    useSortable,
    verticalListSortingStrategy,
    arrayMove
} from "@dnd-kit/sortable";
import { CSS } from "@dnd-kit/utilities";
import { Block } from "./Definition";
import { BLOCK_COMPONENTS } from "../BlockRegistry";
import { DragHandleContext } from "../../FormBuilder";

const FollowUpRenderer: React.FC<{ child: Block }> = ({child}) => {
    const Comp = BLOCK_COMPONENTS[child.type];
    if (!Comp) return null;
    return <Comp {...child} useContionnalField={false} isChildBlock={true} />;
};

const SortableChildBlock: React.FC<{
    id: UniqueIdentifier;
    children: React.ReactNode;
}> = ({id, children}) => {
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
            <div ref={setNodeRef} style={style} data-child-id={String(id)}>
                {children}
            </div>
        </DragHandleContext.Provider>
    );
};

export const ChildrenSorter: React.FC<{
    childrenBlocks: Block[];
    onReorder: (next: Block[]) => void;
}> = ({childrenBlocks, onReorder}) => {
    const sensors = useSensors(
        useSensor(PointerSensor, {activationConstraint: {distance: 2}})
    );

    const childIds = useMemo(() => childrenBlocks.map((c) => c.id), [childrenBlocks]);

    const [activeChildId, setActiveChildId] = useState<UniqueIdentifier | null>(null);
    const [activeSize, setActiveSize] = useState<{ width: number; height: number } | null>(null);

    const onDragStart = useCallback((e: DragStartEvent) => {
        setActiveChildId(e.active.id);
        const el = document.querySelector<HTMLElement>(`[data-child-id="${String(e.active.id)}"]`);
        if (el) {
            const r = el.getBoundingClientRect();
            setActiveSize({width: r.width, height: r.height});
        } else {
            setActiveSize(null);
        }
    }, []);

    const onDragEnd = useCallback((e: DragEndEvent) => {
        const {active, over} = e;
        if (over && active.id !== over.id) {
            const oldIndex = childrenBlocks.findIndex((b) => b.id === active.id);
            const newIndex = childrenBlocks.findIndex((b) => b.id === over.id);
            if (oldIndex !== -1 && newIndex !== -1) {
                onReorder(arrayMove(childrenBlocks, oldIndex, newIndex));
            }
        }
        setActiveChildId(null);
        setActiveSize(null);
    }, [childrenBlocks, onReorder]);

    const onDragCancel = useCallback(() => {
        setActiveChildId(null);
        setActiveSize(null);
    }, []);

    return (
        <DndContext
            sensors={sensors}
            collisionDetection={closestCenter}
            onDragStart={onDragStart}
            onDragEnd={onDragEnd}
            onDragCancel={onDragCancel}
        >
            <SortableContext items={childIds} strategy={verticalListSortingStrategy}>
                <div className="flex flex-col gap-3">
                    {childrenBlocks.map((child) => (
                        <SortableChildBlock key={child.id} id={child.id}>
                            <FollowUpRenderer child={child}/>
                        </SortableChildBlock>
                    ))}
                </div>
            </SortableContext>

            <DragOverlay dropAnimation={null}>
                {activeChildId && activeSize ? (
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
