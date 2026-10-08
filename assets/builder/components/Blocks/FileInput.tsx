import { makeInputBlock } from "./GenericInput";

const acceptByType: Record<string, string> = {
    image: "image/*",
    file: "application/pdf",
    both: "image/*,application/pdf",
};

const FileInput = makeInputBlock("file", {
    toInputAttrs: (form) => ({
        accept: acceptByType[form.acceptedFile as string] ?? acceptByType.image,
        multiple: Boolean(form.allowMultiple),
    }),
});

export default FileInput;
