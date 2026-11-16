import { FC } from "react";
import { UploadSimpleIcon } from "@phosphor-icons/react";
import { makeInputBlock } from "./GenericInput";
import { MessageKey, t } from "../../i18n";

/** `image` ou `document` ; les anciennes valeurs (`file` = PDF, `both`) deviennent `document`, comme côté PHP. */
const fileKind = (acceptedFile: unknown) => (acceptedFile === "image" ? "image" : "document");

/** « 3 images maximum », « 1 fichier maximum », « Ajoutez vos fichiers » (illimité)… comme file_limit_text (thème). */
const limitLabel = (acceptedFile: unknown, max: number): string => {
    const count = max === 1 ? "one" : max > 1 ? "many" : "none";

    return t(`file.limit.${fileKind(acceptedFile)}.${count}` as MessageKey, {max});
};

/** Aperçu non interactif de la zone de dépôt du formulaire (thème form_builder : file_repeatable_row), sans le bouton d'ajout. */
const FileInputBlock = makeInputBlock("file", {
    renderPreview: (form) => (
        <div inert className="flex w-full flex-col items-center gap-4 rounded-lg border border-dashed border-input p-8 text-center">
            <div className="flex size-16 items-center justify-center rounded-full bg-muted text-muted-foreground">
                <UploadSimpleIcon className="size-6"/>
            </div>
            <div className="flex flex-col gap-1">
                <p className="text-sm text-muted-foreground">{limitLabel(form.acceptedFile, Number(form.maxItems) || 0)}</p>
                <p className="text-xs text-muted-foreground">{t(`file.formats.${fileKind(form.acceptedFile)}` as MessageKey)}</p>
            </div>
        </div>
    ),
});

/** Anciens blocs : maximum (`allowMultiple: false` = 1, sinon 5) et type normalisés comme côté PHP. */
const FileInput: FC<any> = (props) => (
    <FileInputBlock
        {...props}
        acceptedFile={fileKind(props.acceptedFile)}
        maxItems={props.maxItems ?? (props.allowMultiple === false ? 1 : 5)}
    />
);

export default FileInput;
