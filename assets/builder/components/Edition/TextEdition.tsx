import { ChangeEvent, useState } from "react";
import { Switch } from "../ui/switch";
import { completeMask, formatMask, type MaskType } from "../../../lib/masks";
import { Input } from "../ui/input";
import { Textarea } from "../ui/textarea";
import { FIELD_CLASSES, FIELD_DESCRIPTION_CLASSES, FIELD_LABEL_CLASSES } from "../../utilities/typography";

type Props = {
    label: string;
    value: string;
    type?: "text" | "textarea" | "number";
    helpText?: string;
    rows?: number;
    /** Même masque que le champ du formulaire (téléphone, e-mail, URL). */
    mask?: MaskType;
    /** Option facultative : un switch l'active ; le désactiver vide la valeur. */
    optional?: boolean;
    /** Libellé lu par les lecteurs d'écran quand `label` est vide (pas de libellé affiché). */
    ariaLabel?: string;
    editItem: (text: string) => void
}

export const TextEdition = ({label, value, helpText, editItem, type = 'text', rows = 3, mask, optional = false, ariaLabel}: Props) => {
    const [enabled, setEnabled] = useState(!optional || String(value ?? "") !== "");

    const handleChange = (e: ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
        editItem(mask ? formatMask(mask, e.target.value) : e.target.value);
    };

    const toggle = (on: boolean) => {
        setEnabled(on);
        if (!on) editItem("");
    };

    return (
        <div className={FIELD_CLASSES}>
            {optional ? (
                <div
                    className="flex items-center cursor-pointer justify-between"
                    onClick={() => toggle(!enabled)}
                >
                    <span className={FIELD_LABEL_CLASSES}>{label}</span>
                    <Switch checked={enabled} onCheckedChange={toggle} onClick={(e) => e.stopPropagation()}/>
                </div>
            ) : label && (
                <label className={FIELD_LABEL_CLASSES}>
                    {label}
                </label>
            )}

            {!enabled ? null : type === "textarea" ? (
                <Textarea
                    value={value}
                    onChange={handleChange}
                    rows={rows}
                    aria-label={ariaLabel}
                />
            ) : (
                <Input
                    type={type}
                    value={value}
                    aria-label={ariaLabel}
                    onChange={handleChange}
                    onBlur={mask ? () => editItem(completeMask(mask, value)) : undefined}
                />
            )}

            {enabled && helpText && (
                <p className={FIELD_DESCRIPTION_CLASSES}>{helpText}</p>
            )}
        </div>
    );
};
