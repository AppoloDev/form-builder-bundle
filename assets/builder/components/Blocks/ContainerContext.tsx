import { createContext } from "react";

/**
 * Vrai à l'intérieur d'un groupe ou d'un répétable : un conteneur imbriqué prend alors un fond `background`
 * au lieu du fond teinté, pour ne pas empiler les teintes.
 */
export const ContainerContext = createContext(false);

/** Classes du cadre (fichier .tsx : le @source Tailwind de l'application ne scanne que les .tsx)  d'un groupe / répétable en édition (mêmes tokens que le formulaire de réponse). */
export const containerFrameClasses = (nested: boolean): string =>
    `flex flex-col gap-5 rounded-lg border border-border p-4 md:p-6 transition-colors ${nested ? "bg-background" : "mb-6 bg-muted/30"}`;
