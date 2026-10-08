// Logique pure du contrôleur form-builder-condition (testée par vitest).

/**
 * Libellés choisis dans le champ propriétaire : `<select>` (liste déroulante) ou conteneur d'inputs
 * radio / checkbox (choix affichés à plat).
 *
 * @param {Element} owner
 * @returns {string[]}
 */
export const readSelectedLabels = (owner) => {
    if (owner instanceof HTMLSelectElement) {
        return Array.from(owner.selectedOptions, (option) => option.value);
    }

    return Array.from(owner.querySelectorAll('input:checked'), (input) => input.value);
};

/**
 * @param {string[]} selected libellés choisis
 * @param {string} operator 'is' | 'is_not'
 * @param {string} optionLabel libellé visé par la règle
 * @returns {boolean} le champ conditionnel doit-il être affiché ?
 */
export const isConditionActive = (selected, operator, optionLabel) => {
    const isSelected = selected.includes(optionLabel);

    return operator === 'is_not' ? !isSelected : isSelected;
};
