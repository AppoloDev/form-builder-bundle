/**
 * Typographie des blocs Titre et Paragraphe : source unique, alignée sur le formulaire de réponse de l'application
 * (WYSIWYG). Utilisée par les composants `Title` et `Paragraph` en édition comme en aperçu.
 *
 * Fichier .tsx : le `@source` Tailwind de l'application ne scanne que les .tsx, et les classes doivent apparaître
 * en entier (pas de `md:${…}` construit à la volée).
 */
export type HeadingLevel = "h1" | "h2" | "h3" | "h4" | "h5" | "h6";

export const HEADING_LEVELS: readonly HeadingLevel[] = ["h1", "h2", "h3", "h4", "h5", "h6"];

/** Classes d'un titre dans le formulaire de réponse. */
export const TITLE_CLASSES: Record<HeadingLevel, string> = {
    h1: "text-3xl font-bold text-foreground",
    h2: "text-2xl font-bold text-foreground",
    h3: "text-xl font-bold text-foreground",
    h4: "text-lg font-bold text-foreground",
    h5: "text-base font-bold text-foreground",
    h6: "text-sm font-bold text-foreground",
};

/**
 * Mêmes classes pour le champ de saisie du titre en édition : la taille est répétée sous `md:` pour passer devant
 * le `md:text-sm` du composant `Input` (la taille ne doit pas dépendre de la largeur d'écran).
 */
export const TITLE_INPUT_CLASSES: Record<HeadingLevel, string> = {
    h1: "text-3xl md:text-3xl font-bold text-foreground",
    h2: "text-2xl md:text-2xl font-bold text-foreground",
    h3: "text-xl md:text-xl font-bold text-foreground",
    h4: "text-lg md:text-lg font-bold text-foreground",
    h5: "text-base md:text-base font-bold text-foreground",
    h6: "text-sm md:text-sm font-bold text-foreground",
};

/** Classes d'un paragraphe dans le formulaire de réponse. */
export const PARAGRAPH_CLASSES = "text-base text-muted-foreground";

/** Idem pour la zone de saisie du paragraphe en édition (devant le `md:text-sm` du `Textarea`). */
export const PARAGRAPH_TEXTAREA_CLASSES = "text-base md:text-base text-muted-foreground";

/** Libellé d'un champ, comme `Field:Label` du kit shadcn de l'application (blocs et panneau d'options). */
export const FIELD_LABEL_CLASSES = "text-sm leading-snug font-medium text-foreground";

/**
 * Texte modifiable en place (libellé, titre, paragraphe) : texte nu, sans bordure, fond, marge ni anneau de focus de l'`Input` (seul le curseur
 * de saisie signale l'édition). Taille répétée sous `md:` pour passer devant le `md:text-sm` de l'`Input`.
 */
export const INLINE_BARE_CLASSES = "h-auto min-h-0 mx-0 rounded-none border-0 p-0 shadow-none bg-transparent focus-visible:ring-0";

export const FIELD_LABEL_INPUT_CLASSES = `${INLINE_BARE_CLASSES} text-sm md:text-sm placeholder:text-muted-foreground/70`;

/** Texte d'aide d'un champ, comme `Field:Description`. */
export const FIELD_DESCRIPTION_CLASSES = "text-sm leading-normal font-normal text-muted-foreground";

/** Ligne libellé + contrôle + aide, comme `Field` (orientation verticale). */
export const FIELD_CLASSES = "flex flex-col gap-2";

export const isHeadingLevel = (value: string): value is HeadingLevel => (HEADING_LEVELS as readonly string[]).includes(value);

/** Niveau de titre valide, h1 par défaut (comme les valeurs par défaut du builder). */
export const headingLevelOf = (value: string | undefined): HeadingLevel => (value && isHeadingLevel(value) ? value : "h1");
