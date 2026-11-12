import { FieldSetProps, BlockDefinition } from "./Definition";
import { useFormBuilderStore } from "../../stores/block.store";
import { EditableBlock } from "./EditableBlock";
import { AddButton } from "../AddButton";
import { Empty } from "../Empty";
import { ChildrenSorter, DropZone } from "./ChildrenSorter";
import { createBlockFromTemplate } from "../../utilities/block.utiles";
import { ContainerContext, containerFrameClasses } from "./ContainerContext";
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
            <ContainerContext.Provider value={true}>
            <DropZone containerId={id} className={containerFrameClasses(nested)}>
                {children.length === 0 ? (
                    <Empty onPick={addChild}/>
                ) : (
                    <>
                        <ChildrenSorter childrenBlocks={children} containerId={id}/>
                        <AddButton onPick={addChild}/>
                    </>
                )}
            </DropZone>
            </ContainerContext.Provider>
        </EditableBlock>
    );
};

export default FieldSet;
