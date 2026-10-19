import { RepeatableProps, Block, BlockDefinition } from "./Definition";
import { useFormBuilderStore } from "../../stores/block.store";
import { EditableBlock } from "./EditableBlock";
import { Empty } from "../Empty";
import { AddMenu } from "../AddMenu";
import { ChildrenSorter } from "./ChildrenSorter";
import { createBlockFromTemplate } from "../../utilities/block.utiles";
import { TextEdition } from "../Edition/TextEdition";
import { Button } from "../ui/button";
import { ContainerContext, containerFrameClasses, repeatableCardClasses } from "./ContainerContext";
import { t } from "../../i18n";
import { useContext } from "react";

type Props = RepeatableProps & { preview?: boolean };

const Repeatable = ({id, type, children, maxItems, preview}: Props) => {
    const {updateBlock} = useFormBuilderStore();
    const nested = useContext(ContainerContext);

    const addChild = (def: BlockDefinition, overrides?: Record<string, any>) => {
        const newBlock = createBlockFromTemplate(def, overrides);
        updateBlock(id, {children: [...children, newBlock]});
    };

    const handleReorder = (next: Block[]) => {
        updateBlock(id, {children: next});
    };

    const handleMaxItemsChange = (v: string) => {
        updateBlock(id, {maxItems: Number(v) || 0});
    };

    return (
        <EditableBlock id={id} type={type} preview={preview} editionItems={[
            <TextEdition
                key="maxItems"
                label={t("repeatable.max")}
                type="number"
                helpText={t("repeatable.unlimited")}
                value={String(maxItems ?? 0)}
                editItem={handleMaxItemsChange}
            />,
        ]}>
            <ContainerContext.Provider value={true}>
            <div className={preview ? "flex flex-col gap-5" : containerFrameClasses(nested, false)}>
                {children.length === 0 ? (
                    <Empty onPick={addChild}/>
                ) : (
                    <>
                        {/* Élément répété : une carte dans le cadre du conteneur */}
                        <div className={preview ? undefined : repeatableCardClasses}>
                            {/* Sur la carte (fond « background »), un conteneur imbriqué repasse en fond teinté */}
                            <ContainerContext.Provider value={false}>
                                <ChildrenSorter childrenBlocks={children} onReorder={handleReorder}/>
                            </ContainerContext.Provider>
                        </div>

                        <div>
                            <AddMenu onPick={addChild}>
                                <Button type="button" size="sm">
                                    {t("builder.addBlock")}
                                </Button>
                            </AddMenu>
                        </div>
                    </>
                )}
            </div>
            </ContainerContext.Provider>
        </EditableBlock>
    );
};

export default Repeatable;
