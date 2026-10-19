import { type LucideIcon, Hash, ListChecks, CalendarClock, Paperclip, LayoutGrid, Sparkles, TextCursorInput } from "lucide-react";
import type { MessageKey } from "../../i18n";

/**
 * Catégories de types de champs. Notion du builder (présentation du menu d'ajout et restriction des champs
 * proposés) : elle n'est jamais écrite dans la structure JSON d'un formulaire.
 */
export const BLOCK_CATEGORIES = ["text", "choice", "number", "datetime", "file", "advanced", "layout"] as const;

export type BlockCategory = (typeof BLOCK_CATEGORIES)[number];

type CategoryDefinition = {
    id: BlockCategory;
    titleKey: MessageKey;
    icon: LucideIcon;
};

/** Ordre d'affichage fixe dans le menu d'ajout. */
export const categoryDefinitions: CategoryDefinition[] = [
    {id: "text", titleKey: "category.text", icon: TextCursorInput},
    {id: "choice", titleKey: "category.choice", icon: ListChecks},
    {id: "number", titleKey: "category.number", icon: Hash},
    {id: "datetime", titleKey: "category.datetime", icon: CalendarClock},
    {id: "file", titleKey: "category.file", icon: Paperclip},
    {id: "advanced", titleKey: "category.advanced", icon: Sparkles},
    {id: "layout", titleKey: "category.layout", icon: LayoutGrid},
];

export const isBlockCategory = (value: string): value is BlockCategory =>
    (BLOCK_CATEGORIES as readonly string[]).includes(value);
