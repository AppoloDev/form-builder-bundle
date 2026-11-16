import { useEffect, useState } from "react";
import { SelectProps, ConditionOperator, ConditionRule, SelectOption, BlockDefinition, Block } from "./Definition";
import { useFormBuilderStore } from "../../stores/block.store";
import { FieldInput } from "./FieldInput";
import { TextEdition } from "../Edition/TextEdition";
import { CheckboxEdition } from "../Edition/CheckboxEdition";
import { OptionList } from "./OptionList";
import { ConditionRules } from "./ConditionRules";
import { createBlockFromTemplate } from "../../utilities/block.utiles";
import { Button } from "../ui/button";
import { v4 as uuidv4 } from "uuid";
import { GitPullRequestIcon } from "@phosphor-icons/react";
import { t } from "../../i18n";

type Props = Omit<SelectProps, 'id'> & {
    id: string;
    isChildBlock?: boolean;
    useContionnalField?: boolean
};

const Select = (
    {
        id,
        type: blockType,
        label: propsLabel,
        helpText: propsHelpText,
        required: propsRequired,
        readOnly: propsReadOnly,
        multiple: propsMultiple,
        customOption: propsCustomOption,
        options: propsOptions,
        conditions: propsConditions,
        isChildBlock,
        useContionnalField: propsUseContionnalField = true,
    }: Props) => {
    const {updateBlock} = useFormBuilderStore();

    const [form, setForm] = useState({
        label: propsLabel || "",
        helpText: propsHelpText || "",
        required: propsRequired ?? false,
        readOnly: propsReadOnly ?? false,
        multiple: propsMultiple ?? false,
        customOption: propsCustomOption ?? false,
        options: (propsOptions || []) as SelectOption[],
        conditions: (propsConditions || []) as ConditionRule[],
    });

    useEffect(() => {
        setForm({
            label: propsLabel || "",
            helpText: propsHelpText || "",
            required: propsRequired ?? false,
            readOnly: propsReadOnly ?? false,
            multiple: propsMultiple ?? false,
            customOption: propsCustomOption ?? false,
            options: (propsOptions || []) as SelectOption[],
            conditions: (propsConditions || []) as ConditionRule[],
        });
    }, [propsLabel, propsHelpText, propsRequired, propsReadOnly, propsMultiple, propsCustomOption, JSON.stringify(propsOptions), JSON.stringify(propsConditions)]);

    useEffect(() => {
        if (form.options.length === 0) {
            const defaults: SelectOption[] = [
                {id: uuidv4(), label: t("option.default", {n: 1})},
                {id: uuidv4(), label: t("option.default", {n: 2})},
                {id: uuidv4(), label: t("option.default", {n: 3})},
            ];
            setForm(prev => ({...prev, options: defaults}));
            updateBlock(id, {options: defaults});
        }
    }, []);

    const handleChange = <K extends keyof typeof form>(key: K, value: (typeof form)[K]) => {
        if (key === 'label') {
            const patch = {label: value as string};
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
        const newRule: ConditionRule = {id: uuidv4(), operator: '', optionId: "", children: []};
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

    const addBlockToRule = (ruleId: string, def: BlockDefinition) => {
        const newBlock = createBlockFromTemplate(def);
        const nextConditions = form.conditions.map((r) =>
            r.id === ruleId ? {...r, children: [...r.children, newBlock]} : r
        );
        const patch = {conditions: nextConditions};
        setForm(prev => ({...prev, ...patch}));
        updateBlock(id, patch);
    };

    const editionItems = [
        <TextEdition key="label" label={t("field.title")} value={form.label} editItem={(v) => handleChange("label", v)}/>,
        <TextEdition key="helpText" optional label={t("field.helpText")} type="textarea" value={form.helpText}
                     editItem={(v) => handleChange("helpText", v)}/>,
        ...(isChildBlock ? [] : [
            <CheckboxEdition key="required" label={t("field.required")} checked={form.required}
                             editItem={(v: boolean) => handleChange("required", v)}/>
        ]),
        <CheckboxEdition key="multiple" label={t("field.multiple")} checked={form.multiple}
                         editItem={(v) => handleChange("multiple", v)}/>,
        <CheckboxEdition key="customOption" label={t("select.customOption")} checked={form.customOption}
                         editItem={(v) => handleChange("customOption", v)}/>,
        <CheckboxEdition key="readOnly" label={t("field.readOnly")} checked={form.readOnly}
                         editItem={(v) => handleChange("readOnly", v)}/>,
    ];

    return (
        <FieldInput id={id} type={blockType} form={form} editionItems={editionItems}
                    actions={propsUseContionnalField && (
                        <Button
                            type="button"
                            variant="ghost"
                            className="w-full justify-start"
                            onClick={addCondition}
                            disabled={form.options.length === 0}
                        >
                            <GitPullRequestIcon/> {t("condition.add")}
                        </Button>
                    )}
                    onLabelChange={(v) => handleChange("label", v)}
                    onRequiredChange={isChildBlock ? undefined : (v) => handleChange("required", v)}>
            <OptionList
                options={form.options}
                onChange={(v) => handleChange("options", v)}
                createOption={(optionId) => ({id: optionId, label: ""})}
                indicator="chevron"
                readOnly={form.readOnly}
            />

            {propsUseContionnalField && (
                <ConditionRules
                    conditions={form.conditions}
                    options={form.options.map((o) => ({id: o.id, label: o.label}))}
                    onChangeRuleOption={updateRuleOption}
                    onChangeRuleOperator={updateRuleOperator}
                    onRemoveRule={removeRule}
                    onAddBlockToRule={addBlockToRule}
                />
            )}
        </FieldInput>
    );
};

export default Select;
