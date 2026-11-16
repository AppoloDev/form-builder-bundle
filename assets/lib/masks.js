/**
 * Masques de saisie partagés par le formulaire (contrôleur form-builder-input-mask) et le builder (valeur par
 * défaut et indice des blocs Tel/Email/Url). Le serveur revalide (Regex/Email/Url).
 *
 * `format` s'applique à chaque frappe, `complete` à la sortie du champ.
 */

/** « 06 12 34 56 78 », « +33 6 12 34 56 78 » ; autre indicatif : « +32470123456 » (chiffres seuls). */
function formatTel(value) {
    const international = value.trimStart().startsWith('+');
    const digits = value.replace(/\D/g, '');
    const pairs = (part) => part.match(/.{1,2}/g) ?? [];

    if (!international) {
        return pairs(digits.slice(0, digits.startsWith('0') ? 10 : 15)).join(' ');
    }
    if (digits.startsWith('33')) {
        const national = digits.slice(2, 11);

        return ['+33', national.slice(0, 1), ...pairs(national.slice(1))].filter(Boolean).join(' ');
    }

    return `+${digits.slice(0, 15)}`;
}

/** @typedef {'tel' | 'email' | 'url'} MaskType */

/**
 * @param {MaskType} type
 * @param {string} value
 * @returns {string}
 */
export function formatMask(type, value) {
    switch (type) {
        case 'tel':
            return formatTel(value);
        case 'email':
            return value.replace(/\s/g, '').toLowerCase();
        case 'url':
            return value.replace(/\s/g, '');
        default:
            return value;
    }
}

/**
 * URL sans protocole : « https:// » ajouté (comme `default_protocol` côté serveur).
 *
 * @param {MaskType} type
 * @param {string} value
 * @returns {string}
 */
export function completeMask(type, value) {
    const formatted = formatMask(type, value);
    if ('url' === type && '' !== formatted && !/^[a-z][a-z0-9+.-]*:\/\//i.test(formatted)) {
        return `https://${formatted}`;
    }

    return formatted;
}
