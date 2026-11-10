import React, { ReactElement, PropsWithChildren } from "react";
import { EditableBlock } from "./EditableBlock";
import { InlineEditableText } from "../InlineEditableText";
import { BlockType, blockDefinitions } from "./Definition";
import { t } from "../../i18n";
import { FIELD_CLASSES, FIELD_DESCRIPTION_CLASSES, FIELD_LABEL_CLASSES, FIELD_LABEL_INPUT_CLASSES } from "../../utilities/typography";

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
        className="ml-1 cursor-pointer text-muted-foreground transition-opacity hover:opacity-60"
    >
        *
    </button>
);

export const FieldInput = ({id, type, editionItems, form, children, onLabelChange, onRequiredChange}: Props) => {
    return (
        <EditableBlock id={id} type={type} editionItems={editionItems}>
            <div className={FIELD_CLASSES}>
                <label className={`self-start ${FIELD_LABEL_CLASSES}`} htmlFor={id}>
                    <InlineEditableText
                        value={form.label}
                        onCommit={(v) => onLabelChange?.(v)}
                        blockId={id}
                        placeholder={(type && blockDefinitions[type]?.title) || t("field.label")}
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
