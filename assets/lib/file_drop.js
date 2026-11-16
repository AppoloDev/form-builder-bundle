/**
 * Vrai si le fichier correspond à l'attribut `accept` (liste séparée par des virgules de types MIME, `type/*` ou
 * extensions `.ext`). `accept` vide : tout est accepté. Le serveur revalide de toute façon (contrainte File).
 *
 * @param {{name: string, type: string}} file
 * @param {string} accept
 */
export function acceptsFile(file, accept) {
    const rules = (accept ?? '').split(',').map((rule) => rule.trim().toLowerCase()).filter(Boolean);
    if (rules.length === 0) {
        return true;
    }

    const type = (file.type ?? '').toLowerCase();
    const name = (file.name ?? '').toLowerCase();

    return rules.some((rule) => {
        if (rule.startsWith('.')) {
            return name.endsWith(rule);
        }
        if (rule.endsWith('/*')) {
            return type.startsWith(rule.slice(0, -1));
        }
        return type === rule;
    });
}

/**
 * Index de la prochaine entrée de la collection : après le plus grand index existant (entrées masquées comprises,
 * pour ne jamais réutiliser celui d'un fichier marqué à supprimer).
 *
 * @param {Array<string|undefined>} indexes
 */
export function nextIndex(indexes) {
    const numbers = indexes.map((index) => Number.parseInt(index ?? '', 10)).filter((n) => Number.isInteger(n));

    return numbers.length === 0 ? 0 : Math.max(...numbers) + 1;
}

const KINDS = {
    image: ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif', 'heic', 'heif', 'bmp', 'svg'],
    pdf: ['pdf'],
    doc: ['doc', 'docx', 'odt', 'rtf', 'txt'],
    sheet: ['xls', 'xlsx', 'ods', 'csv'],
    slides: ['ppt', 'pptx', 'odp'],
};

/**
 * Famille d'un fichier pour son icône : image, pdf, doc, sheet, slides ou file (même table que le thème,
 * bloc file_repeatable_child_row).
 *
 * @param {{name: string, type: string}} file
 */
export function fileKind(file) {
    if ((file.type ?? '').startsWith('image/')) {
        return 'image';
    }
    const extension = (file.name ?? '').split('.').pop().toLowerCase();

    return Object.keys(KINDS).find((kind) => KINDS[kind].includes(extension)) ?? 'file';
}

/** « 512 o », « 1,2 Ko », « 3,4 Mo »… */
export function formatBytes(bytes, locale = document.documentElement.lang || 'fr') {
    const units = locale.startsWith('fr') ? ['o', 'Ko', 'Mo', 'Go'] : ['B', 'KB', 'MB', 'GB'];
    let value = bytes;
    let unit = 0;
    while (value >= 1024 && unit < units.length - 1) {
        value /= 1024;
        unit++;
    }

    return `${value.toLocaleString(locale, {maximumFractionDigits: unit === 0 ? 0 : 1})} ${units[unit]}`;
}
