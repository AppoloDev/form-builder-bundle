import React, { ReactElement, PropsWithChildren } from "react";
import { EditableBlock } from "./EditableBlock";
import { InlineEditableText } from "../InlineEditableText";
import { BlockType } from "./Definition";
import { t } from "../../i18n";
import { FIELD_CLASSES, FIELD_DESCRIPTION_CLASSES, FIELD_LABEL_CLASSES, FIELD_LABEL_INPUT_CLASSES } from "../../utilities/typography";
import { AsteriskIcon } from "@phosphor-icons/react"

type Props = { id: string;
    type?: BlockType;
    form: Record<string, any>;
    editionItems: ReactElement | ReactElement[];
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

export const FieldInput = ({id, type, editionItems, form, children, onLabelChange, onRequiredChange}: Props) => {
    return (
        <EditableBlock id={id} type={type} editionItems={editionItems}>
            <div className={FIELD_CLASSES}>
                <label className={`self-start flex max-w-full items-baseline ${FIELD_LABEL_CLASSES}`} htmlFor={id}>
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

                {form.helpText && (
                    <p className={FIELD_DESCRIPTION_CLASSES}>
                        {form.helpText}
                    </p>
                )}
            </div>
        </EditableBlock>
    )
};
