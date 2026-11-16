import { useState } from "react";
import { CheckboxEdition } from "./CheckboxEdition";
import { TextEdition } from "./TextEdition";
import { t } from "../../i18n";

type Props = {
    label: string;
    yesLabel: string;
    noLabel: string;
    editItem: (key: "yesLabel" | "noLabel", value: string) => void;
};

/** Switch « valeur par défaut » (Oui / Non) ; décoché, les deux libellés deviennent modifiables. Le recocher les rétablit. */
export const YesNoLabelsEdition = ({label, yesLabel, noLabel, editItem}: Props) => {
    const defaults = {yesLabel: t("yesNo.yes"), noLabel: t("yesNo.no")};
    const [useDefault, setUseDefault] = useState(yesLabel === defaults.yesLabel && noLabel === defaults.noLabel);

    const toggle = (on: boolean) => {
        setUseDefault(on);
        if (on) {
            editItem("yesLabel", defaults.yesLabel);
            editItem("noLabel", defaults.noLabel);
        }
    };

    return (
        <>
            <CheckboxEdition label={label} checked={useDefault} editItem={toggle}/>
            {!useDefault && (
                <>
                    <TextEdition label="" ariaLabel={t("yesNo.yesLabel")} value={yesLabel} editItem={(v) => editItem("yesLabel", v)}/>
                    <TextEdition label="" ariaLabel={t("yesNo.noLabel")} value={noLabel} editItem={(v) => editItem("noLabel", v)}/>
                </>
            )}
        </>
    );
};
