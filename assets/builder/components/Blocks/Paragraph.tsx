import { useEffect, useState } from "react";
import { ParagraphProps } from "./Definition";
import { useFormBuilderStore } from "../../stores/block.store";
import { EditableBlock } from "./EditableBlock";
import { InlineEditableText } from "../InlineEditableText";

type Props = ParagraphProps & { preview?: boolean };

const Paragraph = ({id, type, text, preview}: Props) => {
    const {updateBlock} = useFormBuilderStore();
    const [label, setLabel] = useState(text);

    useEffect(() => {
        setLabel(text);
    }, [text]);

    const handleChange = <K extends keyof Omit<ParagraphProps, "id">>(field: K, value: ParagraphProps[K]) => {
        updateBlock(id, {[field]: value} as Partial<ParagraphProps>);
    };

    return (
        <EditableBlock id={id} type={type} preview={preview}>
            <div className={preview ? "" : "rounded-lg p-2 transition-colors"}>
                {preview ? (
                    <p>{label}</p>
                ) : (
                    <InlineEditableText
                        value={label}
                        onCommit={(v) => {
                            setLabel(v);
                            handleChange('text', v);
                        }}
                        placeholder="Texte du paragraphe"
                        multiline
                    />
                )}
            </div>
        </EditableBlock>
    );
};

export default Paragraph;
