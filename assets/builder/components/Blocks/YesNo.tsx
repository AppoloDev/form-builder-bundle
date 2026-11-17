import { makeInputBlock } from "./GenericInput";
import { Button } from "../ui/button";

// Aperçu : les deux boutons du formulaire, libellés repris de la configuration.
const YesNo = makeInputBlock("radio", {
    renderPreview: (form) => (
        <div inert className="flex flex-wrap gap-2">
            {[form.yesLabel, form.noLabel].map((label, i) => (
                <Button key={i} type="button" variant="outline" size="lg" className="min-w-28 px-4">{label}</Button>
            ))}
        </div>
    ),
});
export default YesNo;
