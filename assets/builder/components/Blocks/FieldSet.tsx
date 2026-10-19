import { FieldSetProps, Block, BlockDefinition } from "./Definition";
import { useFormBuilderStore } from "../../stores/block.store";
import { EditableBlock } from "./EditableBlock";
import { Empty } from "../Empty";
import { ChildrenSorter } from "./ChildrenSorter";
import { createBlockFromTemplate } from "../../utilities/block.utiles";
import { ContainerContext, containerFrameClasses } from "./ContainerContext";
import { t } from "../../i18n";
import { useContext } from "react";

type Props = FieldSetProps & { preview?: boolean };

const FieldSet = ({id, type, children, preview}: Props) => {
    const {updateBlock} = useFormBuilderStore();
    const nested = useContext(ContainerContext);

    const addChild = (def: BlockDefinition, overrides?: Record<string, any>) => {
        const newBlock = createBlockFromTemplate(def, overrides);
        updateBlock(id, {children: [...children, newBlock]});
    };

    const addChildAfter = (index: number, def: BlockDefinition, overrides?: Record<string, any>) => {
        const next = [...children];
        next.splice(index + 1, 0, createBlockFromTemplate(def, overrides));
        updateBlock(id, {children: next});
    };

    const handleReorder = (next: Block[]) => {
        updateBlock(id, {children: next});
    };

    return (
        <EditableBlock id={id} type={type} preview={preview}>
            <ContainerContext.Provider value={true}>
            <div className={preview ? "flex flex-col gap-5" : containerFrameClasses(nested)}>
                {children.length === 0 ? (
                    <Empty onPick={addChild}/>
                ) : (
                    <ChildrenSorter childrenBlocks={children} onReorder={handleReorder} onAddAfter={addChildAfter}/>
                )}
            </div>
            </ContainerContext.Provider>
        </EditableBlock>
    );
};

export default FieldSet;
