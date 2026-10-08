// Entoure le builder et le champ caché d'un FormStructureType : recopie la structure dans le champ
// à chaque changement, et demande confirmation à la soumission si des champs supprimés vont
// effacer des réponses existantes. Les textes viennent des attributs data-* (traduits côté Twig).
export class FormBuilderManagerElement extends HTMLElement {
    #initialKeys = new Set();
    #currentKeys = new Set();
    #answersCount = 0;

    connectedCallback() {
        const targetElement = this.querySelector('input[type="hidden"]');
        this.#answersCount = parseInt(this.dataset.answersCount ?? '0', 10);

        const formBuilder = this.querySelector('form-builder');
        if (formBuilder) {
            const initialJson = formBuilder.getAttribute('json');
            if (initialJson) {
                try {
                    const structure = JSON.parse(initialJson);
                    this.#initialKeys = new Set(this.#collectKeys(structure));
                    this.#currentKeys = new Set(this.#initialKeys);
                } catch {}
            }

            formBuilder.addEventListener('change', (e) => {
                if (e.detail !== undefined) {
                    if (targetElement) {
                        targetElement.value = JSON.stringify(e.detail);
                    }
                    this.#currentKeys = new Set(this.#collectKeys(e.detail));
                }
            });
        }

        const form = this.closest('form');
        if (form) {
            form.addEventListener('submit', (e) => {
                const removedCount = [...this.#initialKeys].filter((k) => !this.#currentKeys.has(k)).length;
                if (removedCount > 0 && this.#answersCount > 0 && !window.confirm(this.#confirmMessage(removedCount))) {
                    e.preventDefault();
                }
            });
        }
    }

    #confirmMessage(removedCount) {
        const plural = (count, one, many) => (count > 1 ? many.replace('{count}', count) : one);

        return this.dataset.confirmTemplate
            .replace('{fields}', plural(removedCount, this.dataset.fieldsOne, this.dataset.fieldsMany))
            .replace('{answers}', plural(this.#answersCount, this.dataset.answersOne, this.dataset.answersMany));
    }

    #collectKeys(blocks) {
        const keys = [];
        if (!Array.isArray(blocks)) return keys;
        for (const block of blocks) {
            if (block.id) keys.push(block.id);
            if (Array.isArray(block.children)) {
                keys.push(...this.#collectKeys(block.children));
            }
        }
        return keys;
    }
}

customElements.define('form-builder-manager', FormBuilderManagerElement);
