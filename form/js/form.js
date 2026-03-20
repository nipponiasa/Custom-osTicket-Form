customElements.define('required-field', class extends HTMLElement {
    connectedCallback() {
        this.innerHTML = '<span class="text-danger" aria-hidden="true"> *</span>';
    }
});
