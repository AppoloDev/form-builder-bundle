import { Controller } from '@hotwired/stimulus';
import { isConditionActive, readSelectedLabels } from '../lib/condition';

// Affiche ou masque un champ conditionnel selon la valeur choisie dans son champ propriétaire. Les champs
// masqués sont désactivés pour ne pas être envoyés (ni bloquer la validation du navigateur).
export default class extends Controller {
    static values = { owner: String, operator: String, optionLabel: String };

    connect() {
        this.ownerElement = document.getElementById(this.ownerValue);
        if (!this.ownerElement) {
            return;
        }

        this.onChange = () => this.refresh();
        this.ownerElement.addEventListener('change', this.onChange);
        this.refresh();
    }

    disconnect() {
        this.ownerElement?.removeEventListener('change', this.onChange);
    }

    refresh() {
        const active = isConditionActive(readSelectedLabels(this.ownerElement), this.operatorValue, this.optionLabelValue);

        this.element.hidden = !active;
        this.element.querySelectorAll('input, select, textarea').forEach((control) => {
            if (active && control.dataset.conditionDisabled) {
                control.disabled = false;
                delete control.dataset.conditionDisabled;
            } else if (!active && !control.disabled) {
                control.disabled = true;
                control.dataset.conditionDisabled = '1';
            }
        });
    }
}
