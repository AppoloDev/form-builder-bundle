import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        this.name = this.element.dataset.name;

        this.prototype = this.element.dataset.prototype;
        this.prototypeElement = document.createElement('div');
        this.prototypeElement.innerHTML = this.prototype;

        this.collectionChildSelector = this.element.dataset.collectionChildSelector ?? '.collec_child';

        this.addBtnTemplate = this.prototypeElement.querySelector('#add-btn');
        this.removeBtnTemplate = this.prototypeElement.querySelector('#remove-btn');

        if (this.prototype) {
            this.createAddBtn();
            this.customizePresentElement();

            if (this.element.dataset.addAuto) {
                const buttonElement = this.addButtonContainerElement.querySelector('a, button');
                if (buttonElement) {
                    buttonElement.dispatchEvent(new Event('click'));
                }
            }
        }
    }

    createAddBtn() {
        this.addButtonContainerElement = document.createElement('div');
        this.addButtonContainerElement.innerHTML = this.addBtnTemplate.innerHTML;

        const buttonElement = this.addButtonContainerElement.querySelector('a, button');

        if (buttonElement) {
            buttonElement.addEventListener('click', (ev) => {
                this.insertElementToCollection(ev, this.addButtonContainerElement);
                this.checkcreateAddBtn();
            });

            this.element.appendChild(this.addButtonContainerElement);
        }
        this.checkcreateAddBtn();
    }

    checkcreateAddBtn() {
        if (this.element.dataset.maxItems) {
            let childrenEl = Array.from(this.element.querySelectorAll(this.collectionChildSelector));
            if (childrenEl && this.element.dataset.deleteSelector !== undefined) {
                childrenEl = childrenEl.filter((child) => !child.classList.contains('hidden'));
            }

            if (childrenEl.length >= parseInt(this.element.dataset.maxItems)) {
                this.addButtonContainerElement.classList.add('hidden');
            } else {
                this.addButtonContainerElement.classList.remove('hidden');
            }
        }
    }

    /**
     * @param {Event} ev
     * @param {HTMLDivElement} addButtonContainerElement
     */
    insertElementToCollection(ev, addButtonContainerElement) {
        const collectionElement = document.createElement('div');
        collectionElement.classList.add(this.collectionChildSelector.replace('.', ''));
        this.element.insertBefore(collectionElement, addButtonContainerElement);
        const length = this.getCount();
        collectionElement.setAttribute('data-val', length);
        collectionElement.insertAdjacentHTML('beforeend', this.getReplacedHtml(length));

        this.createRemoveBtn(collectionElement);
        ev.preventDefault();
    }

    customizePresentElement() {
        this.element.querySelectorAll(this.collectionChildSelector).forEach((collectionElement) => {
            this.createRemoveBtn(collectionElement);
        });
    }

    getReplacedHtml(length) {
        let proto = this.prototype;
        return proto.replace(new RegExp(`${this.name ? this.name : '__name__'}`, 'g'), length);
    }

    getCount() {
        let index = -1;

        return Math.max.apply(
            Math,
            Array.from(
                this.element.querySelectorAll(`:scope > ${this.collectionChildSelector}`)
            )
                .map(
                    (o) => o.dataset.val ? o.dataset.val : index++
                )
        ) + 1;
    }

    /**
     * @param {HTMLDivElement} collectionElement
     */
    createRemoveBtn(collectionElement) {
        if (!this.element.dataset.removeBtnTarget) {
            return;
        }

        const removeBtnTarget = collectionElement.querySelector(this.element.dataset.removeBtnTarget);

        if (removeBtnTarget) {
            const removeButtonContainerElement = document.createElement('div');
            removeButtonContainerElement.innerHTML = this.removeBtnTemplate.innerHTML;

            const buttonElement = removeButtonContainerElement.querySelector('a, button');

            buttonElement.addEventListener('click', (ev) => this.remove(ev));

            removeBtnTarget.innerHTML = `<div class="w-full ml-2">${removeBtnTarget.innerHTML}</div>`;
            removeBtnTarget.classList.add('flex');
            removeBtnTarget.classList.add('items-center');
            removeBtnTarget.appendChild(removeButtonContainerElement);
        }
    }

    // Action Stimulus réutilisable, câblable directement depuis n'importe quel bouton de
    // suppression rendu ailleurs (ex. le slot "content" de UI:FileChip pour les fichiers
    // d'une collection) via data-action="click->collection-builder#remove", sans passer
    // par le mécanisme <template id="remove-btn"> ci-dessus.
    remove(event) {
        event.preventDefault();

        const collectionChild = event.target.closest(this.collectionChildSelector);

        if (this.element.dataset.deleteSelector === undefined) {
            collectionChild.remove();
        } else {
            const checkbox = collectionChild.querySelector(this.element.dataset.deleteSelector);
            checkbox.setAttribute('checked', true);
            collectionChild.classList.add('hidden');
        }

        this.checkcreateAddBtn();
    }
}
