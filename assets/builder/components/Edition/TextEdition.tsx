import { ChangeEvent } from "react";
import { Input } from "../ui/input";
import { Textarea } from "../ui/textarea";
import { FIELD_CLASSES, FIELD_DESCRIPTION_CLASSES, FIELD_LABEL_CLASSES } from "../../utilities/typography";

type Props = {
    label: string;
    value: string;
    type?: "text" | "textarea" | "number";
    helpText?: string;
    rows?: number;
    editItem: (text: string) => void
}

export const TextEdition = ({label, value, helpText, editItem, type = 'text', rows = 3}: Props) => {
    const handleChange = (e: ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
        editItem(e.target.value);
    };

    return (
        <div className={FIELD_CLASSES}>
            <label className={FIELD_LABEL_CLASSES}>
                {label}
            </label>

            {type === "textarea" ? (
                <Textarea
                    value={value}
                    onChange={handleChange}
                    rows={rows}
                />
            ) : (
                <Input
                    type={type}
                    value={value}
                    onChange={handleChange}
                />
            )}

            {helpText && (
                <p className={FIELD_DESCRIPTION_CLASSES}>{helpText}</p>
            )}
        </div>
    );
};
