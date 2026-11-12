import { SeparatorProps } from "./Definition";
import { EditableBlock } from "./EditableBlock";

/** Trait horizontal entre deux parties du formulaire ; aucune option. */
const Separator = ({id, type}: SeparatorProps) => (
    <EditableBlock id={id} type={type}>
        <div className="py-3">
            <hr className="border-border"/>
        </div>
    </EditableBlock>
);

export default Separator;
