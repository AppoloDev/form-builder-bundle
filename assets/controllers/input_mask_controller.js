import { Controller } from '@hotwired/stimulus';
import { completeMask, formatMask } from '../lib/masks';

/**
 * Masque de saisie d'un champ téléphone, e-mail ou URL (valeur `type`), posé par TelInput/EmailInput/UrlInput.
 * Mise en forme à chaque frappe, complément (https://) à la sortie du champ.
 */
export default class extends Controller {
    static values = {type: String};

    connect() {
        this.element.value = formatMask(this.typeValue, this.element.value);
    }

    format() {
        // ponytail: le curseur revient en fin de champ après une correction au milieu ; suffisant pour une saisie
        // de gauche à droite.
        this.element.value = formatMask(this.typeValue, this.element.value);
    }

    complete() {
        this.element.value = completeMask(this.typeValue, this.element.value);
    }
}
