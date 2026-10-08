import { useEffect, useState } from "react";
import { SignatureProps } from "./Definition";
import { useFormBuilderStore } from "../../stores/block.store";
import { EditableBlock } from "./EditableBlock";
import { TextEdition } from "../Edition/TextEdition";
import { CheckboxEdition } from "../Edition/CheckboxEdition";
import { InlineEditableText } from "../InlineEditableText";

type Props = SignatureProps & { isChildBlock?: boolean; preview?: boolean };

const Signature = ({id, type, label, helpText, required, isChildBlock, preview}: Props) => {
    const {updateBlock} = useFormBuilderStore();

    const [form, setForm] = useState({
        label: label || "",
        helpText: helpText || "",
        required: required ?? false,
    });

    useEffect(() => {
        setForm({
            label: label || "",
            helpText: helpText || "",
            required: required ?? false,
        });
    }, [label, helpText, required]);

    const handleChange = <K extends keyof typeof form>(field: K, value: (typeof form)[K]) => {
        const patch = {[field]: value};
        setForm(prev => ({...prev, ...patch}));
        updateBlock(id, patch);
    };

    const editionItems = [
        <TextEdition key="label" label="Libellé" value={form.label} editItem={(v) => handleChange('label', v)}/>,
        <TextEdition key="helpText" label="Message d'aide" type="textarea" value={form.helpText}
                     editItem={(v) => handleChange('helpText', v)}/>,
        ...(isChildBlock ? [] : [
            <CheckboxEdition key="required" label="Requis" checked={form.required}
                             editItem={(v: boolean) => handleChange('required', v)}/>
        ]),
    ];

    return (
        <EditableBlock id={id} type={type} preview={preview} editionItems={editionItems}>
            <div className={preview ? "flex flex-col gap-2" : "flex flex-col gap-2 rounded-lg transition-colors"}>
                <label className="text-sm font-medium">
                    {preview ? (
                        form.label
                    ) : (
                        <InlineEditableText
                            value={form.label}
                            onCommit={(v) => handleChange('label', v)}
                            placeholder="Libellé"
                        />
                    )}
                    {form.required && <span className="text-destructive">*</span>}
                </label>

                <div
                    className="flex h-24 items-center justify-center rounded-lg border border-dashed border-border text-sm text-muted-foreground">
                    Zone de signature
                </div>

                {form.helpText && <div className="text-sm text-muted-foreground">{form.helpText}</div>}
            </div>
        </EditableBlock>
    );
};

export default Signature;
