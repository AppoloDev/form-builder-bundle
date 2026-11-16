import { MinusIcon, PlusIcon } from "@phosphor-icons/react";
import { CheckboxEdition } from "./CheckboxEdition";
import { Button } from "../ui/button";
import { FIELD_CLASSES, FIELD_LABEL_CLASSES } from "../../utilities/typography";
import { t } from "../../i18n";

type Props = {
    unlimitedLabel: string;
    label: string;
    /** 0 (ou absent) = illimité. */
    value: number | undefined;
    /** Plus petit maximum autorisé, valeur prise en quittant « illimité ». */
    min: number;
    editItem: (value: number) => void;
};

/** Switch « illimité », puis stepper − N + (modèle reui c-button-group-37) quand il est désactivé, jamais sous `min`. */
export const MaxItemsEdition = ({unlimitedLabel, label, value, min, editItem}: Props) => {
    const current = value || 0;

    return (
        <>
            <CheckboxEdition label={unlimitedLabel} checked={current === 0} editItem={(on) => editItem(on ? 0 : min)}/>
            {current > 0 && (
                <div className={FIELD_CLASSES}>
                    <span className={FIELD_LABEL_CLASSES}>{label}</span>
                    <div role="group" className="flex self-start [&>*:not(:first-child)]:rounded-l-none [&>*:not(:last-child)]:rounded-r-none [&>*:not(:first-child)]:border-l-0">
                        <Button
                            type="button"
                            variant="outline"
                            size="icon"
                            aria-label={t("edition.decrease")}
                            disabled={current <= min}
                            onClick={() => editItem(Math.max(min, current - 1))}
                        >
                            <MinusIcon aria-hidden="true"/>
                        </Button>
                        <span className="flex w-12 items-center justify-center rounded-lg border border-border bg-muted text-sm font-medium tabular-nums">
                            {current}
                        </span>
                        <Button
                            type="button"
                            variant="outline"
                            size="icon"
                            aria-label={t("edition.increase")}
                            onClick={() => editItem(current + 1)}
                        >
                            <PlusIcon aria-hidden="true"/>
                        </Button>
                    </div>
                </div>
            )}
        </>
    );
};
