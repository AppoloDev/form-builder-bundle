import { FieldSetProps, Block, BlockDefinition } from "./Definition";
import { useFormBuilderStore } from "../../stores/block.store";
import { EditableBlock } from "./EditableBlock";
import { Empty } from "../Empty";
import { AddMenu } from "../AddMenu";
import { ChildrenSorter } from "./ChildrenSorter";
import { createBlockFromTemplate } from "../../utilities/block.utiles";
import { Button } from "../ui/button";
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
                    <>
                        <ChildrenSorter childrenBlocks={children} onReorder={handleReorder}/>

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

export default FieldSet;
