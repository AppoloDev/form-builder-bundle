import { UniqueIdentifier } from "@dnd-kit/core";
import {
    type LucideIcon,
    Type as TypeIcon,
    AlignLeft,
    Hash,
    Mail,
    Phone,
    Link,
    Calendar,
    MapPin,
    Paperclip,
    Clock,
} from "lucide-react";
import { onBuilderLocaleChange, t } from "../../i18n";

export type BlockId = UniqueIdentifier;

export interface BaseBlockProps {
    id: BlockId;
    type: BlockType;
}

export interface TextInputProps extends BaseBlockProps {
    type: 'TextInput';
    label: string;
    placeHolder?: string;
    helpText?: string;
    required?: boolean;
    readOnly?: boolean;
    defaultValue?: string;
}


export interface TextareaInputProps extends BaseBlockProps {
    type: 'TextareaInput';
    label: string;
    placeHolder?: string;
    required?: boolean;
    helpText?: string;
    readOnly?: boolean;
    defaultValue?: string;
    rows?: number;
}

export interface TitleProps extends BaseBlockProps {
    type: 'Title';
    text: string;
    heading: string;
}

export interface ParagraphProps extends BaseBlockProps {
    type: 'Paragraph';
    text: string;
}

export interface ChoiceGroupProps extends BaseBlockProps {
    type: 'ChoiceGroup';
    label: string;
    helpText?: string;
    required?: boolean;
    readOnly?: boolean;
    multiple?: boolean;
    options: OptionItem[];
    conditions?: ConditionRule[];
}

export interface NumberInputProps extends BaseBlockProps {
    type: 'NumberInput';
    label: string;
    placeHolder?: string;
    helpText?: string;
    required?: boolean;
    readOnly?: boolean;
    defaultValue?: string;
    min?: string;
    max?: string;
    step?: string;
}

export interface EmailInputProps extends BaseBlockProps {
    type: 'EmailInput';
    label: string;
    placeHolder?: string;
    helpText?: string;
    required?: boolean;
    readOnly?: boolean;
    defaultValue?: string;
}

export interface TelInputProps extends BaseBlockProps {
    type: 'TelInput';
    label: string;
    placeHolder?: string;
    helpText?: string;
    required?: boolean;
    readOnly?: boolean;
    defaultValue?: string;
}

export interface UrlInputProps extends BaseBlockProps {
    type: 'UrlInput';
    label: string;
    placeHolder?: string;
    helpText?: string;
    required?: boolean;
    readOnly?: boolean;
    defaultValue?: string;
}

export interface DateTimeInputProps extends BaseBlockProps {
    type: 'DateTimeInput';
    label: string;
    helpText?: string;
    required?: boolean;
    readOnly?: boolean;
    hasCurrentDate?: boolean;
    mode?: string;
}

export interface AddressInputProps extends BaseBlockProps {
    type: 'AddressInput';
    label: string;
    placeHolder?: string;
    helpText?: string;
    required?: boolean;
}

export interface FileInputProps extends BaseBlockProps {
    type: 'FileInput';
    label: string;
    helpText?: string;
    required?: boolean;
    acceptedFile?: string;
    allowMultiple?: boolean;
}

export interface HourMinuteInputProps extends BaseBlockProps {
    type: 'HourMinuteInput';
    label: string;
    helpText?: string;
    required?: boolean;
    readOnly?: boolean;
    defaultValue?: string;
}

export interface SelectProps extends BaseBlockProps {
    type: 'Select';
    label: string;
    helpText?: string;
    required?: boolean;
    readOnly?: boolean;
    multiple?: boolean;
    customOption?: boolean;
    options: SelectOption[];
    conditions?: ConditionRule[];
}

export interface SignatureProps extends BaseBlockProps {
    type: 'Signature';
    label: string;
    helpText?: string;
    required?: boolean;
}

export interface FieldSetProps extends BaseBlockProps {
    type: 'FieldSet';
    children: Block[];
}

export interface RepeatableProps extends BaseBlockProps {
    type: 'Repeatable';
    children: Block[];
    maxItems?: number;
}

export type OptionItem = {
    id: string;
    label: string;
    value: string;
};

