import './bootstrap';
import 'trix';
import 'trix/dist/trix.css';

import Alpine from 'alpinejs';

Alpine.data('searchSelect', ({ endpoint, selectedId = null, selectedLabel = '', placeholder = 'Buscar...' }) => ({
    endpoint,
    selectedId,
    selectedLabel,
    query: '',
    placeholder,
    results: [],
    open: false,
    async search() {
        if (this.query.length < 1) {
            this.results = [];
            return;
        }

        const response = await fetch(`${this.endpoint}?query=${encodeURIComponent(this.query)}`);
        this.results = await response.json();
        this.open = true;
    },
    choose(item) {
        this.selectedId = item.id;
        this.selectedLabel = item.label;
        this.query = '';
        this.results = [];
        this.open = false;
    },
    clear() {
        this.selectedId = null;
        this.selectedLabel = '';
        this.query = '';
        this.results = [];
    },
}));

Alpine.data('searchMultiSelect', ({ endpoint, selected = [] }) => ({
    endpoint,
    query: '',
    results: [],
    selected,
    open: false,
    async search() {
        if (this.query.length < 1) {
            this.results = [];
            return;
        }

        const response = await fetch(`${this.endpoint}?query=${encodeURIComponent(this.query)}`);
        const items = await response.json();
        this.results = items.filter((item) => !this.selected.some((selected) => selected.id === item.id));
        this.open = true;
    },
    add(item) {
        this.selected.push(item);
        this.results = [];
        this.query = '';
        this.open = false;
    },
    remove(id) {
        this.selected = this.selected.filter((item) => item.id !== id);
    },
}));

window.Alpine = Alpine;

Alpine.start();
