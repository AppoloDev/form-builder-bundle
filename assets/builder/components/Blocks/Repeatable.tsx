import { RepeatableProps, Block, BlockDefinition } from "./Definition";
import { useFormBuilderStore } from "../../stores/block.store";
import { EditableBlock } from "./EditableBlock";
import { Empty } from "../Empty";
import { AddMenu } from "../AddMenu";
import { ChildrenSorter } from "./ChildrenSorter";
import { createBlockFromTemplate } from "../../utilities/block.utiles";
import { TextEdition } from "../Edition/TextEdition";
import { Button } from "../ui/button";

type Props = RepeatableProps & { preview?: boolean };

const Repeatable = ({id, type, children, maxItems, preview}: Props) => {
    const {updateBlock} = useFormBuilderStore();

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
                label="Nombre maximum de répétitions"
                type="number"
                helpText="0 = illimité"
                value={String(maxItems ?? 0)}
                editItem={handleMaxItemsChange}
            />,
        ]}>
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

export default Repeatable;
