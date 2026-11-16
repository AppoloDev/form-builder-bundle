import { ReactNode, useEffect, useRef, useState } from "react";
import { v4 as uuidv4 } from "uuid";
import { createPortal } from "react-dom";
import { closestCenter, DndContext, DragEndEvent, DragMoveEvent, DragOverlay, DragStartEvent, KeyboardSensor, PointerSensor, useSensor, useSensors } from "@dnd-kit/core";
import { SortableContext, sortableKeyboardCoordinates, useSortable } from "@dnd-kit/sortable";
import { CSS } from "@dnd-kit/utilities";
import { DotsSixVerticalIcon, TrashIcon } from "@phosphor-icons/react";
import { Button } from "../ui/button";
import { Input } from "../ui/input";
import { t } from "../../i18n";

type Option = { id: string; label: string };

type Props<O extends Option> = {
    options: O[];
    onChange: (next: O[]) => void;
    /** Construit une nouvelle option vide (les types d'option diffèrent entre ChoiceGroup et Select). */
    createOption: (id: string) => O;
};

/**
 * Édition en place des options d'un bloc de choix : une ligne par option (réordonnable), et un champ d'ajout.
 * DndContext propre, imbriqué dans celui du FormBuilder : le tri des options reste isolé du tri des blocs.
 * Même rendu que le tri des blocs : rien ne bouge, une copie transparente suit le pointeur et une barre bleue
 * indique où l'option sera déposée.
 */
