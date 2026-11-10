import { Block, BlockDefinition, BlockId } from "../components/Blocks/Definition";
import { v4 as uuidv4 } from 'uuid';

export const generateBlockId = (type: string): BlockId => {
    return `${type}-${uuidv4()}`;
};

// Bloc tout juste ajouté depuis le builder : son libellé (ou son texte) prend le focus au montage.
let pendingFocusId: BlockId | null = null;

/** Vrai une seule fois, pour le dernier bloc créé par createBlockFromTemplate. */
export const takePendingFocus = (id: BlockId): boolean => {
    if (id !== pendingFocusId) return false;
    pendingFocusId = null;
    return true;
};

export const createBlockFromTemplate = (definition: BlockDefinition): Block => {
    const id = generateBlockId(definition.type);
    pendingFocusId = id;
    return {...definition.defaultProps, id} as Block;
};
