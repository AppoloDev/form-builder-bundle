import { useEffect, useState } from "react";
import { ParagraphProps } from "./Definition";
import { useFormBuilderStore } from "../../stores/block.store";
import { EditableBlock } from "./EditableBlock";
import { InlineEditableText } from "../InlineEditableText";
import { t } from "../../i18n";
import { PARAGRAPH_TEXTAREA_CLASSES } from "../../utilities/typography";
import { TransformEdition } from "../Edition/TransformEdition";
import { transformTextBlock } from "./TextVariants";

const Paragraph = ({id, type, text}: ParagraphProps) => {
    const {updateBlock, replaceBlock} = useFormBuilderStore();
    const [label, setLabel] = useState(text);

    useEffect(() => {
        setLabel(text);
    }, [text]);

    const handleChange = <K extends keyof Omit<ParagraphProps, "id">>(field: K, value: ParagraphProps[K]) => {
        updateBlock(id, {[field]: value} as Partial<ParagraphProps>);
    };

    return (
        <EditableBlock id={id} type={type} editionItems={[
            <TransformEdition
                key="transform"
                current="paragraph"
                onTransform={(v) => replaceBlock(id, transformTextBlock({id, type, text: label}, v))}
            />
        ]}>
            <div className="rounded-lg p-2 transition-colors">
                <InlineEditableText
                        value={label}
                        blockId={id}
                    onCommit={(v) => {
                            setLabel(v);
                            handleChange('text', v);
                        }}
                        placeholder={t("paragraph.text")}
                        multiline
                        className={`placeholder:text-muted-foreground/50 ${PARAGRAPH_TEXTAREA_CLASSES}`}
                    />
            </div>
        </EditableBlock>
    );
};

export default Paragraph;
