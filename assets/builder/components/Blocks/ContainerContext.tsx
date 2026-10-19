import { createContext } from "react";

/**
 * Vrai à l'intérieur d'un groupe ou d'un répétable : un conteneur imbriqué n'ajoute pas d'espace sous lui
 * (c'est le `gap` de son parent qui espace).
 */
export const ContainerContext = createContext(false);

/**
 * Classes du cadre d'un groupe / répétable en édition (mêmes tokens que le formulaire de réponse).
 * Fichier .tsx : le `@source` Tailwind de l'application ne scanne que les .tsx.
 */
export const containerFrameClasses = (nested: boolean, hostsToolbars = true): string =>
    // Les blocs enfants affichent leur barre d'outils à leur gauche (`right-full`) : sans gouttière à l'intérieur
    // du cadre, elle déborderait sur la bordure. Même gouttière (pl-28) que la racine du builder.
    `flex flex-col gap-5 rounded-lg border border-border transition-colors ${hostsToolbars ? "py-4 pr-4 pl-28 md:py-6 md:pr-6" : "p-4 md:p-6"} ${nested ? "" : "mb-6"}`;

/** Carte d'un élément répété : ses blocs enfants portent leurs barres d'outils, d'où la gouttière. */
export const repeatableCardClasses = "rounded-lg border border-border py-4 pr-4 pl-28";
