import { MouseEvent, useRef } from "react";
import { AddMenu } from "./AddMenu";
import { BlockDefinition } from "./Blocks/Definition";
import { Button } from "./ui/button";
import { t } from "../i18n";
import { PlusIcon } from "@phosphor-icons/react";

type Props = {
    onPick: (def: BlockDefinition) => void;
}

export const Empty = ({onPick}: Props) => {
    const buttonRef = useRef<HTMLButtonElement>(null);

    // Toute la zone ouvre le menu à la souris ; le bouton reste le contrôle accessible au clavier.
    const openFromZone = (e: MouseEvent) => {
        if ((e.target as HTMLElement).closest("button")) return;
        buttonRef.current?.click();
    };

    return (
        <div
            onClick={openFromZone}
            className="w-full flex flex-col justify-center space-y-5 p-20 rounded-lg cursor-pointer transition-colors hover:bg-muted/50"
        >
            <div className="max-w-sm mx-auto flex flex-col items-center gap-2">
                <div className="font-semibold text-foreground text-center">
                    {t("builder.empty")}
                </div>

                <p className="text-sm text-muted-foreground text-center">
                    {t("builder.emptyDescription")}
                </p>
            </div>

            <div className="flex justify-center mt-5">
                <AddMenu onPick={onPick}>
                    <Button type="button" ref={buttonRef}>
                        <PlusIcon /> {t("builder.addBlock")}
                    </Button>
                </AddMenu>
            </div>
        </div>
    )
}
