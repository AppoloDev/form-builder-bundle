import { EditionItem, makeInputBlock } from "./GenericInput";

const fileExtras: EditionItem[] = [
    {
        key: "acceptedFile",
        label: "Fichiers acceptés",
        type: "select",
        options: [
            {value: "image", label: "Images"},
            {value: "file", label: "PDF"},
            {value: "both", label: "Images et PDF"},
        ],
    },
    {key: "allowMultiple", label: "Autoriser plusieurs fichiers", type: "checkbox"},
];

const acceptByType: Record<string, string> = {
    image: "image/*",
    file: "application/pdf",
    both: "image/*,application/pdf",
};

const FileInput = makeInputBlock("file", {
    extraSchema: fileExtras,
    toInputAttrs: (form) => ({
        accept: acceptByType[form.acceptedFile as string] ?? acceptByType.image,
        multiple: Boolean(form.allowMultiple),
    }),
});

export default FileInput;
