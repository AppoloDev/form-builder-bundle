import { PlusIcon } from "@phosphor-icons/react";
import { AddMenu } from "./AddMenu";
import { BlockDefinition } from "./Blocks/Definition";
import { t } from "../i18n";

/** « Ajouter un élément » d'un conteneur (condition, groupe, répétable) : allure d'une liste déroulante (SelectTrigger), + à gauche. */
export const AddElementButton = ({onPick}: { onPick: (def: BlockDefinition) => void }) => (
    <AddMenu onPick={onPick}>
        <button
            type="button"
            className="flex h-8 w-fit min-w-40 cursor-pointer items-center gap-1.5 rounded-lg border border-input bg-transparent py-2 pr-2 pl-2.5 text-sm whitespace-nowrap text-muted-foreground transition-colors outline-none select-none hover:bg-muted focus-visible:border-ring focus-visible:ring-3 focus-visible:ring-ring/50"
        >
            <PlusIcon aria-hidden="true" className="size-4"/>
            {t("builder.addElement")}
        </button>
    </AddMenu>
);
