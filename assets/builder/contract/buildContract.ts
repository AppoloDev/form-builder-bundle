import { blockDefinitions } from "../components/Blocks/Definition";

export type BlockContract = {
    /** Valeurs initiales d'un bloc fraîchement ajouté (sans l'id). */
    defaults: Record<string, unknown>;
    /** Clés que le builder peut émettre pour ce type (hors `id` et `type`). */
    keys: string[];
};

export type Contract = {
    types: Record<string, BlockContract>;
};

/**
 * Décrit ce que le builder émet, type par type. Ce contrat est versionné
 * (`blocks.json`) et vérifié côté PHP : voir tests/Unit/Contract/FrontContractTest.php.
 */
export const buildContract = (): Contract => {
    const types: Record<string, BlockContract> = {};

    for (const definition of Object.values(blockDefinitions).sort((a, b) => a.type.localeCompare(b.type))) {
        const keys = new Set<string>([
            ...Object.keys(definition.defaultProps),
            ...(definition.editionSchema ?? []).map((item) => item.key),
        ]);
        keys.delete("id");
        keys.delete("type");

        types[definition.type] = {
            defaults: JSON.parse(JSON.stringify(definition.defaultProps)) as Record<string, unknown>,
            keys: [...keys].sort(),
        };
    }

    return { types };
};

export const serializeContract = (contract: Contract): string => `${JSON.stringify(contract, null, 2)}\n`;
