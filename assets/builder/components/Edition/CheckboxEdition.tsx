import { Switch } from "../ui/switch";
import { FIELD_LABEL_CLASSES } from "../../utilities/typography";

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
            <span className={FIELD_LABEL_CLASSES}>{label}</span>
            <Switch
                checked={checked}
                onCheckedChange={editItem}
                onClick={(e) => e.stopPropagation()}
            />
        </div>
    );
}
