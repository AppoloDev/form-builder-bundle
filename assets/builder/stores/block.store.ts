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

    setOverId: (id: string | null) => void;
    setActiveId: (id: UniqueIdentifier | null) => void;
    moveBlock: (activeId: string, overId: string) => void;
    moveBlockToEnd: (activeId: string) => void;
}

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

// Upgrades one block from a legacy shape to the current one. Idempotent —
// a block already in the new shape passes through unchanged, so this is
// safe to run on every load regardless of the input's actual shape.
const migrateBlock = (block: Block): Block => {
    if (block.type === 'ChoiceGroup') {
        const conditions: ConditionRule[] = [...(block.conditions ?? [])];

        // Legacy JSON may still carry showConditionalField/children on an
        // option even though OptionItem's type no longer declares them —
        // this cast is the intentional escape hatch for reading that.
        const legacyOptions = block.options as Array<OptionItem & { showConditionalField?: boolean; children?: Block[] }>;

        const options: OptionItem[] = legacyOptions.map((option) => {
            // eslint's no-unused-vars would flag this destructure-to-discard
            // otherwise (ignoreRestSiblings isn't enabled repo-wide).
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
        // Legacy JSON may still have options as plain strings.
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

    return recurseIntoChildren(block, (children) => children.map(migrateBlock));
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

    setOverId: (id) => {
        set({ overId: id });
    },

    setActiveId: (id) => {
        set({ activeId: id });
    },

    moveBlock: (activeId, overId) => {
        set((state) => {
            const oldIndex = state.blocks.findIndex((b) => b.id === activeId);
            const newIndex = state.blocks.findIndex((b) => b.id === overId);

            if (oldIndex === -1 || newIndex === -1) return state;

            return {
                blocks: arrayMove(state.blocks, oldIndex, newIndex)
            };
        });
    },

    moveBlockToEnd: (activeId) => {
        set((state) => {
            const oldIndex = state.blocks.findIndex((b) => b.id === activeId);

            if (oldIndex === -1) return state;

            return {
                blocks: arrayMove(state.blocks, oldIndex, state.blocks.length - 1)
            };
        });
    },
}));
