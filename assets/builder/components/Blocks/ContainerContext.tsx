import { createContext } from "react";
import type { BlockType } from "./Definition";

/** Groupe et répétable : conteneurs qui ne s'imbriquent pas (ni l'un dans l'autre, ni en eux-mêmes). */
export const isContainerType = (type: BlockType): boolean => type === "FieldSet" || type === "Repeatable";

/**
 * Vrai partout sous un groupe ou un répétable (y compris dans les règles de condition de ses champs) : le menu
 * d'ajout n'y propose plus de conteneur. Distinct de ContainerContext, que le répétable remet à faux pour l'espacement.
 */
export const InsideContainerContext = createContext(false);

/**
 * Vrai à l'intérieur d'un groupe ou d'un répétable : un conteneur imbriqué n'ajoute pas d'espace sous lui
 * (c'est le `gap` de son parent qui espace).
 */
export const ContainerContext = createContext(false);

/**
 * Classes du cadre d'un groupe / répétable en édition (mêmes tokens que le formulaire de réponse).
 * Fichier .tsx : le `@source` Tailwind de l'application ne scanne que les .tsx.
 */
export const containerFrameClasses = (nested: boolean): string =>
    // Les blocs enfants affichent leur barre d'outils à leur gauche (`right-full`) : sans gouttière à l'intérieur
    // du cadre, elle déborderait sur la bordure. Même gouttière (pl-28) que la racine du builder.
    // gap-8 : même espacement entre le dernier bloc et « Ajouter un élément » qu'entre deux blocs (ChildrenSorter).
    `flex flex-col gap-8 rounded-lg border border-border transition-colors py-10 pr-4 pl-28 md:pr-6 ${nested ? "" : "mb-6"}`;
