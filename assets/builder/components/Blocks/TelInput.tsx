import { makeInputBlock } from "./GenericInput";

// Largeur réduite comme dans le formulaire (thème de l'app) : un numéro n'a pas besoin de max-w-sm.
const TelInput = makeInputBlock("tel", { maxWidth: () => "max-w-48" });
export default TelInput;
