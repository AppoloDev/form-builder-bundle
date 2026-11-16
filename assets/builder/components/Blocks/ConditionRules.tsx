import { useContext } from "react";
import { TrashIcon } from "@phosphor-icons/react";
import { Button } from "../ui/button";
import { Select as SelectField, SelectContent, SelectItem, SelectTrigger, SelectValue } from "../ui/select";
import { AddElementButton } from "../AddElementButton";
import { ChildrenSorter, DropZone } from "./ChildrenSorter";
import { ACTIONS_GAP, ActionsInsetContext } from "./EditableBlock";
import { BlockDefinition, ConditionOperator, ConditionRule } from "./Definition";
import { t } from "../../i18n";

type OptionChoice = { id: string; label: string };

type Props = {
    conditions: ConditionRule[];
    options: OptionChoice[];
    onChangeRuleOption: (ruleId: string, optionId: string) => void;
    onChangeRuleOperator: (ruleId: string, operator: ConditionOperator) => void;
    onRemoveRule: (ruleId: string) => void;
    onAddBlockToRule: (ruleId: string, def: BlockDefinition) => void;
};

/** Retrait du contenu d'une condition : bordure (4) + pl-4 (16). */
const CONDITION_INSET = 20;

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
    }: Props) => {
    const inset = useContext(ActionsInsetContext);
    if (conditions.length === 0) return null;

    return (
        <div className="space-y-4">
            {conditions.map((rule) => {
                return (
                    // Bordure alignée sur le libellé du bloc, comme les options.
                    <div key={rule.id} className="min-w-0 space-y-3 border-l-4 border-border py-4 pl-4">
                        {/* En-tête (« Quand » + listes) : son survol seul révèle la corbeille, calée à droite de la
                            colonne d'actions (sous la poignée du bloc) : bordure + pl-4 (20) + écart (8).
                            En padding, pour garder le survol entre la corbeille et l'en-tête. */}
                        <div data-row-actions="" className="group/rule relative space-y-3">
                            <div style={{paddingRight: CONDITION_INSET + ACTIONS_GAP + inset}} className="absolute top-0 right-full opacity-0 transition-opacity group-hover/rule:opacity-100 has-focus-visible:opacity-100">
                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="icon-sm"
                                    onClick={() => onRemoveRule(rule.id)}
                                    className="text-destructive hover:text-destructive"
                                    aria-label={t("condition.deleteAria")}
                                >
                                    <TrashIcon/>
                                </Button>
                            </div>
                            <p className="text-xs uppercase text-muted-foreground">{t("condition.when")}</p>
                            <div className="flex flex-wrap items-center gap-2">

                                <SelectField
                                    items={options.map((opt) => ({value: opt.id, label: opt.label || " "}))}
                                    value={rule.optionId || null}
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
                                    value={rule.operator || null}
                                    onValueChange={(v) => onChangeRuleOperator(rule.id, (v ?? "is") as ConditionOperator)}
                                >
                                    <SelectTrigger className="h-8 w-auto min-w-40">
                                        <SelectValue placeholder={t("condition.chooseOperator")}/>
                                    </SelectTrigger>
                                    <SelectContent>
                                        {operatorOptions().map((op) => (
                                            <SelectItem key={op.value} value={op.value}>
                                                {op.label}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </SelectField>
                            </div>
                        </div>

                        <p className="text-xs uppercase text-muted-foreground">{t("condition.then")}</p>

                        <ActionsInsetContext.Provider value={inset + CONDITION_INSET}>
                        <DropZone containerId={rule.id} className="space-y-3 rounded-lg">
                            {rule.children.length > 0 && (
                                <ChildrenSorter
                                    childrenBlocks={rule.children}
                                    containerId={rule.id}
                                    conditional
                                />
                            )}

                            <AddElementButton onPick={(def) => onAddBlockToRule(rule.id, def)}/>
                        </DropZone>
                        </ActionsInsetContext.Provider>
                    </div>
                );
            })}
        </div>
    );
};
