import { Switch } from "../ui/switch";

type Props = {
    label: string;
    checked: boolean;
    editItem: (editable: boolean) => void;
}

export const CheckboxEdition = ({label, checked, editItem}: Props) => {
    return (
        <div
            className="flex items-center cursor-pointer justify-between"
            onClick={() => editItem(!checked)}
        >
            <span className="text-sm font-medium text-foreground">{label}</span>
            <Switch
                checked={checked}
                onCheckedChange={editItem}
                onClick={(e) => e.stopPropagation()}
            />
        </div>
    );
}
