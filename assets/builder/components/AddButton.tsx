import { PlusIcon } from "@phosphor-icons/react";
import { AddMenu } from "./AddMenu";
import { BlockDefinition } from "./Blocks/Definition";
import { Button } from "./ui/button";
import { t } from "../i18n";

/** Bouton « Ajouter » (taille default = h-8, comme « Ajouter un élément ») placé sous le dernier bloc d'une liste : ajoute en fin de liste. */
export const AddButton = ({onPick}: { onPick: (def: BlockDefinition) => void }) => (
    <div className="pt-1">
        <AddMenu onPick={onPick}>
            <Button type="button">
                <PlusIcon/> {t("builder.add")}
            </Button>
        </AddMenu>
    </div>
);
