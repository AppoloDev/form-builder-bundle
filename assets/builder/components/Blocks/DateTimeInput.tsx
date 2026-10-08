import { EditionItem, makeInputBlock } from "./GenericInput";

const dateTimeExtras: EditionItem[] = [
    {
        key: "mode",
        label: "Type de saisie",
        type: "select",
        options: [
            {value: "date", label: "Date"},
            {value: "datetime-local", label: "Date et heure"},
            {value: "time", label: "Heure"},
        ],
    },
];

const allowedModes = ["date", "datetime-local", "time"];

const DateTimeInput = makeInputBlock("date", {
    extraSchema: dateTimeExtras,
    toInputAttrs: (form) => {
        const mode = typeof form.mode === "string" && allowedModes.includes(form.mode)
            ? form.mode
            : "datetime-local";

        return {type: mode};
    },
});

export default DateTimeInput;
