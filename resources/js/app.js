import './bootstrap';
import 'trix';
import 'trix/dist/trix.css';

import Alpine from 'alpinejs';

if (window.Trix?.config?.lang) {
    Object.assign(window.Trix.config.lang, {
        attachFiles: 'Adjuntar archivos',
        bold: 'Negrita',
        bullets: 'Viñetas',
        captionPlaceholder: 'Agregar una leyenda...',
        code: 'Código',
        heading1: 'Encabezado',
        indent: 'Aumentar nivel',
        italic: 'Cursiva',
        link: 'Enlace',
        numbers: 'Numeración',
        outdent: 'Disminuir nivel',
        quote: 'Cita',
        redo: 'Rehacer',
        remove: 'Eliminar',
        strike: 'Tachado',
        undo: 'Deshacer',
        unlink: 'Quitar enlace',
        url: 'URL',
        urlPlaceholder: 'Escribe una URL...',
    });
}

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
            this.open = false;
            return;
        }

        try {
            const response = await fetch(`${this.endpoint}?query=${encodeURIComponent(this.query)}`);

            if (!response.ok) {
                throw new Error(`Search request failed with status ${response.status}`);
            }

            this.results = await response.json();
            this.open = this.results.length > 0;
        } catch (error) {
            this.results = [];
            this.open = false;
            console.error('User search failed', error);
        }
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
            this.open = false;
            return;
        }

        try {
            const response = await fetch(`${this.endpoint}?query=${encodeURIComponent(this.query)}`);

            if (!response.ok) {
                throw new Error(`Search request failed with status ${response.status}`);
            }

            const items = await response.json();
            this.results = items.filter((item) => !this.selected.some((selected) => selected.id === item.id));
            this.open = this.results.length > 0;
        } catch (error) {
            this.results = [];
            this.open = false;
            console.error('Assigned users search failed', error);
        }
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

const defaultPrioritySelectStyles = {
    background: '#ffffff',
    border: '#cbd5e1',
    text: '#0f172a',
};

function applyPrioritySelectTone(select) {
    const option = select.options[select.selectedIndex];
    const background = option?.dataset.priorityBackground ?? defaultPrioritySelectStyles.background;
    const border = option?.dataset.priorityBorder ?? defaultPrioritySelectStyles.border;
    const text = option?.dataset.priorityText ?? defaultPrioritySelectStyles.text;

    select.style.backgroundColor = background;
    select.style.borderColor = border;
    select.style.color = text;
}

function setupPrioritySelects() {
    document.querySelectorAll('[data-priority-select]').forEach((select) => {
        applyPrioritySelectTone(select);
        select.addEventListener('change', () => applyPrioritySelectTone(select));
    });
}

window.Alpine = Alpine;

Alpine.start();

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', setupPrioritySelects, { once: true });
} else {
    setupPrioritySelects();
}
