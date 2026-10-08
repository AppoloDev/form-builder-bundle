import { makeInputBlock } from "./GenericInput";

const allowedModes = ["date", "datetime-local", "time"];

const DateTimeInput = makeInputBlock("date", {
    toInputAttrs: (form) => {
        const mode = typeof form.mode === "string" && allowedModes.includes(form.mode)
            ? form.mode
            : "datetime-local";

        return {type: mode};
    },
});

export default DateTimeInput;
