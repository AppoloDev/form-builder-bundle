import { AddMenu } from "./AddMenu";
import { BlockDefinition } from "./Blocks/Definition";
import { t } from "../i18n";

type Props = {
    onPick: (def: BlockDefinition, overrides?: Record<string, any>) => void;
    placeholder?: string;
};

/**
 * Bouton « + » centré sur le bord inférieur d'un bloc : ajoute un bloc à la suite de celui-ci.
 * À placer dans un parent `relative` (racine du builder, enfants d'un groupe ou d'un répétable).
 */
export const AddAfterButton = ({onPick, placeholder}: Props) => (
    <div className="absolute left-1/2 -bottom-3 z-10 -translate-x-1/2">
        <AddMenu onPick={onPick} placeholder={placeholder}>
            <button
                type="button"
                className="rounded-full border border-input bg-primary shadow-sm p-2 hover:bg-primary/80 cursor-pointer transition-colors"
                title={t("builder.addBlock")}
            >
                <svg width="16" height="16" viewBox="0 0 24 24" className="text-primary-foreground">
                    <path fill="currentColor" d="M11 11V5h2v6h6v2h-6v6h-2v-6H5v-2z"/>
                </svg>
            </button>
        </AddMenu>
    </div>
);
