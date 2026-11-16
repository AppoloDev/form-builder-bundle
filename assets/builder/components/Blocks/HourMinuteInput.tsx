import { makeInputBlock } from "./GenericInput";

/** Heure courante HH:MM (locale). */
const nowTime = (): string => new Date().toTimeString().slice(0, 5);

const HourMinuteInput = makeInputBlock("time", {
    // « Préremplir avec l'heure actuelle » : prioritaire sur la valeur par défaut, comme dans le formulaire.
    toInputAttrs: (form) => (form.hasCurrentTime ? {value: nowTime()} : {}),
    // Même largeur que le champ heure du formulaire (w-28).
    maxWidth: () => "max-w-28",
});
export default HourMinuteInput;