export const OptionList = <O extends Option>({ options, onChange, createOption }: Props<O>) => {
    const [pendingFocusId, setPendingFocusId] = useState<string | null>(null);
    // Recherches DOM limitées à cette liste : les ids d'option ne sont uniques qu'au sein d'un bloc.
    const listRef = useRef<HTMLDivElement>(null);
    const sensors = useSensors(
        useSensor(PointerSensor, {activationConstraint: {distance: 4}}),
        useSensor(KeyboardSensor, {coordinateGetter: sortableKeyboardCoordinates}),
    );

    useEffect(() => {
        if (!pendingFocusId) return;
        const input = listRef.current?.querySelector<HTMLInputElement>(`[data-option-id="${pendingFocusId}"]`);
        input?.focus();
        input?.setSelectionRange(input.value.length, input.value.length);
        setPendingFocusId(null);
    }, [pendingFocusId, options]);

    const withLabel = (option: O, label: string): O => ({...option, label, ...('value' in option ? {value: label} : {})});

    /** Saisie dans le champ « Ajouter une option » : crée l'option et y déplace la saisie, le champ reste vide. */
    const add = (label: string) => {
        if (label === "") return;
        const option = withLabel(createOption(uuidv4()), label);
        onChange([...options, option]);
        setPendingFocusId(option.id);
    };

    const [bar, setBar] = useState<{ top: number; left: number; width: number } | null>(null);
    const [preview, setPreview] = useState<HTMLElement | null>(null);
    const rowOf = (id: unknown) => listRef.current?.querySelector<HTMLElement>(`[data-option-row="${String(id)}"]`);

    /** Option survolée, et si l'on dépose après elle (centre de la copie sous le centre de l'option). */
    const targetOf = ({active, over}: DragMoveEvent | DragEndEvent) => {
        const dragged = active.rect.current.translated;
        if (!over || over.id === active.id || !dragged) return null;
        return {overId: over.id, after: dragged.top + dragged.height / 2 > over.rect.top + over.rect.height / 2};
    };

    const onDragStart = ({active}: DragStartEvent) => {
        const el = rowOf(active.id);
        if (!el) return;
        const clone = el.cloneNode(true) as HTMLElement;
        clone.removeAttribute("data-option-row");
        clone.removeAttribute("style");
        clone.style.width = `${el.getBoundingClientRect().width}px`;
        setPreview(clone);
    };

    const onDragMove = (event: DragMoveEvent) => {
        const target = targetOf(event);
        const rect = target ? rowOf(target.overId)?.getBoundingClientRect() : null;
        if (!target || !rect) return setBar(null);
        // Au milieu de l'écart entre deux lignes (space-y-1 = 4px).
        setBar({top: target.after ? rect.bottom + 2 : rect.top - 2, left: rect.left, width: rect.width});
    };

    const onDragEnd = (event: DragEndEvent) => {
        const target = targetOf(event);
        if (target) {
            const moved = options.find((o) => o.id === event.active.id);
            const rest = options.filter((o) => o.id !== event.active.id);
            const at = rest.findIndex((o) => o.id === target.overId) + (target.after ? 1 : 0);
            if (moved) onChange([...rest.slice(0, at), moved, ...rest.slice(at)]);
        }
        onDragCancel();
    };

    const onDragCancel = () => {
        setPreview(null);
        setBar(null);
    };

    return (
        <div ref={listRef}>
            <DndContext sensors={sensors} collisionDetection={closestCenter} onDragStart={onDragStart} onDragMove={onDragMove}
                        onDragEnd={onDragEnd} onDragCancel={onDragCancel}>
                <SortableContext items={options.map((o) => o.id)} strategy={() => null}>
                    <div className="space-y-1">
                        {options.map((opt, idx) => (
                            <OptionRow key={opt.id} id={opt.id} onRemove={() => onChange(options.filter((_, i) => i !== idx))}>
                                <Input
                                    data-option-id={opt.id}
                                    value={opt.label}
                                    placeholder={t("option.default", {n: idx + 1})}
                                    onChange={(e) => onChange(options.map((o, i) => (i === idx ? withLabel(o, e.target.value) : o)))}
                                    className="border-transparent bg-transparent hover:border-input focus-visible:border-ring"
                                />
                            </OptionRow>
                        ))}

                        {/* Faux bouton : aligné sur les options (emplacements invisibles de la poignée et de la poubelle). */}
                        <div className="flex items-center gap-2">
                            <DotsSixVerticalIcon aria-hidden="true" className="invisible"/>
                            <span aria-hidden="true" className="size-8 shrink-0"/>
                            <Input
                                value=""
                                placeholder={t("option.addLong")}
                                onChange={(e) => add(e.target.value)}
                                className="border-transparent bg-transparent hover:border-input focus-visible:border-ring"
                            />
                        </div>
                    </div>
                </SortableContext>

                {/* Mêmes calques que le tri des blocs : copie (40) < barre d'insertion (45) < infobulles (z-50). */}
                <DragOverlay dropAnimation={null} zIndex={40}>
                    {preview ? <div className="pointer-events-none opacity-60" ref={(node) => node?.replaceChildren(preview)}/> : null}
                </DragOverlay>
                {bar && createPortal(
                    <div aria-hidden="true" className="fixed z-[45] h-1 -translate-y-1/2 rounded-full bg-blue-500 pointer-events-none" style={bar}/>,
                    document.body
                )}
            </DndContext>
        </div>
    );
};

const OptionRow = ({id, onRemove, children}: { id: string; onRemove: () => void; children: ReactNode }) => {
    const {attributes, listeners, setNodeRef, setActivatorNodeRef, transform, transition, isDragging} = useSortable({id});
    const reveal = "opacity-0 transition-opacity group-hover/option:opacity-100 focus-visible:opacity-100";

    return (
        <div
            ref={setNodeRef}
            data-option-row={id}
            style={{transform: CSS.Translate.toString(transform), transition, opacity: isDragging ? 0.6 : 1}}
            className="group/option relative flex items-center gap-2 rounded-md"
        >
            <button
                type="button"
                ref={setActivatorNodeRef}
                {...attributes}
                {...listeners}
                className={`flex cursor-grab touch-none items-center text-muted-foreground active:cursor-grabbing ${reveal}`}
                aria-label={t("option.reorderAria")}
            >
                <DotsSixVerticalIcon/>
            </button>

            <Button
                type="button"
                variant="ghost"
                size="icon-sm"
                onClick={onRemove}
                className={`text-destructive hover:text-destructive ${reveal}`}
                aria-label={t("option.deleteAria")}
            >
                <TrashIcon/>
            </Button>

            {children}
        </div>
    );
};
