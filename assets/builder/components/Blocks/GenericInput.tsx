import React, { FC, HTMLInputTypeAttribute, useMemo, useState, useEffect } from "react";
import { useFormBuilderStore } from "../../stores/block.store";
import { TextEdition } from "../Edition/TextEdition";
import { CheckboxEdition } from "../Edition/CheckboxEdition";
import { SelectEdition } from "../Edition/SelectEdition";
import { FieldInput } from "./FieldInput";
import { blockDefinitions } from "./Definition";
import { labelToName } from "../../utilities/string.utiles";
import { Input } from "../ui/input";
import { Textarea } from "../ui/textarea";
import { InputGroup, InputGroupAddon } from "../ui/input-group";
import { Tooltip } from "../Tooltip";

type CommonProps = {
    id: string;
    helpText?: string;
    label?: string;
    placeHolder?: string;
    required?: boolean;
    isChildBlock?: boolean;
    preview?: boolean;
    [key: string]: any;
};

type FormStateBase = {
    label: string;
    placeHolder: string;
    helpText: string;
    required: boolean;
};

export type EditionItem =
    | { key: keyof FormStateBase | string; label: string; type: "text" | "textarea" | "number"; helpText?: string; rows?: number }
    | { key: keyof FormStateBase | string; label: string; type: "checkbox" }
    | {
    key: keyof FormStateBase | string;
    label: string;
    type: "select";
    options: { value: string; label: string }[];
    helpText?: string
};

const baseSchema: EditionItem[] = [
    {key: "label", label: "Titre", type: "text"},
    {key: "placeHolder", label: "Placeholder", type: "text"},
    {key: "helpText", label: "Message d'aide", type: "textarea"},
    {key: "required", label: "Requis", type: "checkbox"},
];

type MakeOpts = {
    extraSchema?: EditionItem[];
    toInputAttrs?: (form: Record<string, any>) => Record<string, any>;
};

export const makeInputBlock = (
    inputType: React.HTMLInputTypeAttribute | HTMLTextAreaElement,
    opts: MakeOpts = {}
) => {
    const InputBlock: FC<CommonProps> = (props) => {
        const {id, type: blockType, index: _index, isChildBlock, preview, ...restProps} = props;
        const {updateBlock} = useFormBuilderStore();

        const [form, setForm] = useState<Record<string, any>>({
            ...restProps,
            label: restProps.label || "",
            placeHolder: restProps.placeHolder || "",
            helpText: restProps.helpText || "",
            required: restProps.required || false,
            name: restProps.name || labelToName(restProps.label || ""),
        });

        useEffect(() => {
            setForm({
                ...restProps,
                label: restProps.label || "",
                placeHolder: restProps.placeHolder || "",
                helpText: restProps.helpText || "",
                required: restProps.required || false,
                name: restProps.name || labelToName(restProps.label || ""),
            });
        }, [JSON.stringify(restProps)]);

        const editionSchema = useMemo(
            () => {
                let schema = [...baseSchema, ...(opts.extraSchema ?? [])];
                if (isChildBlock) {
                    schema = schema.filter(item => item.key !== 'required');
                }
                return schema;
            },
            [opts.extraSchema, isChildBlock]
        );

        const handleChange = (key: string, value: any) => {
            if (key === 'label') {
                const newName = labelToName(value);
                const patch = { label: value, name: newName };
                setForm(prev => ({ ...prev, ...patch }));
                updateBlock(id, patch);
            } else {
                const patch = { [key]: value };
                setForm(prev => ({ ...prev, ...patch }));
                updateBlock(id, patch);
            }
        };

        const editionItems = useMemo(
            () =>
                editionSchema.map(def => {
                    const value = form[def.key as string];

                    if (def.type === "checkbox") {
                        return (
                            <CheckboxEdition
                                key={String(def.key)}
                                label={def.label}
                                checked={Boolean(value)}
                                editItem={(v: boolean) => handleChange(def.key, v)}
                            />
                        );
                    }

                    if (def.type === "select") {
                        return (
                            <SelectEdition
                                key={String(def.key)}
                                label={def.label}
                                value={value ?? ""}
                                options={def.options}
                                helpText={def.helpText}
                                editItem={(v: string) => handleChange(def.key, v)}
                            />
                        );
                    }

                    return (
                        <TextEdition
                            key={String(def.key)}
                            label={def.label}
                            value={value ?? ""}
                            type={def.type || "text"}
                            helpText={def.helpText || ""}
                            rows={def.rows}
                            editItem={(v: string) => handleChange(def.key as string, v)}
                        />
                    );
                }),
            [editionSchema, form]
        );

        const rawInputAttrs = opts.toInputAttrs?.(form) ?? {};
        const {type: overrideType, ...restInputAttrs} = rawInputAttrs;
        const finalType = (overrideType as HTMLInputTypeAttribute) ?? inputType;
        const Icon = blockDefinitions[blockType as keyof typeof blockDefinitions]?.icon;

        return (
            <FieldInput
                editionItems={editionItems}
                form={form}
                id={id}
                type={blockType}
                preview={preview}
                onLabelChange={(v) => handleChange('label', v)}
            >
                <InputGroup>
                    {finalType === 'textarea' ?
                        <Textarea
                            id={id}
                            data-slot="input-group-control"
                            placeholder={form.placeHolder}
                            rows={props.rows}
                            disabled
                            className="border-0 shadow-none focus-visible:ring-0"
                            {...restInputAttrs}
                        />
                        :
                        <Input
                            id={id}
                            data-slot="input-group-control"
                            type={finalType}
                            placeholder={form.placeHolder}
                            disabled
                            className="border-0 shadow-none focus-visible:ring-0"
                            {...restInputAttrs}
                        />
                    }

                    {Icon && (
                        <InputGroupAddon align="inline-end">
                            <Tooltip content={blockDefinitions[blockType as keyof typeof blockDefinitions]?.title ?? ""}>
                                <Icon className="size-3.5 text-muted-foreground"/>
                            </Tooltip>
                        </InputGroupAddon>
                    )}
                </InputGroup>
            </FieldInput>
        );
    };

    return InputBlock;
};
