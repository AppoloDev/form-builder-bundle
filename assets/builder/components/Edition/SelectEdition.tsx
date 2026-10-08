import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "../ui/select";

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
        <div className="mb-3">
            <label className="block mb-2 text-sm font-medium text-foreground">{label}</label>
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
            {helpText && <p className="mt-1 text-xs text-muted-foreground italic">{helpText}</p>}
        </div>
    );
};