export type SelectOption = {
    id: string;
    label: string;
    isSelected?: boolean;
};

export type ConditionOperator = 'is' | 'is_not';

export type ConditionRule = {
    id: string;
    operator: ConditionOperator;
    optionId: string;
    children: Block[];
};

export type BlockPropsByType = {
    Title: TitleProps;
    Paragraph: ParagraphProps;
    TextInput: TextInputProps;
    TextareaInput: TextareaInputProps;
    ChoiceGroup: ChoiceGroupProps;
    NumberInput: NumberInputProps;
    EmailInput: EmailInputProps;
    TelInput: TelInputProps;
    UrlInput: UrlInputProps;
    DateTimeInput: DateTimeInputProps;
    AddressInput: AddressInputProps;
    FileInput: FileInputProps;
    HourMinuteInput: HourMinuteInputProps;
    Select: SelectProps;
    Signature: SignatureProps;
    FieldSet: FieldSetProps;
    Repeatable: RepeatableProps;
};

export type BlockType = keyof BlockPropsByType;

export type Block = BlockPropsByType[BlockType];

type BlockOf<T extends BlockType> = BlockPropsByType[T];

// Edition schema item types for block configuration
export type DefinitionEditionItem =
    | { key: string; label: string; type: "text" | "textarea" | "number"; helpText?: string; rows?: number }
    | { key: string; label: string; type: "checkbox" }
    | { key: string; label: string; type: "select"; options: { value: string; label: string }[]; helpText?: string };

export interface BlockDefinition<T extends BlockType = BlockType> {
    id: string;
    type: T;
    title: string;
    description: string;
    defaultProps: Omit<BlockOf<T>, 'id'>;
    editionSchema?: DefinitionEditionItem[];
    // Shown on the actual field preview (not the label) so visually similar
    // input types — Text/Email/Tel/Url, etc. — stay distinguishable at a glance.
    icon?: LucideIcon;
}

type BlockDefinitions = { [T in BlockType]: BlockDefinition<T> };

// Common edition fields shared by most input blocks
const commonInputSchema = (): DefinitionEditionItem[] => [
    {key: "label", label: t("field.title"), type: "text"},
    {key: "placeHolder", label: t("field.placeholder"), type: "text"},
    {key: "helpText", label: t("field.helpText"), type: "textarea", rows: 2},
    {key: "required", label: t("field.required"), type: "checkbox"},
];

const readOnlyItem = (): DefinitionEditionItem => ({key: "readOnly", label: t("field.readOnly"), type: "checkbox"});

// Text-like inputs that can be prefilled and locked
const prefillableInputSchema = (): DefinitionEditionItem[] => [
    ...commonInputSchema(),
    {key: "defaultValue", label: t("field.defaultValue"), type: "text"},
    readOnlyItem(),
];

// Input blocks without a placeholder field
const commonInputSchemaNoPlaceholder = (): DefinitionEditionItem[] => [
    {key: "label", label: t("field.title"), type: "text"},
    {key: "helpText", label: t("field.helpText"), type: "textarea", rows: 2},
    {key: "required", label: t("field.required"), type: "checkbox"},
];

