import { useEffect, useState } from "react";
import { SignatureProps } from "./Definition";
import { useFormBuilderStore } from "../../stores/block.store";
import { EditableBlock } from "./EditableBlock";
import { TextEdition } from "../Edition/TextEdition";
import { CheckboxEdition } from "../Edition/CheckboxEdition";
import { InlineEditableText } from "../InlineEditableText";
import { RequiredMark } from "./FieldInput";
import { t } from "../../i18n";
import { SignatureIcon } from "@phosphor-icons/react";
import { Tooltip } from "../Tooltip";
import { blockDefinitions } from "./Definition";
import { FIELD_CLASSES, FIELD_DESCRIPTION_CLASSES, FIELD_TEXT_INSET_CLASSES, FIELD_LABEL_CLASSES, FIELD_LABEL_INPUT_CLASSES } from "../../utilities/typography";

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
        <TextEdition key="helpText" optional label={t("field.helpText")} type="textarea" value={form.helpText}
                     editItem={(v) => handleChange('helpText', v)}/>,
        ...(isChildBlock ? [] : [
            <CheckboxEdition key="required" label={t("field.required")} checked={form.required}
                             editItem={(v: boolean) => handleChange('required', v)}/>
        ]),
    ];

    return (
        <EditableBlock id={id} type={type} editionItems={editionItems}>
            <div className={FIELD_CLASSES}>
                <label className={`self-start flex max-w-full items-baseline ${FIELD_TEXT_INSET_CLASSES} ${FIELD_LABEL_CLASSES}`}>
                    <InlineEditableText
                        value={form.label}
                        onCommit={(v) => handleChange('label', v)}
                        blockId={id}
                        placeholder={t("field.labelPlaceholder")}
                        className={FIELD_LABEL_INPUT_CLASSES}
                    />
                    {!isChildBlock && form.required && <RequiredMark onRemove={() => handleChange('required', false)}/>}
                </label>

                {/* Même rendu que le formulaire (thème signature_row) : ligne de signature en bas, sur la largeur utile. */}
                <div className="relative flex aspect-video w-full max-w-sm items-end rounded-lg border border-input px-4">
                    <span className="w-full border-t border-input py-2 text-center text-xs text-muted-foreground">{t("signature.area")}</span>
                    {/* Même emplacement que l'icône des autres champs (GenericInput). */}
                    <div className="absolute top-2.5 right-2">
                        <Tooltip content={blockDefinitions.Signature.title}>
                            <SignatureIcon className="size-3.5 text-muted-foreground"/>
                        </Tooltip>
                    </div>
                </div>

                {form.helpText && <p className={FIELD_DESCRIPTION_CLASSES}>{form.helpText}</p>}
            </div>
        </EditableBlock>
    );
};

export default Signature;
