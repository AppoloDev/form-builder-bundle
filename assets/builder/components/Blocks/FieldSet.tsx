import { FieldSetProps, Block, BlockDefinition } from "./Definition";
import { useFormBuilderStore } from "../../stores/block.store";
import { EditableBlock } from "./EditableBlock";
import { Empty } from "../Empty";
import { AddMenu } from "../AddMenu";
import { ChildrenSorter } from "./ChildrenSorter";
import { createBlockFromTemplate } from "../../utilities/block.utiles";
import { Button } from "../ui/button";

type Props = FieldSetProps & { preview?: boolean };

const FieldSet = ({id, type, children, preview}: Props) => {
    const {updateBlock} = useFormBuilderStore();

    const addChild = (def: BlockDefinition, overrides?: Record<string, any>) => {
        const newBlock = createBlockFromTemplate(def, overrides);
        updateBlock(id, {children: [...children, newBlock]});
    };

    const handleReorder = (next: Block[]) => {
        updateBlock(id, {children: next});
    };

    return (
        <EditableBlock id={id} type={type} preview={preview}>
            <div className={preview ? "space-y-3" : "space-y-3 rounded-lg p-2 transition-colors"}>
                {children.length === 0 ? (
                    <Empty onPick={addChild}/>
                ) : (
                    <>
                        <ChildrenSorter childrenBlocks={children} onReorder={handleReorder}/>

                        <div className="pt-1">
                            <AddMenu onPick={addChild}>
                                <Button type="button" size="sm">
                                    Ajouter un bloc
                                </Button>
                            </AddMenu>
                        </div>
                    </>
                )}
            </div>
        </EditableBlock>
    );
};

export default FieldSet;
