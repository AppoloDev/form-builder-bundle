import React, { useEffect, useState } from "react";
import { TitleProps } from "./Definition";
import { useFormBuilderStore } from "../../stores/block.store";
import { EditableBlock } from "./EditableBlock";
import { SelectEdition } from "../Edition/SelectEdition";
import { InlineEditableText } from "../InlineEditableText";
import { t } from "../../i18n";

type Props = TitleProps & { preview?: boolean };

const Title = ({id, type, text, heading, preview}: Props) => {
    const {updateBlock} = useFormBuilderStore();
    const [label, setLabel] = useState(text);
    const [headingLevel, setHeadingLevel] = useState(heading);

    useEffect(() => {
        setLabel(text);
        setHeadingLevel(heading);
    }, [text, heading]);

    const handleChange = <K extends keyof Omit<TitleProps, "id">>(field: K, value: TitleProps[K]) => {
        updateBlock(id, {[field]: value} as Partial<TitleProps>);
    };

    const renderClass = (headingLevel: string) => {
        switch (headingLevel) {
            case 'h1':
                return 'text-4xl';
            case 'h2':
                return 'text-3xl';
            case 'h3':
                return 'text-2xl';
            case 'h4':
                return 'text-xl';
            case 'h5':
                return 'text-lg';
            case 'h6':
                return 'text-base';
        }
    }

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
                    React.createElement(headingLevel, {className: renderClass(headingLevel)}, label)
                ) : (
                    <InlineEditableText
                        value={label}
                        onCommit={(v) => {
                            setLabel(v);
                            handleChange('text', v);
                        }}
                        placeholder={t("title.placeholder")}
                        // shadcn's Input hardcodes `md:text-sm`, which otherwise silently
                        // shrinks the title back down on desktop widths — repeat the size
                        // under `md:` too so it actually wins over Input's own default.
                        className={`h-auto py-1 ${renderClass(headingLevel)} md:${renderClass(headingLevel)}`}
                    />
                )}
            </div>
        </EditableBlock>
    );
};

export default Title;
