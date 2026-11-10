import { UniqueIdentifier } from "@dnd-kit/core";
import {
    type Icon,
    TextTIcon,
    HashIcon,
    EnvelopeSimpleIcon,
    PhoneIcon,
    LinkIcon,
    CalendarDotsIcon,
    MapPinIcon,
    PaperclipIcon,
    ClockIcon,
    TextHIcon,
    RadioButtonIcon,
    CaretDownIcon,
    SignatureIcon,
    StackIcon,
    ArrowsClockwiseIcon,
    AlignLeftIcon,
    TextTSlashIcon, TextAlignLeftIcon,
    AtIcon,
} from "@phosphor-icons/react";
import { onBuilderLocaleChange, t } from "../../i18n";
import type { BlockCategory } from "./Categories";

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
    // Rangement dans le menu d'ajout et restriction par catégorie ; jamais sérialisé dans la structure.
    category: BlockCategory;
    defaultProps: Omit<BlockOf<T>, 'id'>;
    editionSchema?: DefinitionEditionItem[];
    // Shown on the actual field preview (not the label) so visually similar
    // input types — Text/Email/Tel/Url, etc. — stay distinguishable at a glance.
    icon?: Icon;
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
        category: "layout",
        title: t("def.Title.title"),
        icon: TextHIcon,
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
                ]
            },
        ],
    },
    Paragraph: {
        id: "drag-paragraph",
        type: "Paragraph",
        category: "layout",
        title: t("def.Paragraph.title"),
        icon: TextTIcon,
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
        category: "text",
        title: t("def.TextInput.title"),
        icon: TextTIcon,
        description: t("def.TextInput.description"),
        defaultProps: {
            type: "TextInput",
            label: "",
            placeHolder: "",
            required: true,
            helpText: ""
        },
        editionSchema: prefillableInputSchema(),
    },
    TextareaInput: {
        id: "drag-textareainput",
        type: "TextareaInput",
        category: "text",
        title: t("def.TextareaInput.title"),
        icon: TextAlignLeftIcon,
        description: t("def.TextareaInput.description"),
        defaultProps: {
            type: "TextareaInput",
            label: "",
            placeHolder: "",
            required: true,
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
        category: "choice",
        title: t("def.ChoiceGroup.title"),
        icon: RadioButtonIcon,
        description: t("def.ChoiceGroup.description"),
        defaultProps: {
            type: "ChoiceGroup",
            label: "",
            helpText: "",
            required: true,
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
        category: "number",
        title: t("def.NumberInput.title"),
        icon: HashIcon,
        description: t("def.NumberInput.description"),
        defaultProps: {
            type: "NumberInput",
            label: "",
            placeHolder: "",
            required: true,
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
        category: "text",
        title: t("def.EmailInput.title"),
        icon: AtIcon,
        description: t("def.EmailInput.description"),
        defaultProps: {
            type: "EmailInput",
            label: "",
            placeHolder: "",
            required: true,
            helpText: "",
        },
        editionSchema: prefillableInputSchema(),
    },
    TelInput: {
        id: "drag-telinput",
        type: "TelInput",
        category: "text",
        title: t("def.TelInput.title"),
        icon: PhoneIcon,
        description: t("def.TelInput.description"),
        defaultProps: {
            type: "TelInput",
            label: "",
            placeHolder: "",
            required: true,
            helpText: "",
        },
        editionSchema: prefillableInputSchema(),
    },
    UrlInput: {
        id: "drag-urlinput",
        type: "UrlInput",
        category: "text",
        title: t("def.UrlInput.title"),
        icon: LinkIcon,
        description: t("def.UrlInput.description"),
        defaultProps: {
            type: "UrlInput",
            label: "",
            placeHolder: "",
            required: true,
            helpText: "",
        },
        editionSchema: prefillableInputSchema(),
    },
    DateTimeInput: {
        id: "drag-datetimeinput",
        type: "DateTimeInput",
        category: "datetime",
        title: t("def.DateTimeInput.title"),
        icon: CalendarDotsIcon,
        description: t("def.DateTimeInput.description"),
        defaultProps: {
            type: "DateTimeInput",
            label: "",
            required: true,
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
        category: "special",
        title: t("def.AddressInput.title"),
        icon: MapPinIcon,
        description: t("def.AddressInput.description"),
        defaultProps: {
            type: "AddressInput",
            label: "",
            placeHolder: t("field.addressPlaceholder"),
            required: true,
            helpText: "",
        },
        editionSchema: commonInputSchema(),
    },
    FileInput: {
        id: "drag-fileinput",
        type: "FileInput",
        category: "file",
        title: t("def.FileInput.title"),
        icon: PaperclipIcon,
        description: t("def.FileInput.description"),
        defaultProps: {
            type: "FileInput",
            label: "",
            required: true,
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
        category: "datetime",
        title: t("def.HourMinuteInput.title"),
        icon: ClockIcon,
        description: t("def.HourMinuteInput.description"),
        defaultProps: {
            type: "HourMinuteInput",
            label: "",
            required: true,
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
        category: "choice",
        title: t("def.Select.title"),
        icon: CaretDownIcon,
        description: t("def.Select.description"),
        defaultProps: {
            type: "Select",
            label: "",
            helpText: "",
            required: true,
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
        category: "special",
        title: t("def.Signature.title"),
        icon: SignatureIcon,
        description: t("def.Signature.description"),
        defaultProps: {
            type: "Signature",
            label: "",
            helpText: "",
            required: true,
        },
        editionSchema: commonInputSchemaNoPlaceholder(),
    },
    FieldSet: {
        id: "drag-fieldset",
        type: "FieldSet",
        category: "group",
        title: t("def.FieldSet.title"),
        icon: StackIcon,
        description: t("def.FieldSet.description"),
        defaultProps: {
            type: "FieldSet",
            children: [],
        },
    },
    Repeatable: {
        id: "drag-repeatable",
        type: "Repeatable",
        category: "group",
        title: t("def.Repeatable.title"),
        icon: ArrowsClockwiseIcon,
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
