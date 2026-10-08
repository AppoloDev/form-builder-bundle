import { Trash } from "lucide-react";
import { Button } from "../ui/button";
import { Select as SelectField, SelectContent, SelectItem, SelectTrigger, SelectValue } from "../ui/select";
import { AddMenu } from "../AddMenu";
import { Empty } from "../Empty";
import { ChildrenSorter } from "./ChildrenSorter";
import { Block, BlockDefinition, ConditionOperator, ConditionRule } from "./Definition";
import { t } from "../../i18n";

type OptionChoice = { id: string; label: string };

type Props = {
    conditions: ConditionRule[];
    options: OptionChoice[];
    onChangeRuleOption: (ruleId: string, optionId: string) => void;
    onChangeRuleOperator: (ruleId: string, operator: ConditionOperator) => void;
    onRemoveRule: (ruleId: string) => void;
    onAddBlockToRule: (ruleId: string, def: BlockDefinition, overrides?: Record<string, any>) => void;
    onReorderRuleChildren: (ruleId: string, next: Block[]) => void;
};

const operatorOptions = (): { value: ConditionOperator; label: string }[] => [
    {value: "is", label: t("condition.is")},
    {value: "is_not", label: t("condition.isNot")},
];

export const ConditionRules = (
    {
        conditions,
        options,
        onChangeRuleOption,
        onChangeRuleOperator,
        onRemoveRule,
        onAddBlockToRule,
        onReorderRuleChildren,
    }: Props) => {
    if (conditions.length === 0) return null;

    return (
        <div className="space-y-4">
            {conditions.map((rule) => {
                return (
                    <div key={rule.id} className="space-y-3 border-l-4 border-border pl-4">
                        <div className="flex items-center gap-2">
                            <span className="text-sm">Si</span>

                            <SelectField
                                items={options.map((opt) => ({value: opt.id, label: opt.label || " "}))}
                                value={rule.optionId}
                                onValueChange={(v) => onChangeRuleOption(rule.id, v ?? "")}
                            >
                                <SelectTrigger className="h-8 w-auto min-w-40">
                                    <SelectValue placeholder={t("condition.choose")}/>
                                </SelectTrigger>
                                <SelectContent>
                                    {options.map((opt) => (
                                        <SelectItem key={opt.id} value={opt.id}>
                                            {opt.label || " "}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </SelectField>

                            <SelectField
                                items={operatorOptions()}
                                value={rule.operator}
                                onValueChange={(v) => onChangeRuleOperator(rule.id, (v ?? "is") as ConditionOperator)}
                            >
                                <SelectTrigger className="h-8 w-auto min-w-40">
                                    <SelectValue/>
                                </SelectTrigger>
                                <SelectContent>
                                    {operatorOptions().map((op) => (
                                        <SelectItem key={op.value} value={op.value}>
                                            {op.label}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </SelectField>

                            <Button
                                type="button"
                                variant="ghost"
                                size="icon-sm"
                                onClick={() => onRemoveRule(rule.id)}
                                className="ml-auto text-destructive hover:text-destructive"
                                aria-label={t("condition.deleteAria")}
                            >
                                <Trash/>
                            </Button>
                        </div>

                        {rule.children.length === 0 ? (
                            <Empty onPick={(def, overrides) => onAddBlockToRule(rule.id, def, overrides)}/>
                        ) : (
                            <>
                                <ChildrenSorter
                                    childrenBlocks={rule.children}
                                    onReorder={(next) => onReorderRuleChildren(rule.id, next)}
                                />

                                <div className="pt-1">
                                    <AddMenu onPick={(def, overrides) => onAddBlockToRule(rule.id, def, overrides)}>
                                        <Button type="button" size="sm">
                                            {t("builder.addBlock")}
                                        </Button>
                                    </AddMenu>
                                </div>
                            </>
                        )}
                    </div>
                );
            })}
        </div>
    );
};
