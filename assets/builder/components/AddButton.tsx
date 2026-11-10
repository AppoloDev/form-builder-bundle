import { PlusIcon } from "@phosphor-icons/react";
import { AddMenu } from "./AddMenu";
import { BlockDefinition } from "./Blocks/Definition";
import { Button } from "./ui/button";
import { t } from "../i18n";

/** Bouton « Ajouter une question » placé sous le dernier bloc d'une liste : ajoute en fin de liste. */
export const AddButton = ({onPick}: { onPick: (def: BlockDefinition) => void }) => (
    <div className="pt-1">
        <AddMenu onPick={onPick}>
            <Button type="button" size="sm">
                <PlusIcon/> {t("builder.addBlock")}
            </Button>
        </AddMenu>
    </div>
);
