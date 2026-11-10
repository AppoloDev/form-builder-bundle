import { CheckIcon } from "@phosphor-icons/react";
import { Button } from "../ui/button";
import { TEXT_VARIANTS, TextVariant } from "../Blocks/TextVariants";
import { FIELD_CLASSES, FIELD_LABEL_CLASSES } from "../../utilities/typography";
import { t } from "../../i18n";

type Props = {
    current?: TextVariant;
    onTransform: (variant: TextVariant) => void;
};

/** « Transformer en » du menu d'options : titres 1 à 3 et texte explicatif, la variante courante cochée. */
export const TransformEdition = ({current, onTransform}: Props) => (
    <div className={FIELD_CLASSES}>
        <span className={FIELD_LABEL_CLASSES}>{t("block.transformTo")}</span>
        <div className="-mx-2 flex flex-col">
            {TEXT_VARIANTS.map((variant) => (
                <Button
                    key={variant.id}
                    type="button"
                    variant="ghost"
                    aria-pressed={variant.id === current}
                    onClick={() => variant.id !== current && onTransform(variant.id)}
                    className="h-8 w-full justify-start gap-2 px-2 font-normal"
                >
                    <variant.icon className="size-4 text-muted-foreground" aria-hidden="true"/>
                    <span className="flex-1 text-left">{t(variant.titleKey)}</span>
                    {variant.id === current && <CheckIcon className="size-4" aria-hidden="true"/>}
                </Button>
            ))}
        </div>
    </div>
);
