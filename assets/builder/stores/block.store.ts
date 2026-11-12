import { create } from "zustand";
import { arrayMove } from "@dnd-kit/sortable";
import { Block, BlockId, ConditionRule, OptionItem, SelectOption } from "../components/Blocks/Definition";
import { UniqueIdentifier } from "@dnd-kit/core";
import { v4 as uuidv4 } from "uuid";

interface FormBuilderState {
    blocks: Block[];
    overId: string | null;
    activeId: UniqueIdentifier | null;

    setBlocks: (blocks: Block[]) => void;
    addBlock: (block: Block, index?: number) => void;
    removeBlock: (id: BlockId) => void;
    updateBlock: <T extends Block>(id: BlockId, updates: Partial<Omit<T, 'id' | 'type'>>) => void;
    /** Remplace un bloc entier, à n'importe quelle profondeur (changement de type : « Transformer en »). */
    replaceBlock: (id: BlockId, block: Block) => void;

    setOverId: (id: string | null) => void;
    setActiveId: (id: UniqueIdentifier | null) => void;
    /** Déplace un bloc n'importe où dans l'arbre (cf. `moveBlockInTree`). */
    moveBlock: (activeId: BlockId, target: MoveTarget) => void;
}

/** Liste de blocs : la racine (`null`), un groupe / répétable (id du bloc) ou une règle de condition (id de la règle). */
export type ContainerId = BlockId | null;

/** Cible d'un déplacement : à côté d'un bloc (avant, ou après si `after`), ou à la fin d'une liste. */
export type MoveTarget = { overId: BlockId; after?: boolean } | { containerId: ContainerId };

const recurseIntoChildren = (block: Block, recurse: (children: Block[]) => Block[]): Block => {
    if (block.type === 'FieldSet' || block.type === 'Repeatable') {
        if (block.children.length === 0) return block;
        return { ...block, children: recurse(block.children) };
    }

    if (block.type === 'ChoiceGroup' || block.type === 'Select') {
        const conditions = block.conditions ?? [];
        if (conditions.length === 0) return block;
        const nextConditions = conditions.map((rule) =>
            rule.children.length === 0 ? rule : { ...rule, children: recurse(rule.children) }
        );
        return { ...block, conditions: nextConditions };
    }

    return block;
};

/** Listes d'enfants directes d'un bloc, avec l'identifiant de leur conteneur. */
const childLists = (block: Block): [BlockId, Block[]][] => {
    if (block.type === 'FieldSet' || block.type === 'Repeatable') return [[block.id, block.children]];
    if (block.type === 'ChoiceGroup' || block.type === 'Select') {
        return (block.conditions ?? []).map((rule) => [rule.id, rule.children]);
    }
    return [];
};

/** Conteneur de la liste qui contient le bloc `id` (`undefined` : bloc introuvable). */
export const findContainer = (blocks: Block[], id: BlockId, containerId: ContainerId = null): ContainerId | undefined => {
    for (const block of blocks) {
        if (block.id === id) return containerId;
        for (const [childContainer, children] of childLists(block)) {
            const found = findContainer(children, id, childContainer);
            if (found !== undefined) return found;
        }
    }
    return undefined;
};

const findBlock = (blocks: Block[], id: BlockId): Block | undefined => {
    for (const block of blocks) {
        if (block.id === id) return block;
        for (const [, children] of childLists(block)) {
            const found = findBlock(children, id);
            if (found) return found;
        }
    }
    return undefined;
};

/** Identifiants du bloc et de tous les conteneurs qu'il renferme (on ne dépose pas un bloc en lui-même). */
const containerIdsWithin = (block: Block, ids = new Set<BlockId>([block.id])): Set<BlockId> => {
    for (const [containerId, children] of childLists(block)) {
        ids.add(containerId);
        children.forEach((child) => containerIdsWithin(child, ids));
    }
    return ids;
};

/** Applique `fn` à la liste du conteneur `containerId`, à n'importe quelle profondeur. */
const mapContainer = (blocks: Block[], containerId: ContainerId, fn: (list: Block[]) => Block[]): Block[] => {
    if (containerId === null) return fn(blocks);
    return blocks.map((block) => {
        if ((block.type === 'FieldSet' || block.type === 'Repeatable') && block.id === containerId) {
            return { ...block, children: fn(block.children) };
        }
        if ((block.type === 'ChoiceGroup' || block.type === 'Select') && block.conditions?.some((r) => r.id === containerId)) {
            return {
                ...block,
                conditions: block.conditions.map((r) => r.id === containerId ? { ...r, children: fn(r.children) } : r),
            };
        }
        return recurseIntoChildren(block, (children) => mapContainer(children, containerId, fn));
    });
};

