import { useEffect, useState } from "react";
import { TitleProps } from "./Definition";
import { useFormBuilderStore } from "../../stores/block.store";
import { EditableBlock } from "./EditableBlock";
import { TransformEdition } from "../Edition/TransformEdition";
import { InlineEditableText } from "../InlineEditableText";
import { t } from "../../i18n";
import { TITLE_INPUT_CLASSES, headingLevelOf } from "../../utilities/typography";
import { TEXT_VARIANTS, textVariantOf, transformTextBlock } from "./TextVariants";

const Title = ({id, type, text, heading}: TitleProps) => {
    const {updateBlock, replaceBlock} = useFormBuilderStore();
    const [label, setLabel] = useState(text);
    const level = headingLevelOf(heading);
    const variant = TEXT_VARIANTS.find((v) => v.id === textVariantOf({type, heading}));

    useEffect(() => {
        setLabel(text);
    }, [text]);

    return (
        <EditableBlock
            id={id}
            type={type}
            title={variant && t(variant.titleKey)}
            icon={variant?.icon}
            editionItems={[
                <TransformEdition
                    key="transform"
                    current={variant?.id}
                    onTransform={(v) => replaceBlock(id, transformTextBlock({id, type, text: label, heading}, v))}
                />
            ]}
        >
            <div className="rounded-lg p-2 transition-colors">
                <InlineEditableText
                    value={label}
                    blockId={id}
                    onCommit={(v) => {
                        setLabel(v);
                        updateBlock(id, {text: v});
                    }}
                    placeholder={t("title.placeholder")}
                    // Taille répétée sous `md:` (cf. TITLE_INPUT_CLASSES) ; graisse et couleur comme dans le formulaire.
                    className={`h-auto py-1 placeholder:text-muted-foreground/50 ${TITLE_INPUT_CLASSES[level]}`}
                />
            </div>
        </EditableBlock>
    );
};

export default Title;
