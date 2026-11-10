import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "../ui/select";
import { FIELD_CLASSES, FIELD_DESCRIPTION_CLASSES, FIELD_LABEL_CLASSES } from "../../utilities/typography";

type Option = { value: string; label: string };

type Props = {
    label: string;
    value: string;
    options: Option[];
    helpText?: string;
    editItem: (value: string) => void;
};

export const SelectEdition = ({label, value, options, helpText, editItem}: Props) => {
    return (
        <div className={FIELD_CLASSES}>
            <label className={FIELD_LABEL_CLASSES}>{label}</label>
            <Select items={options} value={value ?? ""} onValueChange={(v) => editItem(v ?? "")}>
                <SelectTrigger className="w-full">
                    <SelectValue/>
                </SelectTrigger>
                <SelectContent>
                    {options.map(opt => (
                        <SelectItem key={opt.value} value={opt.value}>{opt.label}</SelectItem>
                    ))}
                </SelectContent>
            </Select>
            {helpText && <p className={FIELD_DESCRIPTION_CLASSES}>{helpText}</p>}
        </div>
    );
};
