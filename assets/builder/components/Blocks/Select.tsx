import { useEffect, useState } from "react";
import { SelectProps, ConditionOperator, ConditionRule, SelectOption, BlockDefinition, Block } from "./Definition";
import { useFormBuilderStore } from "../../stores/block.store";
import { FieldInput } from "./FieldInput";
import { TextEdition } from "../Edition/TextEdition";
import { CheckboxEdition } from "../Edition/CheckboxEdition";
import { OptionsEdition } from "../Edition/OptionsEdition";
import { ConditionRules } from "./ConditionRules";
import { createBlockFromTemplate } from "../../utilities/block.utiles";
import { labelToName } from "../../utilities/string.utiles";
import { Select as SelectField, SelectContent, SelectItem, SelectTrigger, SelectValue } from "../ui/select";
import { Button } from "../ui/button";
import { v4 as uuidv4 } from "uuid";
import { GitPullRequest, Plus } from "lucide-react";

type Props = Omit<SelectProps, 'id'> & {
    id: string;
    isChildBlock?: boolean;
    preview?: boolean;
    useContionnalField?: boolean
};

const Select = (
    {
        id,
        type: blockType,
        label: propsLabel,
        name: propsName,
        helpText: propsHelpText,
        required: propsRequired,
        multiple: propsMultiple,
        options: propsOptions,
        conditions: propsConditions,
        isChildBlock,
        preview,
        useContionnalField: propsUseContionnalField = true,
    }: Props) => {
    const {updateBlock} = useFormBuilderStore();

    const [form, setForm] = useState({
        label: propsLabel || "",
        name: propsName || labelToName(propsLabel || ""),
        helpText: propsHelpText || "",
        required: propsRequired ?? false,
        multiple: propsMultiple ?? false,
        options: (propsOptions || []) as SelectOption[],
        conditions: (propsConditions || []) as ConditionRule[],
    });

    useEffect(() => {
        setForm({
            label: propsLabel || "",
            name: propsName || labelToName(propsLabel || ""),
            helpText: propsHelpText || "",
            required: propsRequired ?? false,
            multiple: propsMultiple ?? false,
            options: (propsOptions || []) as SelectOption[],
            conditions: (propsConditions || []) as ConditionRule[],
        });
    }, [propsLabel, propsName, propsHelpText, propsRequired, propsMultiple, JSON.stringify(propsOptions), JSON.stringify(propsConditions)]);

    useEffect(() => {
        if (form.options.length === 0) {
            const defaults: SelectOption[] = [
                {id: uuidv4(), label: "Option 1"},
                {id: uuidv4(), label: "Option 2"},
                {id: uuidv4(), label: "Option 3"},
            ];
            setForm(prev => ({...prev, options: defaults}));
            updateBlock(id, {options: defaults});
        }
    }, []);

    const handleChange = <K extends keyof typeof form>(key: K, value: (typeof form)[K]) => {
        if (key === 'label') {
            const newName = labelToName(value as string);
            const patch = {label: value as string, name: newName};
            setForm(prev => ({...prev, ...patch}));
            updateBlock(id, patch);
        } else if (key === 'options') {
            const nextOptions = value as unknown as SelectOption[];
            const remainingIds = new Set(nextOptions.map((o) => o.id));
            const nextConditions = form.conditions.filter((r) => remainingIds.has(r.optionId));
            const patch = {options: nextOptions, conditions: nextConditions} as Partial<typeof form>;
            setForm(prev => ({...prev, ...patch}));
            updateBlock(id, patch);
        } else {
            const patch = {[key]: value};
            setForm(prev => ({...prev, ...patch as Partial<typeof form>}));
            updateBlock(id, patch);
        }
    };

    const addCondition = () => {
        const newRule: ConditionRule = {id: uuidv4(), operator: 'is', optionId: form.options[0]?.id ?? "", children: []};
        const patch = {conditions: [...form.conditions, newRule]};
        setForm(prev => ({...prev, ...patch}));
        updateBlock(id, patch);
    };

    const updateRuleOption = (ruleId: string, optionId: string) => {
        const nextConditions = form.conditions.map((r) => (r.id === ruleId ? {...r, optionId} : r));
        const patch = {conditions: nextConditions};
        setForm(prev => ({...prev, ...patch}));
        updateBlock(id, patch);
    };

    const updateRuleOperator = (ruleId: string, operator: ConditionOperator) => {
        const nextConditions = form.conditions.map((r) => (r.id === ruleId ? {...r, operator} : r));
        const patch = {conditions: nextConditions};
        setForm(prev => ({...prev, ...patch}));
        updateBlock(id, patch);
    };

    const removeRule = (ruleId: string) => {
        const nextConditions = form.conditions.filter((r) => r.id !== ruleId);
        const patch = {conditions: nextConditions};
        setForm(prev => ({...prev, ...patch}));
        updateBlock(id, patch);
    };

    const addBlockToRule = (ruleId: string, def: BlockDefinition, overrides?: Record<string, any>) => {
        const newBlock = createBlockFromTemplate(def, overrides);
        const nextConditions = form.conditions.map((r) =>
            r.id === ruleId ? {...r, children: [...r.children, newBlock]} : r
        );
        const patch = {conditions: nextConditions};
        setForm(prev => ({...prev, ...patch}));
        updateBlock(id, patch);
    };

    const reorderRuleChildren = (ruleId: string, nextChildren: Block[]) => {
        const nextConditions = form.conditions.map((r) =>
            r.id === ruleId ? {...r, children: nextChildren} : r
        );
        const patch = {conditions: nextConditions};
        setForm(prev => ({...prev, ...patch}));
        updateBlock(id, patch);
    };

    const editionItems = [
        <TextEdition key="label" label="Titre" value={form.label} editItem={(v) => handleChange("label", v)}/>,
        <TextEdition key="helpText" label="Message d'aide" type="textarea" value={form.helpText}
                     editItem={(v) => handleChange("helpText", v)}/>,
        ...(isChildBlock ? [] : [
            <CheckboxEdition key="required" label="Requis" checked={form.required}
                             editItem={(v: boolean) => handleChange("required", v)}/>
        ]),
        <CheckboxEdition key="multiple" label="Sélection multiple" checked={form.multiple}
                         editItem={(v) => handleChange("multiple", v)}/>,
        <OptionsEdition key="options" label="Options" value={form.options}
                        onChange={(v) => handleChange("options", v)}
                        helpText="Ajoutez, modifiez ou supprimez les options"/>,
        ...(propsUseContionnalField ? [
            <div className="border-t pt-4">
                <Button
                    key="addCondition"
                    type="button"
                    variant="ghost"
                    className="w-full justify-start"
                    onClick={addCondition}
                    disabled={form.options.length === 0}

                >
                    <GitPullRequest/>
                    Ajouter une logique conditionnelle
                </Button>
            </div>
        ] : []),
    ];

    return (
        <FieldInput id={id} type={blockType} form={form} editionItems={editionItems} preview={preview}
                    onLabelChange={(v) => handleChange("label", v)}>
            <SelectField
                disabled
                items={form.options.map((opt) => ({value: opt.label, label: opt.label}))}
                defaultValue={form.options[0]?.label}
            >
                <SelectTrigger className="w-full">
                    <SelectValue placeholder="Sélectionner…"/>
                </SelectTrigger>
                <SelectContent>
                    {form.options.map((opt) => (
                        <SelectItem key={opt.id} value={opt.label}>{opt.label}</SelectItem>
                    ))}
                </SelectContent>
            </SelectField>

            {propsUseContionnalField && (
                <ConditionRules
                    conditions={form.conditions}
                    options={form.options.map((o) => ({id: o.id, label: o.label}))}
                    onChangeRuleOption={updateRuleOption}
                    onChangeRuleOperator={updateRuleOperator}
                    onRemoveRule={removeRule}
                    onAddBlockToRule={addBlockToRule}
                    onReorderRuleChildren={reorderRuleChildren}
                />
            )}
        </FieldInput>
    );
};

export default Select;
