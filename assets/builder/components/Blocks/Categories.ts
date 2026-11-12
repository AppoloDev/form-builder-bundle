import { type Icon, CheckSquareIcon } from "@phosphor-icons/react";
import type { MessageKey } from "../../i18n";
import type { BlockType } from "./Definition";
import { TEXT_VARIANTS } from "./TextVariants";

/**
 * Catégories de types de champs. Notion du builder (restriction des champs proposés, option `categories`) : elle
 * n'est jamais écrite dans la structure JSON d'un formulaire.
 */
export const BLOCK_CATEGORIES = ["text", "choice", "number", "datetime", "file", "special", "layout", "group"] as const;

export type BlockCategory = (typeof BLOCK_CATEGORIES)[number];

export const isBlockCategory = (value: string): value is BlockCategory =>
    (BLOCK_CATEGORIES as readonly string[]).includes(value);

/**
 * Entrée du menu d'ajout. Par défaut, titre et icône de la définition du bloc ; un même type peut figurer plusieurs
 * fois avec ses propres valeurs par défaut (choix unique / multiple).
 */
export type AddMenuEntry = {
    type: BlockType;
    titleKey?: MessageKey;
    icon?: Icon;
    defaultProps?: Record<string, unknown>;
};

/** Sections et ordre du menu d'ajout (présentation seulement, indépendante des catégories). */
export const addMenuSections: { titleKey: MessageKey; entries: AddMenuEntry[] }[] = [
    {
        titleKey: "menu.questions",
        entries: [
            {type: "TextInput"},
            {type: "TextareaInput"},
            {type: "ChoiceGroup", titleKey: "menu.singleChoice"},
            {type: "ChoiceGroup", titleKey: "menu.multipleChoice", icon: CheckSquareIcon, defaultProps: {multiple: true}},
            {type: "Select"},
            {type: "NumberInput"},
            {type: "DateTimeInput"},
            {type: "HourMinuteInput"},
            {type: "EmailInput"},
            {type: "TelInput"},
            {type: "UrlInput"},
            {type: "AddressInput"},
            {type: "FileInput"},
            {type: "Signature"},
        ],
    },
    {
        titleKey: "menu.presentation",
        entries: TEXT_VARIANTS.map((variant): AddMenuEntry => variant.id === "paragraph"
            ? {type: "Paragraph", titleKey: variant.titleKey, icon: variant.icon}
            : {type: "Title", titleKey: variant.titleKey, icon: variant.icon, defaultProps: {heading: variant.id}})
            .concat({type: "Separator"}),
    },
    {
        titleKey: "menu.organisation",
        entries: [{type: "FieldSet"}, {type: "Repeatable"}],
    },
];
