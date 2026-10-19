import { createRoot } from "react-dom/client";
import FormBuilder from './builder/FormBuilder'
import { setBuilderLocale } from './builder/i18n'
import { parseList } from './builder/utilities/allowedTypes'

export class FormBuilderElement extends HTMLElement {
    connectedCallback() {
        const attrs = {
            json: []
        };

        Object.values(this.attributes).forEach((item) => {
            switch (item.name) {
                case 'json':
                    attrs[item.name] = JSON.parse(item.value)
                    break;
                case 'categories':
                case 'types':
                    attrs[item.name] = parseList(item.value)
                    break;
                default:
                    attrs[item.name] = item.value;
                    break;
            }
        });

        setBuilderLocale(attrs.locale);

        this.root = createRoot(this);
        this.root.render(
            <FormBuilder
                onChange={(value) => {
                    const changeEvent = new CustomEvent('change', {detail: value});
                    this.dispatchEvent(changeEvent)
                }}
                onClose={(value) => {
                    const closeEvent = new CustomEvent('close', {detail: value});
                    this.dispatchEvent(closeEvent)
                }}
                {...attrs}/>
        );
    }

    disconnectedCallback() {
        this.root.unmount();
    }
}

customElements.define("form-builder", FormBuilderElement);
