import { Controller } from '@hotwired/stimulus';
import SignaturePad from 'signature_pad';

export default class extends Controller {
    static targets = ['container'];
    static values = { input: String, clearLabel: String, modifyLabel: String };

    connect() {
        this.inputElement = document.querySelector(this.inputValue);
        this.onResize = () => this.init();
        window.addEventListener('resize', this.onResize);
        this.init();
    }

    disconnect() {
        window.removeEventListener('resize', this.onResize);
    }

    init() {
        this.padContainer?.remove();
        this.imgContainer?.remove();

        if (this.inputElement.value === '') {
            this.createPad();
        } else {
            this.createImage();
        }
    }

    createPad() {
        this.padContainer = document.createElement('div');
        this.padCanvas = document.createElement('canvas');

        this.clearButton = document.createElement('div');
        this.clearButton.className = 'mt-1 cursor-pointer text-xs text-muted-foreground underline hover:text-foreground';
        this.clearButton.innerText = this.clearLabelValue;
        this.clearButton.addEventListener('click', () => {
            this.signaturePad.clear();
            this.inputElement.value = '';
        });

        this.padContainer.appendChild(this.padCanvas);
        this.padContainer.appendChild(this.clearButton);

        this.containerTarget.appendChild(this.padContainer);

        this.signaturePad = new SignaturePad(this.padCanvas, {
            backgroundColor: 'rgb(255,255,255)',
        });

        this.signaturePad.addEventListener('endStroke', () => {
            this.inputElement.value = this.signaturePad.toDataURL('image/svg+xml');
        });

        this.resizeCanvas();
    }

    createImage() {
        this.imgContainer = document.createElement('div');
        this.img = document.createElement('img');
        this.img.src = this.inputElement.value;

        this.resignButton = document.createElement('div');
        this.resignButton.className = 'mt-1 cursor-pointer text-xs text-muted-foreground underline hover:text-foreground';
        this.resignButton.innerText = this.modifyLabelValue;
        this.resignButton.addEventListener('click', () => {
            this.imgContainer.remove();
            this.createPad();
        });

        this.imgContainer.appendChild(this.img);
        this.imgContainer.appendChild(this.resignButton);

        this.containerTarget.appendChild(this.imgContainer);
    }

    resizeCanvas() {
        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        this.padCanvas.width = this.padCanvas.offsetWidth * ratio;
        this.padCanvas.height = this.padCanvas.offsetHeight * ratio;
        this.padCanvas.getContext('2d').scale(ratio, ratio);
    }
}
