import { Controller } from '@hotwired/stimulus';

// Nécessite que l'application charge l'API Google Maps Places et émette `google-maps:ready` (ou
// positionne `window.googleMapsReady`).
export default class extends Controller {
    connect() {
        if (window.googleMapsReady) {
            this.initAutocomplete();
            return;
        }

        this.onReady = () => this.initAutocomplete();
        document.addEventListener('google-maps:ready', this.onReady, { once: true });
    }

    disconnect() {
        document.removeEventListener('google-maps:ready', this.onReady);
    }

    initAutocomplete() {
        const input = this.element.querySelector('input');
        if (input) {
            new google.maps.places.Autocomplete(input, {});
        }
    }
}
