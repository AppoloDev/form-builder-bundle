import { FC, useEffect, useMemo, useState } from "react";
import { FieldInput } from "./FieldInput";
import { TextEdition } from "../Edition/TextEdition";
import { CheckboxEdition } from "../Edition/CheckboxEdition";
import { ConditionRules } from "./ConditionRules";
import { Block, BlockDefinition, BlockType, ConditionOperator, ConditionRule, OptionItem } from "./Definition";
import { createBlockFromTemplate } from "../../utilities/block.utiles";
import { useFormBuilderStore } from "../../stores/block.store";
import { v4 as uuidv4 } from "uuid";
import { Button } from "../ui/button";
import { Input } from "../ui/input";
import { Checkbox } from "../ui/checkbox";
import { RadioGroup, RadioGroupItem } from "../ui/radio-group";
import { GitPullRequest, Plus, Trash } from "lucide-react";

type Props = {
    id: string;
    type?: BlockType;
    helpText?: string;
    label?: string;
    required?: boolean;
    readOnly?: boolean;
    multiple?: boolean;
    options?: OptionItem[];
    conditions?: ConditionRule[];
    useContionnalField?: boolean;
    isChildBlock?: boolean;
    preview?: boolean;
};

const ChoiceGroupInput: FC<Props> = (props) => {
    const {
        id,
        type: blockType,
        helpText: propsHelpText,
        label: propsLabel,
        required: propsRequired,
        readOnly: propsReadOnly,
        multiple: propsMultiple,
        options: propsOptions,
        conditions: propsConditions,
        useContionnalField: propsUseContionnalField = true,
        isChildBlock,
        preview,
    } = props;

    const {updateBlock} = useFormBuilderStore();

    const [form, setForm] = useState({
        label: propsLabel || "",
        helpText: propsHelpText || "",
        required: propsRequired ?? false,
        readOnly: propsReadOnly ?? false,
        multiple: propsMultiple ?? false,
        options: (propsOptions || []) as OptionItem[],
        conditions: (propsConditions || []) as ConditionRule[],
    });

    const [pendingFocusOptionId, setPendingFocusOptionId] = useState<string | null>(null);

    useEffect(() => {
        if (!pendingFocusOptionId) return;
        const el = document.querySelector<HTMLInputElement>(`[data-option-id="${pendingFocusOptionId}"]`);
        el?.focus();
        setPendingFocusOptionId(null);
    }, [pendingFocusOptionId, form.options]);

    useEffect(() => {
        setForm({
            label: propsLabel || "",
            helpText: propsHelpText || "",
            required: propsRequired ?? false,
            readOnly: propsReadOnly ?? false,
            multiple: propsMultiple ?? false,
            options: (propsOptions || []) as OptionItem[],
            conditions: (propsConditions || []) as ConditionRule[],
        });
    }, [
        propsLabel,
        propsHelpText,
        propsRequired,
        propsReadOnly,
        propsMultiple,
        JSON.stringify(propsOptions),
        JSON.stringify(propsConditions),
    ]);

    useEffect(() => {
        if (form.options.length === 0) {
            const defaults: OptionItem[] = [
                {id: uuidv4(), label: "", value: ""},
            ];
            const patch = {options: defaults};
            setForm(prev => ({...prev, ...patch}));
            updateBlock(id, patch);
        }
    }, []);

    const handleChange = <K extends keyof typeof form>(key: K, value: (typeof form)[K]) => {
        if (key === 'label') {
            const patch = {label: value as string};
            setForm(prev => ({...prev, ...patch}));
            updateBlock(id, patch);
        } else {
            const patch = {[key]: value};
            setForm(prev => ({...prev, ...patch as Partial<typeof form>}));
            updateBlock(id, patch);
        }
    };

    const addCondition = () => {
        const newRule: ConditionRule = {
            id: uuidv4(),
            operator: 'is',
            optionId: form.options[0]?.id ?? "",
            children: []
        };
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

    const editionItems = useMemo(
        () => {
            const items = [
                <TextEdition key="label" label="Titre" value={form.label} editItem={(v) => handleChange("label", v)}/>,
                <TextEdition key="helpText" label="Message d'aide" value={form.helpText} type="textarea"
                             editItem={(v) => handleChange("helpText", v)}/>,
                <CheckboxEdition key="required" label="Requis" checked={form.required}
                                 editItem={(v) => handleChange("required", v)}/>,
                <CheckboxEdition
                    key="multiple"
                    label="Sélections multiples"
                    checked={form.multiple}
                    editItem={(v) => handleChange("multiple", v)}
                />,
                <CheckboxEdition key="readOnly" label="Lecture seule" checked={form.readOnly}
                                 editItem={(v) => handleChange("readOnly", v)}/>,
            ];

            const filtered = isChildBlock ? items.filter(item => item.key !== 'required') : [...items];

            if (propsUseContionnalField) {
                filtered.push(
                    <div className="border-t pt-4">
                        <Button
                            key="addCondition"
                            type="button"
                            variant="ghost"
                            onClick={addCondition}
                            className="w-full justify-start"
                            disabled={form.options.length === 0}
                        >
                            <GitPullRequest/>
                            Ajouter une logique conditionnelle
                        </Button>
                    </div>
                );
            }

            return filtered;
        },
        [form.label, form.helpText, form.required, form.readOnly, form.multiple, form.options, form.conditions, isChildBlock, propsUseContionnalField]
    );

    const addOption = () => {
        const newOption = {id: uuidv4(), label: "", value: ""};

        const patch = {options: [...form.options, newOption]};
        setForm(prev => ({...prev, ...patch}));
        updateBlock(id, patch);
        setPendingFocusOptionId(newOption.id);
    };

    const updateOption = (idx: number, optionPatch: Partial<OptionItem>) => {
        const nextOptions = form.options.map((o, i) => (i === idx ? {...o, ...optionPatch} : o));

        const patch = {options: nextOptions};
        setForm(prev => ({...prev, ...patch}));
        updateBlock(id, patch);
    };

    const removeOption = (idx: number) => {
        const removedId = form.options[idx].id;
        const nextOptions = form.options.filter((_, i) => i !== idx);
        const nextConditions = form.conditions.filter((r) => r.optionId !== removedId);

        const patch = {options: nextOptions, conditions: nextConditions};
        setForm(prev => ({...prev, ...patch}));
        updateBlock(id, patch);
    };

    const renderOptionRow = (opt: OptionItem, idx: number) => (
        <div key={opt.id} className="group/option relative rounded-md">
            <div className="flex items-center gap-2">
                {form.multiple ? (
                    <Checkbox disabled/>
                ) : (
                    <RadioGroupItem value={opt.id} disabled/>
                )}

                <Input
                    data-option-id={opt.id}
                    value={opt.label}
                    placeholder={`Option ${idx + 1}`}
                    onChange={(e) =>
                        updateOption(idx, {label: e.target.value, value: e.target.value})
                    }
                    className="border-transparent bg-transparent hover:border-input focus-visible:border-ring"
                />

                <div className="flex gap-0.5 opacity-0 transition-opacity group-hover/option:opacity-100">
                    <Button
                        type="button"
                        variant="ghost"
                        size="icon-sm"
                        onClick={() => removeOption(idx)}
                        className="text-destructive hover:text-destructive"
                        aria-label="Supprimer l'option"
                    >
                        <Trash/>
                    </Button>
                </div>
            </div>
        </div>
    );

    return (
        <FieldInput id={id} type={blockType} form={form} editionItems={editionItems} preview={preview}
                    onLabelChange={(v) => handleChange("label", v)}>
            <div className="space-y-3">
                {form.multiple ? (
                    <div className="space-y-1">
                        {form.options.map(renderOptionRow)}
                    </div>
                ) : (
                    <RadioGroup className="space-y-1">
                        {form.options.map(renderOptionRow)}
                    </RadioGroup>
                )}

                {!preview && <div className="flex justify-start">
                    <Button type="button" variant="ghost" onClick={addOption}
                            className="text-muted-foreground">
                        <Plus/>
                        Ajouter une option
                    </Button>
                </div>}

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
            </div>
        </FieldInput>
    );
};

export default ChoiceGroupInput;
