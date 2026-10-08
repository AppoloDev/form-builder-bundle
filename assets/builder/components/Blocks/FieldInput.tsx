import React, { ReactElement, PropsWithChildren } from "react";
import { EditableBlock } from "./EditableBlock";
import { InlineEditableText } from "../InlineEditableText";
import { BlockType } from "./Definition";

type Props = { id: string;
    type?: BlockType;
    form: Record<string, any>;
    editionItems: ReactElement | ReactElement[];
    preview?: boolean;
    onLabelChange?: (value: string) => void; } & PropsWithChildren;

export const FieldInput = ({id, type, editionItems, form, children, preview, onLabelChange}: Props) => {
    return (
        <EditableBlock id={id} type={type} editionItems={editionItems} preview={preview}>
            <div className={preview ? "flex flex-col gap-2" : "flex flex-col gap-2 rounded-lg transition-colors"}>
                {(form.label || !preview) && (
                    <label className="text-sm font-medium self-start" htmlFor={id}>
                        {preview ? (
                            form.label
                        ) : (
                            <InlineEditableText
                                value={form.label}
                                onCommit={(v) => onLabelChange?.(v)}
                                placeholder="Label"
                            />
                        )}
                        {form.required && <span className="text-destructive">*</span>}
                    </label>
                )}

                {children}

                {form.helpText && (
                    <div className="text-sm text-muted-foreground">
                        {form.helpText}
                    </div>
                )}
            </div>
        </EditableBlock>
    )
};
