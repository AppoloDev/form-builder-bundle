import { Input } from "../ui/input";
import { Button } from "../ui/button";
import { v4 as uuidv4 } from "uuid";
import { SelectOption } from "../Blocks/Definition";

type Props = {
    label: string;
    value: SelectOption[];
    helpText?: string;
    onChange: (next: SelectOption[]) => void;
};

export const OptionsEdition = ({ label, value = [], helpText, onChange }: Props) => {
    const add = () => onChange([...value, { id: uuidv4(), label: "Nouvelle option" }]);
    const update = (idx: number, label: string) => {
        const next = [...value];
        next[idx] = { ...next[idx], label };
        onChange(next);
    };
    const remove = (idx: number) => {
        const next = value.filter((_, i) => i !== idx);
        onChange(next);
    };

    return (
        <div className="mb-3">
            <div className="flex items-center justify-between mb-1">
                <label className="block text-sm font-medium text-foreground">{label}</label>
                <Button type="button" size="sm" variant="secondary" onClick={add}>
                    + Ajouter
                </Button>
            </div>

            <div className="space-y-2">
                {value.map((opt, idx) => (
                    <div key={opt.id} className="flex items-center gap-2">
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
                            aria-label="Supprimer l'option"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 256 256"><path d="M216,48H176V40a24,24,0,0,0-24-24H104A24,24,0,0,0,80,40v8H40a8,8,0,0,0,0,16h8V208a16,16,0,0,0,16,16H192a16,16,0,0,0,16-16V64h8a8,8,0,0,0,0-16ZM96,40a8,8,0,0,1,8-8h48a8,8,0,0,1,8,8v8H96Zm96,168H64V64H192ZM112,104v64a8,8,0,0,1-16,0V104a8,8,0,0,1,16,0Zm48,0v64a8,8,0,0,1-16,0V104a8,8,0,0,1,16,0Z"></path></svg>
                        </Button>
                    </div>
                ))}
            </div>

            {helpText && (
                <p className="mt-1 text-xs text-muted-foreground italic">{helpText}</p>
            )}
        </div>
    );
};
