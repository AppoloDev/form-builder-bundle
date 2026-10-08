import { EditionItem, makeInputBlock } from "./GenericInput";

const numberExtras: EditionItem[] = [
    {key: "min", label: "Valeur minimale", type: "number"},
    {key: "max", label: "Valeur maximale", type: "number"},
    {key: "step", label: "Pas", type: "number"},
];

const NumberInput = makeInputBlock("number", {
    extraSchema: numberExtras,
    toInputAttrs: (form) => ({
        min: form.min,
        max: form.max,
        step: form.step,
    }),
});

export default NumberInput;