/** Déplace un bloc dans sa liste ou vers une autre (racine, groupe, répétable, règle de condition). */
export const moveBlockInTree = (blocks: Block[], activeId: BlockId, target: MoveTarget): Block[] => {
    const active = findBlock(blocks, activeId);
    const from = findContainer(blocks, activeId);
    const to = 'overId' in target ? findContainer(blocks, target.overId) : target.containerId;
    if (!active || from === undefined || to === undefined) return blocks;
    if (to !== null && containerIdsWithin(active).has(to)) return blocks;

    if ('overId' in target && from === to) {
        return mapContainer(blocks, to, (list) => arrayMove(
            list,
            list.findIndex((b) => b.id === activeId),
            list.findIndex((b) => b.id === target.overId),
        ));
    }

    return mapContainer(removeBlockRecursive(blocks, activeId), to, (list) => {
        const index = 'overId' in target
            ? list.findIndex((b) => b.id === target.overId) + (target.after ? 1 : 0)
            : list.length;
        return [...list.slice(0, index), active, ...list.slice(index)];
    });
};

const migrateBlock = (block: Block): Block => {
    if (block.type === 'ChoiceGroup') {
        const conditions: ConditionRule[] = [...(block.conditions ?? [])];
        const legacyOptions = block.options as Array<OptionItem & { showConditionalField?: boolean; children?: Block[] }>;

        const options: OptionItem[] = legacyOptions.map((option) => {
            const { showConditionalField: _showConditionalField, children, ...rest } = option;
            if (children && children.length > 0) {
                conditions.push({ id: uuidv4(), operator: 'is', optionId: option.id, children });
            }
            return rest;
        });

        return {
            ...block,
            options,
            conditions: conditions.map((rule) => ({
                ...rule,
                operator: rule.operator ?? 'is',
                children: rule.children.map(migrateBlock),
            })),
        };
    }

    if (block.type === 'Select') {
        const legacyOptions = block.options as unknown as Array<string | SelectOption>;

        const options: SelectOption[] = legacyOptions.map((option) =>
            typeof option === 'string' ? { id: uuidv4(), label: option } : option
        );

        return {
            ...block,
            options,
            conditions: (block.conditions ?? []).map((rule) => ({
                ...rule,
                operator: rule.operator ?? 'is',
                children: rule.children.map(migrateBlock),
            })),
        };
    }

    if (block.type === 'FieldSet' || block.type === 'Repeatable') {
        return { ...block, children: (block.children ?? []).map(migrateBlock) };
    }

    return block;
};

const migrateBlocks = (blocks: Block[]): Block[] => blocks.map(migrateBlock);

const updateBlockRecursive = (blocks: Block[], id: BlockId, updates: any): Block[] => {
    return blocks.map((block) => {
        if (block.id === id) {
            return { ...block, ...updates };
        }

        return recurseIntoChildren(block, (children) => updateBlockRecursive(children, id, updates));
    });
};

const replaceBlockRecursive = (blocks: Block[], id: BlockId, next: Block): Block[] =>
    blocks.map((block) => block.id === id
        ? next
        : recurseIntoChildren(block, (children) => replaceBlockRecursive(children, id, next)));

const removeBlockRecursive = (blocks: Block[], id: BlockId): Block[] => {
    const filteredBlocks = blocks.filter((block) => block.id !== id);

    return filteredBlocks.map((block) =>
        recurseIntoChildren(block, (children) => removeBlockRecursive(children, id))
    );
};
export const useFormBuilderStore = create<FormBuilderState>((set) => ({
    blocks: [],
    overId: null,
    activeId: null,

    setBlocks: (blocks) => {
        set({ blocks: migrateBlocks(blocks) });
    },

    addBlock: (block, index) => {
        set((state) => {
            const newBlocks = [...state.blocks];

            if (typeof index === "number") {
                newBlocks.splice(index, 0, block);
            } else {
                newBlocks.push(block);
            }

            return { blocks: newBlocks };
        });
    },

    removeBlock: (id) => {
        set((state) => ({
            blocks: removeBlockRecursive(state.blocks, id)
        }));
    },

    updateBlock: (id, updates) => {
        set((state) => ({
            blocks: updateBlockRecursive(state.blocks, id, updates)
        }));
    },

    replaceBlock: (id, block) => {
        set((state) => ({
            blocks: replaceBlockRecursive(state.blocks, id, block)
        }));
    },

    setOverId: (id) => {
        set({ overId: id });
    },

    setActiveId: (id) => {
        set({ activeId: id });
    },

    moveBlock: (activeId, target) => {
        set((state) => ({ blocks: moveBlockInTree(state.blocks, activeId, target) }));
    },
}));
