import { useEffect, useState } from "react";
import { SignatureProps } from "./Definition";
import { useFormBuilderStore } from "../../stores/block.store";
import { EditableBlock } from "./EditableBlock";
import { TextEdition } from "../Edition/TextEdition";
import { CheckboxEdition } from "../Edition/CheckboxEdition";
import { InlineEditableText } from "../InlineEditableText";
import { RequiredMark } from "./FieldInput";
import { t } from "../../i18n";
import { FIELD_CLASSES, FIELD_DESCRIPTION_CLASSES, FIELD_LABEL_CLASSES, FIELD_LABEL_INPUT_CLASSES } from "../../utilities/typography";

type Props = SignatureProps & { isChildBlock?: boolean };

const Signature = ({id, type, label, helpText, required, isChildBlock}: Props) => {
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
        <TextEdition key="label" label={t("field.label")} value={form.label} editItem={(v) => handleChange('label', v)}/>,
        <TextEdition key="helpText" label={t("field.helpText")} type="textarea" value={form.helpText}
                     editItem={(v) => handleChange('helpText', v)}/>,
        ...(isChildBlock ? [] : [
            <CheckboxEdition key="required" label={t("field.required")} checked={form.required}
                             editItem={(v: boolean) => handleChange('required', v)}/>
        ]),
    ];

    return (
        <EditableBlock id={id} type={type} editionItems={editionItems}>
            <div className={FIELD_CLASSES}>
                <label className={`self-start flex max-w-full items-baseline ${FIELD_LABEL_CLASSES}`}>
                    <InlineEditableText
                        value={form.label}
                        onCommit={(v) => handleChange('label', v)}
                        blockId={id}
                        placeholder={t("field.labelPlaceholder")}
                        className={FIELD_LABEL_INPUT_CLASSES}
                    />
                    {!isChildBlock && form.required && <RequiredMark onRemove={() => handleChange('required', false)}/>}
                </label>

                <div
                    className="flex h-24 items-center justify-center rounded-lg border border-input text-sm text-muted-foreground">
                    {t("signature.area")}
                </div>

                {form.helpText && <p className={FIELD_DESCRIPTION_CLASSES}>{form.helpText}</p>}
            </div>
        </EditableBlock>
    );
};

export default Signature;
