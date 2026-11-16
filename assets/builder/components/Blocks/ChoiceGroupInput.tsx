import { FC, useEffect, useMemo, useState } from "react";
import { FieldInput } from "./FieldInput";
import { TextEdition } from "../Edition/TextEdition";
import { CheckboxEdition } from "../Edition/CheckboxEdition";
import { ConditionRules } from "./ConditionRules";
import { OptionList } from "./OptionList";
import { Block, BlockDefinition, BlockType, ConditionOperator, ConditionRule, OptionItem } from "./Definition";
import { createBlockFromTemplate } from "../../utilities/block.utiles";
import { useFormBuilderStore } from "../../stores/block.store";
import { v4 as uuidv4 } from "uuid";
import { Button } from "../ui/button";
import { GitPullRequestIcon } from "@phosphor-icons/react";
import { t } from "../../i18n";

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

    const addBlockToRule = (ruleId: string, def: BlockDefinition) => {
        const newBlock = createBlockFromTemplate(def);
        const nextConditions = form.conditions.map((r) =>
            r.id === ruleId ? {...r, children: [...r.children, newBlock]} : r
        );
        const patch = {conditions: nextConditions};
        setForm(prev => ({...prev, ...patch}));
        updateBlock(id, patch);
    };

    const editionItems = useMemo(
        () => {
            const items = [
                <TextEdition key="label" label={t("field.title")} value={form.label} editItem={(v) => handleChange("label", v)}/>,
                <TextEdition key="helpText" label={t("field.helpText")} value={form.helpText} type="textarea"
                             editItem={(v) => handleChange("helpText", v)}/>,
                <CheckboxEdition key="required" label={t("field.required")} checked={form.required}
                                 editItem={(v) => handleChange("required", v)}/>,
                <CheckboxEdition
                    key="multiple"
                    label={t("field.multipleGroup")}
                    checked={form.multiple}
                    editItem={(v) => handleChange("multiple", v)}
                />,
                <CheckboxEdition key="readOnly" label={t("field.readOnly")} checked={form.readOnly}
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
                            <GitPullRequestIcon/>
                            {t("condition.add")}
                        </Button>
                    </div>
                );
            }

            return filtered;
        },
        [form.label, form.helpText, form.required, form.readOnly, form.multiple, form.options, form.conditions, isChildBlock, propsUseContionnalField]
    );

    const changeOptions = (nextOptions: OptionItem[]) => {
        const remainingIds = new Set(nextOptions.map((o) => o.id));
        const patch = {options: nextOptions, conditions: form.conditions.filter((r) => remainingIds.has(r.optionId))};
        setForm(prev => ({...prev, ...patch}));
        updateBlock(id, patch);
    };

    return (
        <FieldInput id={id} type={blockType} form={form} editionItems={editionItems}
                    onLabelChange={(v) => handleChange("label", v)}
                    onRequiredChange={isChildBlock ? undefined : (v) => handleChange("required", v)}>
            <div className="space-y-3">
                <OptionList
                    options={form.options}
                    onChange={changeOptions}
                    createOption={(optionId) => ({id: optionId, label: "", value: ""})}
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
            </div>
        </FieldInput>
    );
};

export default ChoiceGroupInput;