const buildDefinitions = (): BlockDefinitions => ({
    Title: {
        id: "drag-title",
        type: "Title",
        title: t("def.Title.title"),
        description: t("def.Title.description"),
        defaultProps: {
            type: "Title",
            text: t("title.default"),
            heading: 'h1'
        },
        editionSchema: [
            {key: "text", label: t("title.text"), type: "text"},
            {
                key: "heading", label: t("title.level"), type: "select", options: [
                    {value: "h1", label: t("title.levelN", {n: 1})},
                    {value: "h2", label: t("title.levelN", {n: 2})},
                    {value: "h3", label: t("title.levelN", {n: 3})},
                    {value: "h4", label: t("title.levelN", {n: 4})},
                    {value: "h5", label: t("title.levelN", {n: 5})},
                    {value: "h6", label: t("title.levelN", {n: 6})},
                ]
            },
        ],
    },
    Paragraph: {
        id: "drag-paragraph",
        type: "Paragraph",
        title: t("def.Paragraph.title"),
        description: t("def.Paragraph.description"),
        defaultProps: {
            type: "Paragraph",
            text: "Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua."
        },
        editionSchema: [
            {key: "text", label: t("paragraph.text"), type: "textarea", rows: 3},
        ],
    },
    TextInput: {
        id: "drag-textinput",
        type: "TextInput",
        title: t("def.TextInput.title"),
        icon: TypeIcon,
        description: t("def.TextInput.description"),
        defaultProps: {
            type: "TextInput",
            label: t("field.label"),
            placeHolder: "",
            required: false,
            helpText: ""
        },
        editionSchema: prefillableInputSchema(),
    },
    TextareaInput: {
        id: "drag-textareainput",
        type: "TextareaInput",
        title: t("def.TextareaInput.title"),
        icon: AlignLeft,
        description: t("def.TextareaInput.description"),
        defaultProps: {
            type: "TextareaInput",
            label: t("field.label"),
            placeHolder: "",
            required: false,
            helpText: '',
            rows: 5
        },
        editionSchema: [
            ...prefillableInputSchema(),
            {key: "rows", label: t("textarea.rows"), type: "number"},
        ],
    },
    ChoiceGroup: {
        id: "drag-choicegroup",
        type: "ChoiceGroup",
        title: t("def.ChoiceGroup.title"),
        description: t("def.ChoiceGroup.description"),
        defaultProps: {
            type: "ChoiceGroup",
            label: t("field.label"),
            helpText: "",
            required: false,
            multiple: false,
            options: [],
            conditions: [],
        },
        editionSchema: [
            {key: "label", label: t("field.title"), type: "text"},
            {key: "helpText", label: t("field.helpText"), type: "textarea", rows: 2},
            {key: "required", label: t("field.required"), type: "checkbox"},
            {key: "multiple", label: t("field.multiple"), type: "checkbox"},
            readOnlyItem(),
        ],
    },
    NumberInput: {
        id: "drag-numberinput",
        type: "NumberInput",
        title: t("def.NumberInput.title"),
        icon: Hash,
        description: t("def.NumberInput.description"),
        defaultProps: {
            type: "NumberInput",
            label: t("field.label"),
            placeHolder: "",
            required: false,
            helpText: "",
            min: "",
            max: "",
            step: "",
        },
        editionSchema: [
            ...commonInputSchema(),
            {key: "defaultValue", label: t("field.defaultValue"), type: "number"},
            readOnlyItem(),
            {key: "min", label: t("number.min"), type: "number"},
            {key: "max", label: t("number.max"), type: "number"},
            {key: "step", label: t("number.step"), type: "number"},
        ],
    },
    EmailInput: {
        id: "drag-emailinput",
        type: "EmailInput",
        title: t("def.EmailInput.title"),
        icon: Mail,
        description: t("def.EmailInput.description"),
        defaultProps: {
            type: "EmailInput",
            label: t("field.label"),
            placeHolder: "",
            required: false,
            helpText: "",
        },
        editionSchema: prefillableInputSchema(),
    },
    TelInput: {
        id: "drag-telinput",
        type: "TelInput",
        title: t("def.TelInput.title"),
        icon: Phone,
        description: t("def.TelInput.description"),
        defaultProps: {
            type: "TelInput",
            label: t("field.label"),
            placeHolder: "",
            required: false,
            helpText: "",
        },
        editionSchema: prefillableInputSchema(),
    },
    UrlInput: {
        id: "drag-urlinput",
        type: "UrlInput",
        title: t("def.UrlInput.title"),
        icon: Link,
        description: t("def.UrlInput.description"),
        defaultProps: {
            type: "UrlInput",
            label: t("field.label"),
            placeHolder: "",
            required: false,
            helpText: "",
        },
        editionSchema: prefillableInputSchema(),
    },
    DateTimeInput: {
        id: "drag-datetimeinput",
        type: "DateTimeInput",
        title: t("def.DateTimeInput.title"),
        icon: Calendar,
        description: t("def.DateTimeInput.description"),
        defaultProps: {
            type: "DateTimeInput",
            label: t("field.label"),
            required: false,
            helpText: "",
            mode: "datetime-local",
        },
        editionSchema: [
            ...commonInputSchemaNoPlaceholder(),
            {
                key: "mode", label: t("date.mode"), type: "select", options: [
                    {value: "date", label: t("date.modeDate")},
                    {value: "datetime-local", label: t("date.modeDateTime")},
                    {value: "time", label: t("date.modeTime")},
                ]
            },
            {key: "hasCurrentDate", label: t("date.prefill"), type: "checkbox"},
            readOnlyItem(),
        ],
    },
    AddressInput: {
        id: "drag-addressinput",
        type: "AddressInput",
        title: t("def.AddressInput.title"),
        icon: MapPin,
        description: t("def.AddressInput.description"),
        defaultProps: {
            type: "AddressInput",
            label: t("field.label"),
            placeHolder: t("field.addressPlaceholder"),
            required: false,
            helpText: "",
        },
        editionSchema: commonInputSchema(),
    },
    FileInput: {
        id: "drag-fileinput",
        type: "FileInput",
        title: t("def.FileInput.title"),
        icon: Paperclip,
        description: t("def.FileInput.description"),
        defaultProps: {
            type: "FileInput",
            label: t("field.label"),
            required: false,
            helpText: "",
            acceptedFile: "image",
            allowMultiple: false,
        },
        editionSchema: [
            ...commonInputSchemaNoPlaceholder(),
            {
                key: "acceptedFile", label: t("file.accepted"), type: "select", options: [
                    {value: "image", label: t("file.images")},
                    {value: "file", label: t("file.pdf")},
                    {value: "both", label: t("file.both")},
                ]
            },
            {key: "allowMultiple", label: t("file.multiple"), type: "checkbox"},
        ],
    },
    HourMinuteInput: {
        id: "drag-hourminuteinput",
        type: "HourMinuteInput",
        title: t("def.HourMinuteInput.title"),
        icon: Clock,
        description: t("def.HourMinuteInput.description"),
        defaultProps: {
            type: "HourMinuteInput",
            label: t("field.label"),
            required: false,
            helpText: "",
        },
        editionSchema: [
            ...commonInputSchemaNoPlaceholder(),
            {key: "defaultValue", label: t("field.defaultValue"), type: "text", helpText: t("hourMinute.help")},
            readOnlyItem(),
        ],
    },
    Select: {
        id: "drag-select",
        type: "Select",
        title: t("def.Select.title"),
        description: t("def.Select.description"),
        defaultProps: {
            type: "Select",
            label: t("field.label"),
            helpText: "",
            required: false,
            multiple: false,
            options: [],
            conditions: [],
        },
        editionSchema: [
            {key: "label", label: t("field.title"), type: "text"},
            {key: "helpText", label: t("field.helpText"), type: "textarea", rows: 2},
            {key: "required", label: t("field.required"), type: "checkbox"},
            {key: "multiple", label: t("field.multiple"), type: "checkbox"},
            {key: "customOption", label: t("select.customOption"), type: "checkbox"},
            readOnlyItem(),
        ],
    },
    Signature: {
        id: "drag-signature",
        type: "Signature",
        title: t("def.Signature.title"),
        description: t("def.Signature.description"),
        defaultProps: {
            type: "Signature",
            label: t("field.label"),
            helpText: "",
            required: false,
        },
        editionSchema: commonInputSchemaNoPlaceholder(),
    },
    FieldSet: {
        id: "drag-fieldset",
        type: "FieldSet",
        title: t("def.FieldSet.title"),
        description: t("def.FieldSet.description"),
        defaultProps: {
            type: "FieldSet",
            children: [],
        },
    },
    Repeatable: {
        id: "drag-repeatable",
        type: "Repeatable",
        title: t("def.Repeatable.title"),
        description: t("def.Repeatable.description"),
        defaultProps: {
            type: "Repeatable",
            children: [],
            maxItems: 1,
        },
    }
});

export const blockDefinitions: BlockDefinitions = buildDefinitions();

// Les définitions portent des textes traduits : on les reconstruit en place quand la langue change.
onBuilderLocaleChange(() => Object.assign(blockDefinitions, buildDefinitions()));

export const getAllBlockDefinitions = () => Object.values(blockDefinitions);
