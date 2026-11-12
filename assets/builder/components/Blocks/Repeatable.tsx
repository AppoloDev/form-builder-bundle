import { RepeatableProps, BlockDefinition } from "./Definition";
import { useFormBuilderStore } from "../../stores/block.store";
import { EditableBlock } from "./EditableBlock";
import { AddButton } from "../AddButton";
import { Empty } from "../Empty";
import { ChildrenSorter, DropZone } from "./ChildrenSorter";
import { createBlockFromTemplate } from "../../utilities/block.utiles";
import { TextEdition } from "../Edition/TextEdition";
import { ContainerContext, containerFrameClasses, repeatableCardClasses } from "./ContainerContext";
import { t } from "../../i18n";
import { useContext } from "react";

const Repeatable = ({id, type, children, maxItems}: RepeatableProps) => {
    const {updateBlock} = useFormBuilderStore();
    const nested = useContext(ContainerContext);

    const addChild = (def: BlockDefinition) => {
        const newBlock = createBlockFromTemplate(def);
        updateBlock(id, {children: [...children, newBlock]});
    };

    const handleMaxItemsChange = (v: string) => {
        updateBlock(id, {maxItems: Number(v) || 0});
    };

    return (
        <EditableBlock id={id} type={type} editionItems={[
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
            <DropZone containerId={id} className={containerFrameClasses(nested, false)}>
                {children.length === 0 ? (
                    <Empty onPick={addChild}/>
                ) : (
                    <>
                        {/* Élément répété : une carte dans le cadre du conteneur */}
                        <div className={repeatableCardClasses}>
                            <ContainerContext.Provider value={false}>
                                <ChildrenSorter childrenBlocks={children} containerId={id}/>
                            </ContainerContext.Provider>
                            <AddButton onPick={addChild}/>
                        </div>
                    </>
                )}
            </DropZone>
            </ContainerContext.Provider>
        </EditableBlock>
    );
};

export default Repeatable;
