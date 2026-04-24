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

Alpine.data('passwordField', () => ({
    showPassword: false,
    toggle() {
        this.showPassword = !this.showPassword;
    },
}));

Alpine.data('modalDialog', ({ show = false, focusable = false } = {}) => ({
    show,
    focusable,
    init() {
        this.$watch('show', (value) => {
            if (value) {
                document.body.classList.add('overflow-y-hidden');

                if (this.focusable) {
                    window.setTimeout(() => this.firstFocusable()?.focus(), 100);
                }

                return;
            }

            document.body.classList.remove('overflow-y-hidden');
        });

        if (this.show) {
            document.body.classList.add('overflow-y-hidden');
        }
    },
    focusables() {
        const selector = 'a, button, input:not([type=\'hidden\']), textarea, select, details, [tabindex]:not([tabindex=\'-1\'])';

        return [...this.$el.querySelectorAll(selector)]
            .filter((element) => !element.hasAttribute('disabled'));
    },
    firstFocusable() {
        return this.focusables()[0];
    },
    lastFocusable() {
        return this.focusables().slice(-1)[0];
    },
    nextFocusable() {
        return this.focusables()[this.nextFocusableIndex()] || this.firstFocusable();
    },
    prevFocusable() {
        return this.focusables()[this.prevFocusableIndex()] || this.lastFocusable();
    },
    nextFocusableIndex() {
        return (this.focusables().indexOf(document.activeElement) + 1) % (this.focusables().length + 1);
    },
    prevFocusableIndex() {
        return Math.max(0, this.focusables().indexOf(document.activeElement)) - 1;
    },
    open() {
        this.show = true;
    },
    close() {
        this.show = false;
    },
}));

Alpine.data('navigationMenu', () => ({
    open: false,
    toggle() {
        this.open = !this.open;
    },
    close() {
        this.open = false;
    },
}));

Alpine.data('togglePanel', (initialOpen = false) => ({
    open: initialOpen,
    toggle() {
        this.open = !this.open;
    },
    close() {
        this.open = false;
    },
}));

Alpine.data('treeNodeToggle', (initialOpen = false) => ({
    open: initialOpen,
    toggle() {
        this.open = !this.open;
    },
}));

Alpine.data('filterDrawer', (initialOpen = false) => ({
    open: initialOpen,
    toggle() {
        this.open = !this.open;
    },
    show() {
        this.open = true;
    },
    close() {
        this.open = false;
    },
}));

Alpine.data('expandableList', (initialExpanded = false) => ({
    expanded: initialExpanded,
    toggle() {
        this.expanded = !this.expanded;
    },
}));

Alpine.data('reportOptions', ({ format = 'pdf', view = 'list' } = {}) => ({
    format,
    view,
}));

Alpine.data('timedVisibility', (duration = 2000) => ({
    show: true,
    duration,
    init() {
        window.setTimeout(() => {
            this.show = false;
        }, this.duration);
    },
}));

Alpine.data('systemMetricsForm', ({
    testingStatusIds = [],
    selectedStatusId = '',
    pendingErrors = 0,
    errorsInProgress = 0,
    inReview = 0,
    finalizedCount = 0,
} = {}) => ({
    testingStatusIds,
    selectedStatusId,
    pendingErrors,
    errorsInProgress,
    inReview,
    finalizedCount,
    init() {
        if (this.$refs.statusSelect) {
            this.selectedStatusId = this.$refs.statusSelect.value;
        }
    },
    isTestingStatus() {
        return this.testingStatusIds.includes(String(this.selectedStatusId));
    },
    totalTrelloCards() {
        return (Number(this.pendingErrors) || 0)
            + (Number(this.errorsInProgress) || 0)
            + (Number(this.inReview) || 0)
            + (Number(this.finalizedCount) || 0);
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
