import { FieldSetProps, BlockDefinition } from "./Definition";
import { useFormBuilderStore } from "../../stores/block.store";
import { EditableBlock } from "./EditableBlock";
import { AddElementButton } from "../AddElementButton";
import { Empty } from "../Empty";
import { ChildrenSorter, DropZone } from "./ChildrenSorter";
import { createBlockFromTemplate } from "../../utilities/block.utiles";
import { ContainerContext, InsideContainerContext, containerFrameClasses } from "./ContainerContext";
import { t } from "../../i18n";
import { useContext } from "react";

const FieldSet = ({id, type, children}: FieldSetProps) => {
    const {updateBlock} = useFormBuilderStore();
    const nested = useContext(ContainerContext);

    const addChild = (def: BlockDefinition) => {
        const newBlock = createBlockFromTemplate(def);
        updateBlock(id, {children: [...children, newBlock]});
    };

    return (
        <EditableBlock id={id} type={type}>
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

export default FieldSet;
