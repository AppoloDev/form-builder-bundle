import { makeInputBlock } from "./GenericInput";

const NumberInput = makeInputBlock("number", {
    toInputAttrs: (form) => ({
        min: form.min,
        max: form.max,
        step: form.step,
    }),
});

export default NumberInput;
