import { blockDefinitions, type BlockType } from "../components/Blocks/Definition";
import { isBlockCategory } from "../components/Blocks/Categories";

const isBlockType = (value: string): value is BlockType => Object.prototype.hasOwnProperty.call(blockDefinitions, value);

/**
 * Types de blocs que le menu d'ajout propose, d'après les restrictions de `FormStructureType`.
 *
 * - `categories` et `types` à `null`/`undefined` : tout est proposé (`undefined` renvoyé) ;
 * - une seule renseignée : elle s'applique ;
 * - les deux : union (catégories ∪ types).
 *
 * La restriction ne concerne que les ajouts : les blocs déjà présents dans la structure restent affichés,
 * modifiables et supprimables (aucune fonction du store n'en tient compte).
 */
export const resolveAllowedTypes = (
    categories?: readonly string[] | null,
    types?: readonly string[] | null,
): BlockType[] | undefined => {
    if (!categories && !types) {
        return undefined;
    }

    const allowedCategories = new Set((categories ?? []).filter(isBlockCategory));
    const allowedTypes = new Set((types ?? []).filter(isBlockType));

    return Object.values(blockDefinitions)
        .filter((definition) => allowedCategories.has(definition.category) || allowedTypes.has(definition.type))
        .map((definition) => definition.type);
};

/** Liste séparée par des virgules (attribut HTML de `<form-builder>`) → tableau ; absent → `undefined`. */
export const parseList = (value: string | null | undefined): string[] | undefined =>
    value === undefined || value === null ? undefined : value.split(",").map((item) => item.trim()).filter(Boolean);
