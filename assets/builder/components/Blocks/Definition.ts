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
const commonInputSchema: DefinitionEditionItem[] = [
    {key: "label", label: "Titre", type: "text"},
    {key: "placeHolder", label: "Placeholder", type: "text"},
    {key: "helpText", label: "Message d'aide", type: "textarea", rows: 2},
    {key: "required", label: "Requis", type: "checkbox"},
];

const readOnlyItem: DefinitionEditionItem = {key: "readOnly", label: "Lecture seule", type: "checkbox"};

// Text-like inputs that can be prefilled and locked
const prefillableInputSchema: DefinitionEditionItem[] = [
    ...commonInputSchema,
    {key: "defaultValue", label: "Valeur par défaut", type: "text"},
    readOnlyItem,
];

// Input blocks without a placeholder field
const commonInputSchemaNoPlaceholder: DefinitionEditionItem[] = [
    {key: "label", label: "Titre", type: "text"},
    {key: "helpText", label: "Message d'aide", type: "textarea", rows: 2},
    {key: "required", label: "Requis", type: "checkbox"},
];

export const blockDefinitions: BlockDefinitions = {
    Title: {
        id: "drag-title",
        type: "Title",
        title: "Titre",
        description: 'Insertion d\'un titre informatif ne nécessitant pas de réponse de l\'utilisateur.',
        defaultProps: {
            type: "Title",
            text: "Titre",
            heading: 'h1'
        },
        editionSchema: [
            {key: "text", label: "Texte du titre", type: "text"},
            {
                key: "heading", label: "Niveau de titre", type: "select", options: [
                    {value: "h1", label: "Titre de niveau 1"},
                    {value: "h2", label: "Titre de niveau 2"},
                    {value: "h3", label: "Titre de niveau 3"},
                    {value: "h4", label: "Titre de niveau 4"},
                    {value: "h5", label: "Titre de niveau 5"},
                    {value: "h6", label: "Titre de niveau 6"},
                ]
            },
        ],
    },
    Paragraph: {
        id: "drag-paragraph",
        type: "Paragraph",
        title: "Paragraphe",
        description: 'Insertion d\'un paragraphe informatif ne nécessitant pas de réponse de l\'utilisateur.',
        defaultProps: {
            type: "Paragraph",
            text: "Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua."
        },
        editionSchema: [
            {key: "text", label: "Texte du paragraphe", type: "textarea", rows: 3},
        ],
    },
    TextInput: {
        id: "drag-textinput",
        type: "TextInput",
        title: "Texte court",
        icon: TypeIcon,
        description: 'Permet la saisie d\'un texte court.',
        defaultProps: {
            type: "TextInput",
            label: "Libellé",
            placeHolder: "",
            required: false,
            helpText: ""
        },
        editionSchema: prefillableInputSchema,
    },
    TextareaInput: {
        id: "drag-textareainput",
        type: "TextareaInput",
        title: "Zone de texte",
        icon: AlignLeft,
        description: 'Saisie d\'une grande quantité de texte par l\'utilisateur.',
        defaultProps: {
            type: "TextareaInput",
            label: "Libellé",
            placeHolder: "",
            required: false,
            helpText: '',
            rows: 5
        },
        editionSchema: [
            ...prefillableInputSchema,
            {key: "rows", label: "Nombre de lignes", type: "number"},
        ],
    },
    ChoiceGroup: {
        id: "drag-choicegroup",
        type: "ChoiceGroup",
        title: "Choix (radio / cases)",
        description: 'Affiche des boutons radio (sélection unique) ou des cases à cocher (sélections multiples).',
        defaultProps: {
            type: "ChoiceGroup",
            label: "Libellé",
            helpText: "",
            required: false,
            multiple: false,
            options: [],
            conditions: [],
        },
        editionSchema: [
            {key: "label", label: "Titre", type: "text"},
            {key: "helpText", label: "Message d'aide", type: "textarea", rows: 2},
            {key: "required", label: "Requis", type: "checkbox"},
            {key: "multiple", label: "Sélection multiple", type: "checkbox"},
            readOnlyItem,
        ],
    },
    NumberInput: {
        id: "drag-numberinput",
        type: "NumberInput",
        title: "Nombre",
        icon: Hash,
        description: 'Permet la saisie d\'une valeur numérique, avec bornes min/max optionnelles.',
        defaultProps: {
            type: "NumberInput",
            label: "Libellé",
            placeHolder: "",
            required: false,
            helpText: "",
            min: "",
            max: "",
            step: "",
        },
        editionSchema: [
            ...commonInputSchema,
            {key: "defaultValue", label: "Valeur par défaut", type: "number"},
            readOnlyItem,
            {key: "min", label: "Valeur minimale", type: "number"},
            {key: "max", label: "Valeur maximale", type: "number"},
            {key: "step", label: "Pas", type: "number"},
        ],
    },
    EmailInput: {
        id: "drag-emailinput",
        type: "EmailInput",
        title: "Email",
        icon: Mail,
        description: 'Permet la saisie d\'une adresse email.',
        defaultProps: {
            type: "EmailInput",
            label: "Libellé",
            placeHolder: "",
            required: false,
            helpText: "",
        },
        editionSchema: prefillableInputSchema,
    },
    TelInput: {
        id: "drag-telinput",
        type: "TelInput",
        title: "Téléphone",
        icon: Phone,
        description: 'Permet la saisie d\'un numéro de téléphone.',
        defaultProps: {
            type: "TelInput",
            label: "Libellé",
            placeHolder: "",
            required: false,
            helpText: "",
        },
        editionSchema: prefillableInputSchema,
    },
    UrlInput: {
        id: "drag-urlinput",
        type: "UrlInput",
        title: "URL",
        icon: Link,
        description: 'Permet la saisie d\'une adresse web.',
        defaultProps: {
            type: "UrlInput",
            label: "Libellé",
            placeHolder: "",
            required: false,
            helpText: "",
        },
        editionSchema: prefillableInputSchema,
    },
    DateTimeInput: {
        id: "drag-datetimeinput",
        type: "DateTimeInput",
        title: "Date / Heure",
        icon: Calendar,
        description: 'Permet la saisie d\'une date, d\'une heure, ou des deux.',
        defaultProps: {
            type: "DateTimeInput",
            label: "Libellé",
            required: false,
            helpText: "",
            mode: "datetime-local",
        },
        editionSchema: [
            ...commonInputSchemaNoPlaceholder,
            {
                key: "mode", label: "Type de saisie", type: "select", options: [
                    {value: "date", label: "Date"},
                    {value: "datetime-local", label: "Date et heure"},
                    {value: "time", label: "Heure"},
                ]
            },
            {key: "hasCurrentDate", label: "Préremplir avec la date et l'heure actuelles", type: "checkbox"},
            readOnlyItem,
        ],
    },
    AddressInput: {
        id: "drag-addressinput",
        type: "AddressInput",
        title: "Adresse",
        icon: MapPin,
        description: 'Permet la saisie d\'une adresse postale.',
        defaultProps: {
            type: "AddressInput",
            label: "Libellé",
            placeHolder: "Indiquez un lieu…",
            required: false,
            helpText: "",
        },
        editionSchema: commonInputSchema,
    },
    FileInput: {
        id: "drag-fileinput",
        type: "FileInput",
        title: "Fichier",
        icon: Paperclip,
        description: 'Permet le dépôt d\'un ou plusieurs fichiers.',
        defaultProps: {
            type: "FileInput",
            label: "Libellé",
            required: false,
            helpText: "",
            acceptedFile: "image",
            allowMultiple: false,
        },
        editionSchema: [
            ...commonInputSchemaNoPlaceholder,
            {
                key: "acceptedFile", label: "Fichiers acceptés", type: "select", options: [
                    {value: "image", label: "Images"},
                    {value: "file", label: "PDF"},
                    {value: "both", label: "Images et PDF"},
                ]
            },
            {key: "allowMultiple", label: "Autoriser plusieurs fichiers", type: "checkbox"},
        ],
    },
    HourMinuteInput: {
        id: "drag-hourminuteinput",
        type: "HourMinuteInput",
        title: "Heure",
        icon: Clock,
        description: 'Permet la saisie d\'une heure (HH:MM).',
        defaultProps: {
            type: "HourMinuteInput",
            label: "Libellé",
            required: false,
            helpText: "",
        },
        editionSchema: [
            ...commonInputSchemaNoPlaceholder,
            {key: "defaultValue", label: "Valeur par défaut", type: "text", helpText: "Format HH:MM, par exemple 08:30"},
            readOnlyItem,
        ],
    },
    Select: {
        id: "drag-select",
        type: "Select",
        title: "Liste déroulante",
        description: 'Affiche une liste déroulante d\'options, à choix unique ou multiple.',
        defaultProps: {
            type: "Select",
            label: "Libellé",
            helpText: "",
            required: false,
            multiple: false,
            options: [],
            conditions: [],
        },
        editionSchema: [
            {key: "label", label: "Titre", type: "text"},
            {key: "helpText", label: "Message d'aide", type: "textarea", rows: 2},
            {key: "required", label: "Requis", type: "checkbox"},
            {key: "multiple", label: "Sélection multiple", type: "checkbox"},
            {key: "customOption", label: "Autoriser la saisie d'une autre valeur", type: "checkbox"},
            readOnlyItem,
        ],
    },
    Signature: {
        id: "drag-signature",
        type: "Signature",
        title: "Signature",
        description: 'Affiche une zone dédiée à la signature de l\'utilisateur.',
        defaultProps: {
            type: "Signature",
            label: "Libellé",
            helpText: "",
            required: false,
        },
        editionSchema: commonInputSchemaNoPlaceholder,
    },
    FieldSet: {
        id: "drag-fieldset",
        type: "FieldSet",
        title: "Groupe de champs",
        description: 'Regroupe plusieurs blocs à l\'intérieur d\'un même ensemble.',
        defaultProps: {
            type: "FieldSet",
            children: [],
        },
    },
    Repeatable: {
        id: "drag-repeatable",
        type: "Repeatable",
        title: "Répétable",
        description: 'Permet à l\'utilisateur de répéter un ensemble de blocs plusieurs fois.',
        defaultProps: {
            type: "Repeatable",
            children: [],
            maxItems: 1,
        },
    }
};

export const getAllBlockDefinitions = () => Object.values(blockDefinitions);
