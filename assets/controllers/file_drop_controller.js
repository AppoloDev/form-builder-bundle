import { Controller } from '@hotwired/stimulus';
import { acceptsFile, fileKind, formatBytes, nextIndex } from '../lib/file_drop';

/**
 * Zone de dépôt d'un champ fichier (FileRepeatableType, collection d'entrées `{file, delete}`).
 *
 * Chaque fichier choisi ou déposé devient une entrée de la collection, créée depuis le prototype et dont l'input
 * `type=file` reçoit le fichier (DataTransfer) : le serveur reçoit exactement ce qu'il recevait avant, une entrée
 * par fichier. Une entrée existante (fichier déjà enregistré, `data-existing`) se supprime en cochant sa case
 * `delete` ; une entrée ajoutée ici est simplement retirée du DOM.
 *
 * Le rendu (ligne de fichier, icônes) vient des <template> du thème : ce contrôleur ne porte aucune classe CSS.
 * Emplacements remplis dans une ligne : `data-slot="preview|name|size|inputs"`.
 */
export default class extends Controller {
    static targets = ['zone', 'picker', 'list', 'item', 'header', 'addButton', 'itemTemplate', 'icon'];
    static values = {
        prototype: String,
        name: {type: String, default: '__name__'},
        maxItems: {type: Number, default: 0},
        accept: {type: String, default: ''},
    };

    connect() {
        // Un fichier déjà enregistré n'a pas d'input rempli : son `required` bloquerait l'envoi du formulaire.
        this.itemTargets
            .filter((item) => item.hasAttribute('data-existing'))
            .forEach((item) => item.querySelectorAll('input[type="file"]').forEach((input) => { input.required = false; }));
        this.refresh();
    }

    disconnect() {
        this.itemTargets.forEach((item) => this.revokePreview(item));
    }

    browse(event) {
        // Le clic programmatique sur le picker remonte jusqu'ici : l'annuler bloquerait la fenêtre native.
        if (event.target === this.pickerTarget || this.remaining() <= 0) {
            return;
        }
        event.preventDefault();
        this.pickerTarget.click();
    }

    pick() {
        this.addFiles(this.pickerTarget.files);
        this.pickerTarget.value = '';
    }

    dragOver(event) {
        event.preventDefault();
        if (this.remaining() > 0) {
            this.zoneTarget.setAttribute('data-dragging', '');
        }
    }

    dragLeave(event) {
        if (!this.zoneTarget.contains(event.relatedTarget)) {
            this.zoneTarget.removeAttribute('data-dragging');
        }
    }

    drop(event) {
        event.preventDefault();
        this.zoneTarget.removeAttribute('data-dragging');
        this.addFiles(event.dataTransfer?.files ?? []);
    }

    remove(event) {
        event.preventDefault();
        const item = event.target.closest('[data-form-builder-file-drop-target~="item"]');
        if (item) {
            this.removeItem(item);
        }
        this.refresh();
    }

    clear(event) {
        event.preventDefault();
        this.itemTargets.filter((item) => !item.hidden).forEach((item) => this.removeItem(item));
        this.refresh();
    }

    removeItem(item) {
        if (item.hasAttribute('data-existing')) {
            const checkbox = item.querySelector('.input-delete');
            if (checkbox) {
                checkbox.checked = true;
            }
            item.hidden = true;
        } else {
            this.revokePreview(item);
            item.remove();
        }
    }

    addFiles(files) {
        const accepted = Array.from(files).filter((file) => acceptsFile(file, this.acceptValue));
        accepted.slice(0, this.remaining()).forEach((file) => this.listTarget.append(this.createItem(file)));
        this.refresh();
    }

    createItem(file) {
        const index = nextIndex(this.itemTargets.map((item) => item.dataset.val));
        const item = this.itemTemplateTarget.content.firstElementChild.cloneNode(true);
        item.dataset.val = String(index);

        const inputs = item.querySelector('[data-slot="inputs"]');
        inputs.innerHTML = this.prototypeValue.replace(new RegExp(this.nameValue, 'g'), String(index));
        const input = inputs.querySelector('input[type="file"]');
        const transfer = new DataTransfer();
        transfer.items.add(file);
        input.files = transfer.files;

        item.querySelector('[data-slot="name"]').textContent = file.name;
        const size = item.querySelector('[data-slot="size"]');
        if (size) {
            size.textContent = formatBytes(file.size);
        }

        const preview = item.querySelector('[data-slot="preview"]');
        const kind = fileKind(file);
        if (kind === 'image' && file.type.startsWith('image/')) {
            const img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            img.alt = file.name;
            preview.replaceChildren(img);
        } else {
            const icon = this.iconTargets.find((template) => template.dataset.kind === kind)
                ?? this.iconTargets.find((template) => template.dataset.kind === 'file');
            preview.replaceChildren(icon.content.cloneNode(true));
        }

        return item;
    }

    revokePreview(item) {
        const img = item.querySelector('[data-slot="preview"] img');
        if (img?.src.startsWith('blob:')) {
            URL.revokeObjectURL(img.src);
        }
    }

    visibleCount() {
        return this.itemTargets.filter((item) => !item.hidden).length;
    }

    remaining() {
        return this.maxItemsValue > 0 ? this.maxItemsValue - this.visibleCount() : Infinity;
    }

    refresh() {
        if (this.hasHeaderTarget) {
            this.headerTarget.hidden = this.visibleCount() === 0;
        }
        if (this.hasAddButtonTarget) {
            this.addButtonTarget.hidden = this.remaining() <= 0;
        }
    }
}
