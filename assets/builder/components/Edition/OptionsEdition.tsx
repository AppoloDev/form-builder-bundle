import { Input } from "../ui/input";
import { Button } from "../ui/button";
import { Checkbox } from "../ui/checkbox";
import { v4 as uuidv4 } from "uuid";
import { SelectOption } from "../Blocks/Definition";
import { t } from "../../i18n";
import { TrashIcon } from "@phosphor-icons/react";
import { FIELD_CLASSES, FIELD_DESCRIPTION_CLASSES, FIELD_LABEL_CLASSES } from "../../utilities/typography";

type Props = {
    label: string;
    value: SelectOption[];
    helpText?: string;
    /** Choix unique : cocher une option par défaut décoche les autres. */
    exclusive?: boolean;
    onChange: (next: SelectOption[]) => void;
};

export const OptionsEdition = ({ label, value = [], helpText, exclusive = false, onChange }: Props) => {
    const add = () => onChange([...value, { id: uuidv4(), label: t("option.new") }]);
    const update = (idx: number, label: string) => {
        const next = [...value];
        next[idx] = { ...next[idx], label };
        onChange(next);
    };
    const toggleSelected = (idx: number, isSelected: boolean) => {
        onChange(value.map((opt, i) => {
            if (i === idx) return { ...opt, isSelected };
            return exclusive && isSelected ? { ...opt, isSelected: false } : opt;
        }));
    };
    const remove = (idx: number) => {
        const next = value.filter((_, i) => i !== idx);
        onChange(next);
    };

    return (
        <div className={FIELD_CLASSES}>
            <div className="flex items-center justify-between">
                <label className={FIELD_LABEL_CLASSES}>{label}</label>
                <Button type="button" size="sm" variant="secondary" onClick={add}>
                    {t("option.add")}
                </Button>
            </div>

            <div className="space-y-2">
                {value.map((opt, idx) => (
                    <div key={opt.id} className="flex items-center gap-2">
                        <Checkbox
                            checked={opt.isSelected ?? false}
                            onCheckedChange={(checked) => toggleSelected(idx, checked)}
                            aria-label={t("option.preselectAria")}
                            title={t("option.preselectTitle")}
                            className="cursor-pointer"
                        />
                        <Input
                            value={opt.label}
                            onChange={(e) => update(idx, e.target.value)}
                            className="flex-1"
                        />
                        <Button
                            type="button"
                            size="sm"
                            variant="destructive"
                            onClick={() => remove(idx)}
                            aria-label={t("option.deleteAria")}
                        >
                            <TrashIcon size={18}/>
                        </Button>
                    </div>
                ))}
            </div>

            {helpText && (
                <p className={FIELD_DESCRIPTION_CLASSES}>{helpText}</p>
            )}
        </div>
    );
};
