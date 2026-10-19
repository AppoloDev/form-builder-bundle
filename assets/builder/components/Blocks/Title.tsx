import React, { useEffect, useState } from "react";
import { TitleProps } from "./Definition";
import { useFormBuilderStore } from "../../stores/block.store";
import { EditableBlock } from "./EditableBlock";
import { SelectEdition } from "../Edition/SelectEdition";
import { InlineEditableText } from "../InlineEditableText";
import { t } from "../../i18n";
import { TITLE_CLASSES, TITLE_INPUT_CLASSES, headingLevelOf } from "../../utilities/typography";

type Props = TitleProps & { preview?: boolean };

const Title = ({id, type, text, heading, preview}: Props) => {
    const {updateBlock} = useFormBuilderStore();
    const [label, setLabel] = useState(text);
    const [headingLevel, setHeadingLevel] = useState(heading);
    const level = headingLevelOf(headingLevel);

    useEffect(() => {
        setLabel(text);
        setHeadingLevel(heading);
    }, [text, heading]);

    const handleChange = <K extends keyof Omit<TitleProps, "id">>(field: K, value: TitleProps[K]) => {
        updateBlock(id, {[field]: value} as Partial<TitleProps>);
    };

    return (
        <EditableBlock id={id} type={type} preview={preview} editionItems={[
            <SelectEdition
                label={t("title.level")}
                value={headingLevel}
                options={[
                    {value: 'h1', label: t("title.levelN", {n: 1})},
                    {value: 'h2', label: t("title.levelN", {n: 2})},
                    {value: 'h3', label: t("title.levelN", {n: 3})},
                    {value: 'h4', label: t("title.levelN", {n: 4})},
                    {value: 'h5', label: t("title.levelN", {n: 5})},
                    {value: 'h6', label: t("title.levelN", {n: 6})},
                ]}
                editItem={(v) => {
                    handleChange('heading', v)
                    setHeadingLevel(v);
                }}
            />
        ]}>
            <div className={preview ? "" : "rounded-lg p-2 transition-colors"}>
                {preview ? (
                    React.createElement(level, {className: TITLE_CLASSES[level]}, label)
                ) : (
                    <InlineEditableText
                        value={label}
                        onCommit={(v) => {
                            setLabel(v);
                            handleChange('text', v);
                        }}
                        placeholder={t("title.placeholder")}
                        // Taille répétée sous `md:` (cf. TITLE_INPUT_CLASSES) ; graisse et couleur comme dans le formulaire.
                        className={`h-auto py-1 placeholder:text-muted-foreground/50 ${TITLE_INPUT_CLASSES[level]}`}
                    />
                )}
            </div>
        </EditableBlock>
    );
};

export default Title;
