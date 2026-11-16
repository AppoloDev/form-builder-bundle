import { makeInputBlock } from "./GenericInput";

const allowedModes = ["date", "datetime-local", "time"];

/** Maintenant, au format de l'input natif du mode (date : AAAA-MM-JJ, heure : HH:MM). */
const now = (mode: string): string => {
    const local = new Date(Date.now() - new Date().getTimezoneOffset() * 60_000).toISOString();

    return mode === "date" ? local.slice(0, 10) : mode === "time" ? local.slice(11, 16) : local.slice(0, 16);
};

const DateTimeInput = makeInputBlock("date", {
    toInputAttrs: (form) => {
        const mode = typeof form.mode === "string" && allowedModes.includes(form.mode)
            ? form.mode
            : "datetime-local";

        // « Pré-remplir avec la date du jour » : la valeur par défaut est affichée, comme dans le formulaire.
        return form.hasCurrentDate ? {type: mode, value: now(mode)} : {type: mode};
    },
});

export default DateTimeInput;
