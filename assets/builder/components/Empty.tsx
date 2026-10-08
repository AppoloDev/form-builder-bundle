import { AddMenu } from "./AddMenu";
import { BlockDefinition } from "./Blocks/Definition";
import { Button } from "./ui/button";

type Props = {
    onPick: (def: BlockDefinition, overrides?: Record<string, any>) => void;
}

export const Empty = ({onPick}: Props) => {
    return (
        <div className="max-w-sm w-full flex flex-col justify-center mx-auto space-y-5 p-8">
            <div className="flex flex-col items-center gap-2">
                <div className="font-semibold text-foreground text-center">
                    Aucun bloc pour le moment !
                </div>

                <p className="text-sm text-muted-foreground text-center">
                    Pour ajouter un nouveau bloc, veuillez cliquer sur le bouton ci-dessous.
                </p>
            </div>

            <div className="flex justify-center mt-5">
                <AddMenu
                    onPick={onPick}
                >
                    <Button type="button">
                        Ajouter un bloc
                    </Button>
                </AddMenu>
            </div>
        </div>
    )
}
