import { ComponentType } from "react";
import ChoiceGroupInput from "./Blocks/ChoiceGroupInput";
import TextInput from "./Blocks/TextInput";
import TextareaInput from "./Blocks/TextareaInput";
import Title from "./Blocks/Title";
import Paragraph from "./Blocks/Paragraph";
import NumberInput from "./Blocks/NumberInput";
import EmailInput from "./Blocks/EmailInput";
import TelInput from "./Blocks/TelInput";
import UrlInput from "./Blocks/UrlInput";
import DateTimeInput from "./Blocks/DateTimeInput";
import AddressInput from "./Blocks/AddressInput";
import FileInput from "./Blocks/FileInput";
import HourMinuteInput from "./Blocks/HourMinuteInput";
import Select from "./Blocks/Select";
import Signature from "./Blocks/Signature";
import FieldSet from "./Blocks/FieldSet";
import Repeatable from "./Blocks/Repeatable";
import { BlockType } from "./Blocks/Definition";

export const BLOCK_COMPONENTS: Record<BlockType, ComponentType<any>> = {
    'TextInput': TextInput,
    'TextareaInput': TextareaInput,
    'Title': Title,
    'Paragraph': Paragraph,
    'ChoiceGroup': ChoiceGroupInput,
    'NumberInput': NumberInput,
    'EmailInput': EmailInput,
    'TelInput': TelInput,
    'UrlInput': UrlInput,
    'DateTimeInput': DateTimeInput,
    'AddressInput': AddressInput,
    'FileInput': FileInput,
    'HourMinuteInput': HourMinuteInput,
    'Select': Select,
    'Signature': Signature,
    'FieldSet': FieldSet,
    'Repeatable': Repeatable,
};
