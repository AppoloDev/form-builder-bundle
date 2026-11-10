import { type Icon, TextHOneIcon, TextHTwoIcon, TextHThreeIcon } from "@phosphor-icons/react";
import type { MessageKey } from "../../i18n";
import { type Block, type ParagraphProps, type TitleProps, blockDefinitions } from "./Definition";

/**
 * Variantes des blocs de présentation : titres 1 à 3 et texte explicatif, interchangeables (« Transformer en »).
 * Les titres ont chacun leur icône (un seul type `Title`, donc une seule icône dans Definition) ; le texte explicatif
 * reprend celle de sa définition.
 */
export type TextVariant = "h1" | "h2" | "h3" | "paragraph";

export const TEXT_VARIANTS: { id: TextVariant; titleKey: MessageKey; icon: Icon }[] = [
    {id: "h1", titleKey: "text.h1", icon: TextHOneIcon},
    {id: "h2", titleKey: "text.h2", icon: TextHTwoIcon},
    {id: "h3", titleKey: "text.h3", icon: TextHThreeIcon},
    {id: "paragraph", titleKey: "def.Paragraph.title", icon: blockDefinitions.Paragraph.icon!},
];

type TextBlock = TitleProps | ParagraphProps;

/** Variante courante ; un ancien titre de niveau 4 à 6 n'en a aucune. */
export const textVariantOf = (block: Pick<TextBlock, "type"> & { heading?: string }): TextVariant | undefined => {
    const id = block.type === "Paragraph" ? "paragraph" : block.heading;

    return TEXT_VARIANTS.find((variant) => variant.id === id)?.id;
};

/** Même bloc (id et texte conservés) sous une autre variante, sans clé héritée de l'ancien type. */
export const transformTextBlock = (block: TextBlock, variant: TextVariant): Block =>
    variant === "paragraph"
        ? {id: block.id, type: "Paragraph", text: block.text}
        : {id: block.id, type: "Title", text: block.text, heading: variant};
