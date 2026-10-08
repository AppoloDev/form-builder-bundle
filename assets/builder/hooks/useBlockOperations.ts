import { useCallback } from "react";
import { useFormBuilderStore } from "../stores/block.store";
import { Block, BlockId } from "../components/Blocks/Definition";

export const useBlockOperations = (blockId: BlockId) => {
    const { updateBlock, removeBlock } = useFormBuilderStore();

    const handleUpdate = useCallback(
        <T extends Block>(updates: Partial<Omit<T, "id" | "type">>) => {
            updateBlock(blockId, updates);
        },
        [blockId, updateBlock]
    );

    const handleRemove = useCallback(() => {
        removeBlock(blockId);
    }, [blockId, removeBlock]);

    return {
        handleUpdate,
        handleRemove,
    };
};
