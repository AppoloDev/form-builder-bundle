import { FieldSetProps, Block, BlockDefinition } from "./Definition";
import { useFormBuilderStore } from "../../stores/block.store";
import { EditableBlock } from "./EditableBlock";
import { Empty } from "../Empty";
import { AddButton } from "../AddButton";
import { ChildrenSorter } from "./ChildrenSorter";
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

    const handleReorder = (next: Block[]) => {
        updateBlock(id, {children: next});
    };

    return (
        <EditableBlock id={id} type={type}>
            <ContainerContext.Provider value={true}>
            <div className={containerFrameClasses(nested)}>
                {children.length === 0 ? (
                    <Empty onPick={addChild}/>
                ) : (
                    <>
                        <ChildrenSorter childrenBlocks={children} onReorder={handleReorder}/>
                        <AddButton onPick={addChild}/>
                    </>
                )}
            </div>
            </ContainerContext.Provider>
        </EditableBlock>
    );
};

export default FieldSet;
