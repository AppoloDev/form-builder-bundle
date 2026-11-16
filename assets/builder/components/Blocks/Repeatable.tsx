import { RepeatableProps, BlockDefinition } from "./Definition";
import { useFormBuilderStore } from "../../stores/block.store";
import { EditableBlock } from "./EditableBlock";
import { AddElementButton } from "../AddElementButton";
import { Empty } from "../Empty";
import { ChildrenSorter, DropZone } from "./ChildrenSorter";
import { createBlockFromTemplate } from "../../utilities/block.utiles";
import { MaxItemsEdition } from "../Edition/MaxItemsEdition";
import { ContainerContext, InsideContainerContext, containerFrameClasses } from "./ContainerContext";
import { t } from "../../i18n";
import { useContext } from "react";

/** Un répétable limité l'est à 2 éléments au moins : 1 n'est pas une répétition, 0 vaut « illimité ». */
const MIN_ITEMS = 2;

const Repeatable = ({id, type, children, maxItems}: RepeatableProps) => {
    const {updateBlock} = useFormBuilderStore();
    const nested = useContext(ContainerContext);

    const addChild = (def: BlockDefinition) => {
        const newBlock = createBlockFromTemplate(def);
        updateBlock(id, {children: [...children, newBlock]});
    };

    return (
        <EditableBlock id={id} type={type} editionItems={[
            <MaxItemsEdition
                key="maxItems"
                unlimitedLabel={t("repeatable.unlimited")}
                label={t("repeatable.max")}
                value={maxItems}
                min={MIN_ITEMS}
                editItem={(value) => updateBlock(id, {maxItems: value})}
            />,
        ]}>
            <InsideContainerContext.Provider value={true}>
            <ContainerContext.Provider value={true}>
            <DropZone containerId={id} className={containerFrameClasses(nested)}>
                {children.length === 0 ? (
                    <Empty onPick={addChild}/>
                ) : (
                    <>
                        <ChildrenSorter childrenBlocks={children} containerId={id}/>
                        <AddElementButton onPick={addChild}/>
                    </>
                )}
            </DropZone>
            </ContainerContext.Provider>
            </InsideContainerContext.Provider>
        </EditableBlock>
    );
};

export default Repeatable;
