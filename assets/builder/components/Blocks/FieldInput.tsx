import React, { ReactElement, ReactNode, PropsWithChildren } from "react";
import { EditableBlock } from "./EditableBlock";
import { InlineEditableText } from "../InlineEditableText";
import { BlockType } from "./Definition";
import { t } from "../../i18n";
import { FIELD_CLASSES, FIELD_TEXT_INSET_CLASSES, FIELD_LABEL_CLASSES, FIELD_LABEL_INPUT_CLASSES } from "../../utilities/typography";
import { AsteriskIcon, InfoIcon } from "@phosphor-icons/react"

type Props = { id: string;
    type?: BlockType;
    form: Record<string, any>;
    editionItems: ReactElement | ReactElement[];
    actions?: ReactNode;
    onLabelChange?: (value: string) => void;
    /** Absent : champ jamais obligatoire (enfant de condition), pas d'astérisque. */
    onRequiredChange?: (value: boolean) => void; } & PropsWithChildren;

/**
 * Astérisque d'un champ requis « à la Tally » : un clic rend le champ facultatif.
 * On le rend de nouveau requis depuis le menu d'options (interrupteur « Requis »).
 */
export const RequiredMark = ({onRemove}: { onRemove: () => void }) => (
    <button
        type="button"
        onClick={(e) => {
            e.preventDefault();
            onRemove();
        }}
        title={t("field.makeOptional")}
        aria-label={t("field.makeOptional")}
        className="flex items-center justify-center rounded-full ml-1 shrink-0 cursor-pointer text-destructive bg-muted flex-1 aspect-square w-4 h-4"
    >
        <AsteriskIcon size={12} />
    </button>
);

export const FieldInput = ({id, type, editionItems, actions, form, children, onLabelChange, onRequiredChange}: Props) => {
    return (
        <EditableBlock id={id} type={type} editionItems={editionItems} actions={actions}>
            <div className={FIELD_CLASSES}>
                <label className={`self-start flex max-w-full items-baseline ${FIELD_TEXT_INSET_CLASSES} ${FIELD_LABEL_CLASSES}`} htmlFor={id}>
                    <InlineEditableText
                        value={form.label}
                        onCommit={(v) => onLabelChange?.(v)}
                        blockId={id}
                        placeholder={t("field.labelPlaceholder")}
                        className={FIELD_LABEL_INPUT_CLASSES}
                    />
                    {onRequiredChange && form.required && <RequiredMark onRemove={() => onRequiredChange(false)}/>}
                </label>

                {children}

                {form.helpText && <FieldHelp text={form.helpText}/>}
            </div>
        </EditableBlock>
    )
};

/** Aide d'un champ, précédée d'une icône « info » (même rendu que le thème de formulaire de l'application). */
export const FieldHelp = ({text}: { text: string }) => (
    // text-xs, icône centrée sur la première ligne (conteneur flex h-lh), à 6px du champ (-mt-0.5 sur le gap-2), comme le thème du formulaire.
    <p className="-mt-0.5 flex items-start gap-1.5 text-xs leading-normal font-normal text-muted-foreground">
        <span className="flex h-lh shrink-0 items-center"><InfoIcon aria-hidden="true" className="size-3.5"/></span>
        <span>{text}</span>
    </p>
);
