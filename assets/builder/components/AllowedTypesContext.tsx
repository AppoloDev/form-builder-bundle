import { createContext, useContext, useMemo } from "react";
import { BlockDefinition, BlockType, getAllBlockDefinitions } from "./Blocks/Definition";

/**
 * Types que le menu d'ajout propose (`undefined` = tous). Fourni une fois à la racine du builder : tous les
 * `AddMenu` (racine, groupe, répétable, bloc vide) le lisent, sans que la prop soit propagée à la main.
 */
export const AllowedTypesContext = createContext<readonly BlockType[] | undefined>(undefined);

/** Définitions que l'on peut ajouter ; la prop explicite `allowTypes` l'emporte sur le contexte. */
export const useAddableDefinitions = (allowTypes?: readonly BlockType[]): BlockDefinition[] => {
    const fromContext = useContext(AllowedTypesContext);
    const allowed = allowTypes ?? fromContext;

    return useMemo(() => {
        const all = getAllBlockDefinitions();
        if (!allowed) {
            return all;
        }
        const allow = new Set(allowed);

        return all.filter((definition) => allow.has(definition.type));
    }, [allowed]);
};
