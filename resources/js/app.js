import './bootstrap';
import axios from 'axios';

window.axios = axios;

// Navegación SPA optimizada y simplificada
class SimpleSPANavigation {
    constructor() {
        this.cache = new Map();
        this.currentPage = window.location.pathname;
        this.isLoading = false;
        this.menuTimeouts = new Map(); // Para manejar los delays del hover
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.updateActiveMenuItem();
        this.preloadCriticalPages();
    }

    // !! ========================================================== !!
    // !! FUNCIÓN setupEventListeners REESCRITA PARA HOVER !!
    // !! ========================================================== !!
    setupEventListeners() {
        
        // --- 1. Lógica de Hover para Menús ---
        const menuItems = document.querySelectorAll('.menu li.has-submenu');
        
        menuItems.forEach(item => {
            const timeoutDelay = 200; // Delay de 200ms antes de cerrar

            item.addEventListener('mouseenter', () => {
                // Limpia cualquier "timeout" de cierre pendiente
                if (this.menuTimeouts.has(item)) {
                    clearTimeout(this.menuTimeouts.get(item));
                    this.menuTimeouts.delete(item);
                }
                
                // Abre este menú
                item.classList.add('open');
                
                // Cierra los hermanos (menús del mismo nivel)
                const siblings = this.getSiblings(item);
                siblings.forEach(sibling => {
                    if (sibling.classList && sibling.classList.contains('has-submenu')) {
                        sibling.classList.remove('open');
                    }
                });
            });

            item.addEventListener('mouseleave', () => {
                // Inicia un "timeout" para cerrar este menú
                const timeoutId = setTimeout(() => {
                    item.classList.remove('open');
                }, timeoutDelay);
                this.menuTimeouts.set(item, timeoutId);
            });
        });

        // --- 2. Lógica de Clics (Solo para Navegación y Clic-Afuera) ---
        document.addEventListener('click', (e) => {
            if (!(e.target instanceof Element)) return;

            // A. Clic FUERA del menú: Cierra todos los menús
            if (!e.target.closest('.menu')) {
                document.querySelectorAll('.menu .has-submenu.open').forEach(openSubmenu => {
                    openSubmenu.classList.remove('open');
                });
            }
            
            // B. Clic DENTRO de un enlace (Navegación SPA)
            const link = e.target.closest('.menu a');
            // Solo navega si el enlace NO es el padre de un submenú (ej. no es "Ajustes")
            const isSubmenuToggle = link && link.parentElement.classList.contains('has-submenu');
            
            if (link && !isSubmenuToggle && this.shouldIntercept(link)) {
                e.preventDefault();
                this.setImmediateActivate(link);
                // Cerrar todos los submenús al elegir una opción (ej. Carreras) para que no se queden abiertos
                document.querySelectorAll('.menu .has-submenu.open').forEach(openSubmenu => {
                    openSubmenu.classList.remove('open');
                });
                this.navigate(link.href);
            }
        });

        // Manejar botón atrás/adelante del navegador
        window.addEventListener('popstate', (e) => {
            if (!(e.target instanceof Element)) return;
            if (e.state && e.state.page) {
                this.loadPage(e.state.page, false);
            }
            this.navigate(window.location.href);
        });

        // Precargar al hacer hover (optimizado)
        let hoverTimeout;
        document.addEventListener('mouseenter', (e) => {
            if (!(e.target instanceof Element)) return;
            const link = e.target.closest('.menu a');
            if (link && this.shouldIntercept(link)) {
                clearTimeout(hoverTimeout);
                hoverTimeout = setTimeout(() => {
                    this.preloadPage(link.href);
                }, 200);
            }
        }, true);

        document.addEventListener('mouseleave', (e) => {
            if (!(e.target instanceof Element)) return;
            const link = e.target.closest('.menu a');
            if (link) {
                clearTimeout(hoverTimeout);
            }
        }, true);
    }
    getSiblings(elem) {
        let siblings = [];
        if (!elem.parentNode) return siblings;
        let sibling = elem.parentNode.firstChild;
        while (sibling) {
            if (sibling.nodeType === 1 && sibling !== elem) {
                siblings.push(sibling);
            }
            sibling = sibling.nextSibling;
        }
        return siblings;
    }

    setImmediateActivate(link) {
        document.querySelectorAll('.menu li').forEach(li => {
            li.classList.remove('spa-activating');
        });
        const li = link.closest('li');
        if (li) {
            document.querySelectorAll('.menu li').forEach(other => {
                if (other !== li) other.classList.remove('active');
            });
            li.classList.add('active', 'spa-activating');
        }
    }

    shouldIntercept(link) {
        return link.hostname === window.location.hostname && 
                !link.hasAttribute('data-no-intercept') &&
                !link.href.includes('logout') &&
                !link.href.includes('#') &&
                !link.closest('.brand'); 
    }

    async navigate(url) {
        if (this.isLoading || url === window.location.href) return;
        this.isLoading = true;
        try {
            const content = await this.loadPage(url, true);
            if (content) {
                this.updatePage(content, url);
                this.updateActiveMenuItem();
            }
        } catch (error) {
            console.error('Navigation error:', error);
            this.handleNavigationError(error, url);
        } finally {
            this.isLoading = false;
        }
    }

async loadPage(url, updateHistory = true) {
        const cacheKey = this.getCacheKey(url);
        
        // LISTA NEGRA: Estas páginas NUNCA se guardan en memoria
        const noCachePaths = ['/facturacion']; 
        const currentPath = new URL(url, window.location.origin).pathname;
        
        // Si la URL contiene algo de la lista negra, NO usamos caché
        const shouldUseCache = !noCachePaths.some(path => currentPath.includes(path));

        if (shouldUseCache && this.cache.has(cacheKey)) {
            const cached = this.cache.get(cacheKey);
            if (Date.now() - cached.timestamp < 300000) { 
                if (updateHistory) history.pushState({ page: url }, '', url);
                return cached.content;
            }
        }
        
        // Si es facturación, esto pedirá datos frescos al servidor
        try {
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 10000);
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html',
                    'X-CSRF-TOKEN': this.getCSRFToken(),
                    'Cache-Control': 'no-cache' // Forzar al servidor
                },
                signal: controller.signal
            });
            clearTimeout(timeoutId);
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            
            const html = await response.text();
            const content = this.parsePageContent(html);
            if (!content) throw new Error('Invalid structure');

            // Guardamos en caché (pero la próxima vez el 'shouldUseCache' lo ignorará si es necesario)
            this.cache.set(cacheKey, { content: content, timestamp: Date.now() });
            if (this.cache.size > 15) this.cleanupCache();

            if (updateHistory) history.pushState({ page: url }, content.title, url);
            return content;
        } catch (error) {
            if (error.name === 'AbortError') throw new Error('Timeout');
            throw error;
        }
    }

    parsePageContent(html) {
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const mainContent = doc.querySelector('#main-content, .main-content');
        const title = doc.querySelector('title')?.textContent || '';
        if (!mainContent) {
            return null;
        }
        return {
            main: mainContent.innerHTML,
            title: title,
            scripts: this.extractScripts(mainContent)
        };
    }

    extractScripts(container) {
        const scripts = [];
        const scriptElements = container.querySelectorAll('script');
        scriptElements.forEach(script => {
            if (script.src) {
                scripts.push({ type: 'external', src: script.src });
            } else if (script.textContent.trim()) {
                scripts.push({ type: 'inline', content: script.textContent });
            }
        });
        return scripts;
    }

    updatePage(content, url) {
        const mainElement = document.querySelector('#main-content, .main-content');
        if (mainElement) {
            mainElement.style.transition = 'opacity 0.2s ease';
            mainElement.style.opacity = '0.7';
            setTimeout(() => {
                mainElement.innerHTML = content.main;
                mainElement.style.opacity = '1';
                this.executeScripts(content.scripts);
                if (typeof window.initScheduleFormIfNeeded === 'function') window.initScheduleFormIfNeeded();
                if (mainElement.querySelector('#schedule_form') && typeof window.initScheduleClockPickersForContainer === 'function') {
                    window.initScheduleClockPickersForContainer(mainElement);
                }
                if (typeof window.initTeacherHorariosModal === 'function') window.initTeacherHorariosModal();
                if (typeof window.initTeacherViewModal === 'function') window.initTeacherViewModal();
                if (typeof window.initTeacherEditModal === 'function') window.initTeacherEditModal();
                this.scrollToTop();
            }, 100);
        }
        document.title = content.title;
        this.currentPage = url;
    }

    executeScripts(scripts) {
        scripts.forEach(script => {
            if (script.type === 'external') {
                if (!document.querySelector(`script[src="${script.src}"]`)) {
                    const newScript = document.createElement('script');
                    newScript.src = script.src;
                    newScript.async = true;
                    document.head.appendChild(newScript);
                }
            } else {
                try {
                    new Function(script.content)();
                } catch (e) {
                    console.warn('Script execution failed:', e);
                }
            }
        });
    }

    updateActiveMenuItem() {
        const currentPath = new URL(window.location.href).pathname.replace(/\/+$/, '') || '/';
        document.querySelectorAll('.menu a').forEach(link => {
            const li = link.closest('li');
            if (!li) return;
            const href = link.getAttribute('href') || '#';
            let linkPath = '/';
            try {
                linkPath = new URL(href, window.location.origin).pathname.replace(/\/+$/, '') || '/';
            } catch (e) {
                linkPath = href.replace(/\/+$/, '') || '/';
            }
            const isMatch = (linkPath === currentPath) || 
                            (linkPath !== '/' && currentPath.startsWith(linkPath + '/')) || 
                            (linkPath !== '/' && currentPath === linkPath);
            if (isMatch) {
                if (!li.classList.contains('active')) {
                    li.classList.add('active');
                }
                li.classList.remove('spa-activating');
                // Marcar también los padres (has-submenu) para que la rama quede activa al reabrir
                let parent = li.parentElement?.closest('li.has-submenu');
                while (parent) {
                    parent.classList.add('active');
                    parent = parent.parentElement?.closest('li.has-submenu');
                }
            } else {
                if (!li.classList.contains('spa-activating')) {
                    li.classList.remove('active');
                }
            }
        });
    }

    preloadCriticalPages() {
        const criticalPages = ['/cursos', '/mi-informacion', '/ajustes'];
        setTimeout(() => {
            criticalPages.forEach(page => {
                if (page !== this.currentPage) {
                    this.preloadPage(window.location.origin + page);
                }
            });
        }, 3000);
    }

    async preloadPage(url) {
        if (!this.cache.has(this.getCacheKey(url)) && !this.isLoading) {
            try {
                await this.loadPage(url, false);
            } catch (error) {
                // Silenciar errores de precarga
            }
        }
    }

    handleNavigationError(error, url) {
        console.error('Navigation failed:', error);
        setTimeout(() => {
            window.location.href = url;
        }, 1000);
    }

    scrollToTop() {
        const mainContent = document.querySelector('.main-content');
        if (mainContent) {
            mainContent.scrollTo({ top: 0, behavior: 'smooth' });
        }
    }

    getCacheKey(url) {
        return new URL(url).pathname;
    }

    getCSRFToken() {
        return document.querySelector('meta[name="csrf-token"]')?.content || '';
    }

    cleanupCache() {
        const entries = Array.from(this.cache.entries())
            .sort((a, b) => b[1].timestamp - a[1].timestamp)
            .slice(0, 10);
        this.cache.clear();
        entries.forEach(([key, value]) => {
            this.cache.set(key, value);
        });
    }

    clearCache() {
        this.cache.clear();
    }

    navigateTo(url) {
        this.navigate(url);
    }
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    window.spaNav = new SimpleSPANavigation();
    window.navigateTo = (url) => window.spaNav.navigateTo(url);
});

// Limpiar al cerrar
window.addEventListener('beforeunload', () => {
    if (window.spaNav) {
        window.spaNav.clearCache();
    }
});

// --- Manejo permanente del botón context-switcher ---
(function initializeContextSwitcher() {
    if (initializeContextSwitcher._initialized) return;
    initializeContextSwitcher._initialized = true;
    document.addEventListener('click', (event) => {
        const button = event.target.closest('#context-switcher-button');
        const menu = document.getElementById('context-switcher-menu');
        if (button) {
            event.stopPropagation();
            if (menu) {
                menu.classList.toggle('show');
            }
            return;
        }
        if (menu && menu.classList.contains('show')) {
            menu.classList.remove('show');
        }
    });
})();

// --- Delegación: botón "+ Agregar alumnos" (modal inscripción) — funciona con SPA sin refrescar ---
window.cerrarModalInscripcion = function () {
    const modal = document.getElementById('modalInscripcion');
    const iframe = document.getElementById('iframeInscripcion');
    if (modal) modal.style.display = 'none';
    if (iframe) iframe.src = 'about:blank';
};
document.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-action="open-modal-inscripcion"]');
    if (!btn) return;
    const modal = document.getElementById('modalInscripcion');
    const iframe = document.getElementById('iframeInscripcion');
    if (!modal || !iframe) return;
    e.preventDefault();
    e.stopPropagation();
    const url = btn.getAttribute('data-inscription-url');
    if (url) iframe.src = url;
    modal.style.display = 'flex';
}, true);
document.addEventListener('click', (e) => {
    if (e.target.id === 'modalInscripcion') window.cerrarModalInscripcion();
});
document.addEventListener('click', (e) => {
    if (e.target.closest('#modalInscripcion .modal-close')) {
        e.preventDefault();
        window.cerrarModalInscripcion();
    }
});

// --- Delegación: botón "Agregar Carrera" (funciona con SPA al reemplazar contenido) ---
document.addEventListener('click', (e) => {
    const btn = e.target.closest('#openCreateCareerBtn');
    if (!btn) return;
    const modal = document.getElementById('createCareerModal');
    if (modal) modal.style.display = 'flex';
});

// --- Delegación: botón "Agregar Materia" (funciona con SPA al reemplazar contenido) ---
document.addEventListener('click', (e) => {
    const btn = e.target.closest('#openCreateMateriaBtn');
    if (!btn) return;
    const modal = document.getElementById('createMateriaModal');
    if (modal) {
        modal.style.display = 'flex';
        const sel = document.getElementById('carrera_id');
        if (sel) sel.classList.toggle('placeholder', sel.value === '');
    }
});

// --- Delegación: botón "Agregar Docente" (modal en layout; cargar formulario por AJAX para que funcione con SPA) ---
document.addEventListener('click', (e) => {
    const btn = e.target.closest('#btnAgregarDocente, [data-modal="modalRegistroDocente"]');
    if (!btn) return;
    const modal = document.getElementById('modalRegistroDocente');
    const content = document.getElementById('modalRegistroDocenteContent');
    if (!modal || !content) return;
    e.preventDefault();
    e.stopPropagation();
    const formUrl = btn.getAttribute('data-registro-docente-form-url');
    if (!formUrl) return;
    fetch(formUrl, { method: 'GET', headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' } })
        .then((res) => { if (!res.ok) throw new Error('HTTP ' + res.status); return res.text(); })
        .then((html) => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const formWrap = doc.querySelector('.form-body') || doc.querySelector('.form-container') || doc.querySelector('#main-content .container');
            content.innerHTML = formWrap ? formWrap.innerHTML : (doc.querySelector('#main-content')?.innerHTML || '');
            modal.style.display = 'flex';
            modal.classList.add('is-visible');
            modal.setAttribute('aria-hidden', 'false');
        })
        .catch((err) => {
            console.error(err);
            content.innerHTML = '<div style="padding: 1rem; color:#b00020;">No se pudo cargar el formulario.</div>';
            modal.style.display = 'flex';
            modal.classList.add('is-visible');
            modal.setAttribute('aria-hidden', 'false');
        });
}, true);
// Envío del formulario de registro de docente (dentro del modal)
document.addEventListener('submit', (e) => {
    const form = e.target && e.target.closest && e.target.closest('#modalRegistroDocente') && e.target.tagName === 'FORM' ? e.target : null;
    if (!form || form.id !== 'form-registro-docente') return;
    e.preventDefault();
    e.stopPropagation();
    const modal = document.getElementById('modalRegistroDocente');
    const content = document.getElementById('modalRegistroDocenteContent');
    const formData = new FormData(form);
    const action = form.getAttribute('action');
    if (!action) return;
    fetch(action, {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' },
        redirect: 'follow'
    }).then((res) => res.text()).then((html) => {
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const newMain = doc.querySelector('#main-content, .main-content');
        modal.style.display = 'none';
        modal.classList.remove('is-visible');
        modal.setAttribute('aria-hidden', 'true');
        const main = document.querySelector('#main-content, .main-content');
        if (main && newMain) {
            main.innerHTML = newMain.innerHTML;
        }
        document.title = doc.querySelector('title')?.textContent || document.title;
        if (window.spaNav) window.spaNav.updateActiveMenuItem();
    }).catch((err) => {
        console.error(err);
        if (content) content.innerHTML = '<div style="padding: 1rem; color:#b00020;">Error al guardar. Intente de nuevo.</div>';
    });
}, true);

// --- Select carrera_id / semestre_id / materia_id / clase_id: color placeholder #ACACAC ---
document.addEventListener('change', (e) => {
    if (e.target.id === 'carrera_id') {
        e.target.classList.toggle('placeholder', e.target.value === '');
    }
    if (e.target.id === 'semestre_id') {
        e.target.classList.toggle('placeholder', e.target.value === '');
    }
    if (e.target.id === 'materia_id') {
        e.target.classList.toggle('placeholder', e.target.value === '');
    }
    if (e.target.id === 'clase_id') {
        e.target.classList.toggle('placeholder', e.target.value === '');
    }
});
document.addEventListener('DOMContentLoaded', () => {
    const sel = document.getElementById('carrera_id');
    if (sel) sel.classList.toggle('placeholder', sel.value === '');
    const selSemestre = document.getElementById('semestre_id');
    if (selSemestre) selSemestre.classList.toggle('placeholder', selSemestre.value === '');
    const selMateria = document.getElementById('materia_id');
    if (selMateria) selMateria.classList.toggle('placeholder', selMateria.value === '');
    const selClase = document.getElementById('clase_id');
    if (selClase) selClase.classList.toggle('placeholder', selClase.value === '');
});

// --- Delegación: botón "Ver" materia (solo lectura) ---
document.addEventListener('click', (e) => {
    const btn = e.target.closest('.data-btn-view[data-view-materia-id]');
    if (!btn) return;
    const id = btn.getAttribute('data-view-materia-id');
    const modal = document.getElementById('viewMateriaModal_' + id);
    if (modal) modal.style.display = 'flex';
});

// --- Delegación: botón "Editar" materia ---
document.addEventListener('click', (e) => {
    const btn = e.target.closest('.data-btn-edit[data-materia-id]');
    if (!btn) return;
    const id = btn.getAttribute('data-materia-id');
    const modal = document.getElementById('editMateriaModal_' + id);
    if (modal) modal.style.display = 'flex';
});

// --- Delegación: botón cerrar modal (#closeModalBtn / .close-custom) ---
document.addEventListener('click', (e) => {
    const closeBtn = e.target.closest('#closeModalBtn, .close-custom, .btn-close-view');
    if (!closeBtn) return;
    const modal = closeBtn.closest('.modal-overlay');
    if (!modal) return;
    modal.classList.remove('is-visible');
    modal.style.display = 'none';
});

// --- Lista de Docentes: modal "Horarios" (reloj) sin navegar ---
function initTeacherHorariosModal() {
    if (initTeacherHorariosModal._initialized) return;
    initTeacherHorariosModal._initialized = true;
    async function fetchHtml(url) {
        const res = await fetch(url, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html',
            }
        });
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        return await res.text();
    }

    function getModalEls() {
        const modal = document.getElementById('teacherHorariosModal');
        const title = document.getElementById('teacherHorariosModalTitle');
        const content = document.getElementById('teacherHorariosModalContent');
        return { modal, title, content };
    }

    function openModal() {
        const { modal } = getModalEls();
        if (!modal) return;
        modal.style.display = 'flex';
        modal.classList.add('is-visible');
    }

    function closeModal() {
        const { modal } = getModalEls();
        if (!modal) return;
        modal.classList.remove('is-visible');
        modal.style.display = 'none';
    }

    async function loadIntoModal(url) {
        const { content } = getModalEls();
        if (!content) return;
        content.innerHTML = '<div style="padding: 1rem; color:#555;">Cargando...</div>';
        try {
            const html = await fetchHtml(url);
            content.innerHTML = html;
        } catch (err) {
            console.error(err);
            content.innerHTML = '<div style="padding: 1rem; color:#b00020;">No se pudo cargar el contenido.</div>';
        }
    }

    // Abrir modal desde el botón del reloj: cargar contenido primero, luego abrir (sin pantalla "Cargando...")
    document.addEventListener('click', (e) => {
        if (!(e.target instanceof Element)) return;
        const btn = e.target.closest('.data-btn-clock[data-teacher-horarios-url]');
        if (!btn) return;
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();
        const url = btn.getAttribute('data-teacher-horarios-url');
        const teacherName = (btn.getAttribute('data-teacher-name') || '').trim();
        const { title, content } = getModalEls();
        if (title) title.textContent = 'Horario de Docente';
        if (!url) return;
        fetch(url, { method: 'GET', headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' } })
            .then((res) => { if (!res.ok) throw new Error('HTTP ' + res.status); return res.text(); })
            .then((html) => {
                if (content) content.innerHTML = html;
                openModal();
            })
            .catch((err) => {
                console.error(err);
                if (content) content.innerHTML = '<div style="padding: 1rem; color:#b00020;">No se pudo cargar el contenido.</div>';
                openModal();
            });
    }, true);

    // Abrir modal desde el botón del reloj en lista de Alumnos (mismo diseño que Horario de Docente)
    document.addEventListener('click', (e) => {
        if (!(e.target instanceof Element)) return;
        const btn = e.target.closest('.data-btn-clock-student[data-student-horarios-url]');
        if (!btn) return;
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();
        const url = btn.getAttribute('data-student-horarios-url');
        const { title, content } = getModalEls();
        if (title) title.textContent = 'Horario de Alumno';
        if (!url) return;
        fetch(url, { method: 'GET', headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' } })
            .then((res) => { if (!res.ok) throw new Error('HTTP ' + res.status); return res.text(); })
            .then((html) => {
                if (content) content.innerHTML = html;
                openModal();
            })
            .catch((err) => {
                console.error(err);
                if (content) content.innerHTML = '<div style="padding: 1rem; color:#b00020;">No se pudo cargar el contenido.</div>';
                openModal();
            });
    }, true);

    // Cerrar al hacer clic fuera (overlay) o con Escape
    document.addEventListener('click', (e) => {
        if (!(e.target instanceof Element)) return;
        const { modal } = getModalEls();
        if (!modal) return;
        if (e.target === modal) closeModal();
    });
    document.addEventListener('keydown', (e) => {
        if (e.key !== 'Escape') return;
        const { modal } = getModalEls();
        if (modal && modal.style.display !== 'none') closeModal();
    });

    // Al exportar (imprimir) desde el modal Horario de Docente, marcar body para que las reglas
    // @media print solo oculten el resto de la página y muestren este modal (evita que en Mi Información > Horario
    // al exportar solo se vea "Cargando...").
    document.addEventListener('click', (e) => {
        if (!(e.target instanceof Element)) return;
        const btn = e.target.closest('.teacher-horario-modal-export-btn');
        if (!btn || !document.getElementById('teacherHorariosModal')?.contains(btn)) return;
        document.body.classList.add('print-teacher-horario');
        window.print();
    }, true);
}
window.initTeacherHorariosModal = initTeacherHorariosModal;
initTeacherHorariosModal();

window.addEventListener('afterprint', () => {
    document.body.classList.remove('print-teacher-horario');
});

// --- Lista de Docentes: modal "Ver" (ojo) sin navegar ---
function initTeacherViewModal() {
    if (initTeacherViewModal._initialized) return;
    initTeacherViewModal._initialized = true;
    async function fetchHtml(url) {
        const res = await fetch(url, {
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }
        });
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        return await res.text();
    }
    function getViewModalEls() {
        return {
            modal: document.getElementById('teacherViewModal'),
            title: document.getElementById('teacherViewModalTitle'),
            content: document.getElementById('teacherViewModalContent')
        };
    }
    function openViewModal() {
        const { modal } = getViewModalEls();
        if (!modal) return;
        modal.style.display = 'flex';
        modal.classList.add('is-visible');
    }
    function closeViewModal() {
        const { modal } = getViewModalEls();
        if (!modal) return;
        modal.classList.remove('is-visible');
        modal.style.display = 'none';
    }
    document.addEventListener('click', (e) => {
        if (!(e.target instanceof Element)) return;
        const btn = e.target.closest('.data-action-btn.data-btn-view[data-teacher-view-url]');
        if (!btn) return;
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();
        const url = btn.getAttribute('data-teacher-view-url');
        const teacherName = (btn.getAttribute('data-teacher-name') || '').trim();
        const { title, content } = getViewModalEls();
        if (!title || !content) return;
        if (title) title.textContent = 'Informacion del Docente';
        if (!url) return;
        fetchHtml(url).then((html) => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const body = doc.querySelector('.modal-view-career__body') || doc.querySelector('.career-view-dl') || doc.querySelector('#main-content .container');
            content.innerHTML = body ? body.innerHTML : doc.body.innerHTML;
            openViewModal();
        }).catch((err) => {
            console.error(err);
            content.innerHTML = '<div style="padding: 1rem; color:#b00020;">No se pudo cargar la información.</div>';
            openViewModal();
        });
    }, true);
    document.addEventListener('click', (e) => {
        if (!(e.target instanceof Element)) return;
        const { modal } = getViewModalEls();
        if (modal && e.target === modal) closeViewModal();
    });
    document.addEventListener('keydown', (e) => {
        if (e.key !== 'Escape') return;
        const { modal } = getViewModalEls();
        if (modal && modal.classList.contains('is-visible')) closeViewModal();
    });
}
window.initTeacherViewModal = initTeacherViewModal;
initTeacherViewModal();

// --- Lista de Docentes: modal "Editar" sin navegar ---
function initTeacherEditModal() {
    if (initTeacherEditModal._initialized) return;
    initTeacherEditModal._initialized = true;
    const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    function getEditModalEls() {
        return {
            modal: document.getElementById('teacherEditModal'),
            title: document.getElementById('teacherEditModalTitle'),
            content: document.getElementById('teacherEditModalContent')
        };
    }
    function openEditModal() {
        const { modal } = getEditModalEls();
        if (!modal) return;
        modal.style.display = 'flex';
        modal.classList.add('is-visible');
    }
    function closeEditModal() {
        const { modal } = getEditModalEls();
        if (!modal) return;
        modal.classList.remove('is-visible');
        modal.style.display = 'none';
    }
    document.addEventListener('click', (e) => {
        if (!(e.target instanceof Element)) return;
        const btn = e.target.closest('.data-action-btn.data-btn-edit[data-teacher-edit-url]');
        if (!btn) return;
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();
        const url = btn.getAttribute('data-teacher-edit-url');
        const teacherName = (btn.getAttribute('data-teacher-name') || '').trim();
        const { title, content } = getEditModalEls();
        if (!title || !content) return;
        title.textContent = 'Editar informacion';
        if (!url) return;
        fetch(url, { method: 'GET', headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' } })
            .then((res) => { if (!res.ok) throw new Error('HTTP ' + res.status); return res.text(); })
            .then((html) => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const formWrap = doc.querySelector('.form-body') || doc.querySelector('.form-container') || doc.querySelector('#main-content .container');
                content.innerHTML = formWrap ? formWrap.innerHTML : (doc.querySelector('#main-content')?.innerHTML || '');
                openEditModal();
            })
            .catch((err) => {
                console.error(err);
                content.innerHTML = '<div style="padding: 1rem; color:#b00020;">No se pudo cargar el formulario.</div>';
                openEditModal();
            });
    }, true);
    document.addEventListener('submit', (e) => {
        const form = e.target && e.target.closest && e.target.closest('#teacherEditModal') && e.target.tagName === 'FORM' ? e.target : null;
        if (!form) return;
        e.preventDefault();
        e.stopPropagation();
        const formData = new FormData(form);
        const action = form.getAttribute('action');
        if (!action) return;
        const { modal, content } = getEditModalEls();
        fetch(action, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' },
            redirect: 'follow'
        }).then((res) => res.text()).then((html) => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const formWrap = doc.querySelector('.form-body') || doc.querySelector('.form-container');
            if (formWrap) {
                content.innerHTML = formWrap.innerHTML;
                return;
            }
            closeEditModal();
            const main = document.querySelector('#main-content, .main-content');
            const newMain = doc.querySelector('#main-content, .main-content');
            if (main && newMain) {
                const msgSuccess = newMain.querySelector('.message-success');
                if (msgSuccess) msgSuccess.remove();
                main.innerHTML = newMain.innerHTML;
            }
            document.title = doc.querySelector('title')?.textContent || document.title;
            if (typeof window.bindRegistroDocenteModal === 'function') window.bindRegistroDocenteModal();
            if (typeof window.openRegistroDocenteModalIfNeeded === 'function') window.openRegistroDocenteModalIfNeeded();
        }).catch((err) => {
            console.error(err);
            content.innerHTML = '<div style="padding: 1rem; color:#b00020;">Error al guardar. Intente de nuevo.</div>';
        });
    }, true);
    document.addEventListener('click', (e) => {
        if (!(e.target instanceof Element)) return;
        const { modal } = getEditModalEls();
        if (modal && e.target === modal) closeEditModal();
    });
    document.addEventListener('keydown', (e) => {
        if (e.key !== 'Escape') return;
        const { modal } = getEditModalEls();
        if (modal && modal.classList.contains('is-visible')) closeEditModal();
    });
}
window.initTeacherEditModal = initTeacherEditModal;
initTeacherEditModal();

// --- Delegación: botón "Ver" carrera (visualizar) ---
document.addEventListener('click', (e) => {
    const btn = e.target.closest('.btn-view[data-view-career-id]');
    if (!btn) return;
    const id = btn.getAttribute('data-view-career-id');
    const modal = document.getElementById('viewCareerModal_' + id);
    if (modal) modal.style.display = 'flex';
});

// --- Delegación: botón "Editar" carrera ---
document.addEventListener('click', (e) => {
    const btn = e.target.closest('.btn-edit[data-career-id]');
    if (!btn) return;
    const id = btn.getAttribute('data-career-id');
    const modal = document.getElementById('editCareerModal_' + id);
    if (modal) modal.style.display = 'flex';
});

// --- Delegación: clic en tarjeta carrera → Reticula escolar (no en botones) ---
document.addEventListener('click', (e) => {
    const card = e.target.closest('.carrer-card[data-reticula-url]');
    if (!card) return;
    if (e.target.closest('.carrer-btn-section') || e.target.closest('form')) return;
    const url = card.getAttribute('data-reticula-url');
    if (url) window.location.href = url;
});

// --- Buscador Lista de Materias: siempre búsqueda General (todas las columnas) ---
function materiasApplySearch() {
    const input = document.getElementById('materiasSearchNombre');
    if (!input) return;
    const main = document.querySelector('#main-content, .main-content');
    if (!main) return;
    const tbody = main.querySelector('.tabla-base.tabla-materias .cuerpo-tabla');
    if (!tbody) return;
    const search = (input.value || '').trim().toLowerCase();
    const rows = Array.from(tbody.querySelectorAll('tr'));
    rows.forEach((tr) => {
        if (tr.classList.contains('materias-carrera-header')) return;
        if (!search) {
            tr.style.display = '';
            return;
        }
        let found = false;
        for (let c = 1; c <= 5; c++) {
            const cell = tr.querySelector(`td:nth-child(${c})`);
            const text = cell ? cell.textContent.trim().toLowerCase() : '';
            if (text.includes(search)) { found = true; break; }
        }
        tr.style.display = found ? '' : 'none';
    });
    rows.forEach((tr) => {
        if (!tr.classList.contains('materias-carrera-header')) return;
        let next = tr.nextElementSibling;
        let hasVisible = false;
        while (next && !next.classList.contains('materias-carrera-header')) {
            if (next.style.display !== 'none') { hasVisible = true; break; }
            next = next.nextElementSibling;
        }
        tr.style.display = hasVisible ? '' : 'none';
    });
}
document.addEventListener('input', (e) => {
    if (e.target.id === 'materiasSearchNombre') materiasApplySearch();
});

// --- Buscador Lista de Docentes: búsqueda general por carrera, nombre, apellido paterno, apellido materno y estado ---
document.addEventListener('input', (e) => {
    const input = e.target.id === 'docentesSearchNombre' ? e.target : null;
    if (!input) return;
    const main = document.querySelector('#main-content, .main-content');
    if (!main) return;
    const tbody = main.querySelector('.tabla-base.tabla-docentes .cuerpo-tabla');
    if (!tbody) return;
    const search = (input.value || '').trim().toLowerCase();
    const searchWords = search ? search.split(/\s+/).filter(Boolean) : [];
    const rows = Array.from(tbody.querySelectorAll('tr'));
    rows.forEach((tr) => {
        const rfc = (tr.querySelector('td:nth-child(1)')?.textContent || '').trim().toLowerCase();
        const carrera = (tr.querySelector('td:nth-child(2)')?.textContent || '').trim().toLowerCase();
        const nombre = (tr.querySelector('td:nth-child(3)')?.textContent || '').trim().toLowerCase();
        const paterno = (tr.querySelector('td:nth-child(4)')?.textContent || '').trim().toLowerCase();
        const materno = (tr.querySelector('td:nth-child(5)')?.textContent || '').trim().toLowerCase();
        const estado = (tr.querySelector('td:nth-child(6)')?.textContent || '').trim().toLowerCase();
        const textoBusqueda = [rfc, carrera, nombre, paterno, materno, estado].join(' ');
        const match = !search || (searchWords.length === 0 ? true : searchWords.every(w => textoBusqueda.includes(w)));
        tr.style.display = match ? '' : 'none';
    });
});

// --- Horarios: búsqueda por Carrera, Materia o Docente (delegación para que funcione con SPA y al refrescar) ---
let _horariosSearchTimeout;
document.addEventListener('submit', (e) => {
    const form = e.target.id === 'search-form' ? e.target : null;
    if (!form || !form.closest('.schedule-table')) return;
    e.preventDefault();
    const input = document.getElementById('search-input');
    const tbody = document.getElementById('horarios-tbody');
    if (!input || !tbody) return;
    doHorariosSearch(input, form, tbody);
});
document.addEventListener('input', (e) => {
    if (e.target.id !== 'search-input') return;
    const form = document.getElementById('search-form');
    const tbody = document.getElementById('horarios-tbody');
    if (!form || !tbody) return;
    clearTimeout(_horariosSearchTimeout);
    _horariosSearchTimeout = setTimeout(() => doHorariosSearch(e.target, form, tbody), 150);
});
document.addEventListener('keyup', (e) => {
    if (e.target.id !== 'search-input') return;
    const form = document.getElementById('search-form');
    const tbody = document.getElementById('horarios-tbody');
    if (!form || !tbody) return;
    clearTimeout(_horariosSearchTimeout);
    _horariosSearchTimeout = setTimeout(() => doHorariosSearch(e.target, form, tbody), 150);
});
document.addEventListener('keydown', (e) => {
    if (e.target.id === 'search-input' && e.key === 'Enter') e.preventDefault();
});
function doHorariosSearch(searchInput, searchForm, tbody) {
    const query = (searchInput && searchInput.value ? searchInput.value : '').trim();
    let baseUrl = searchForm.getAttribute('action') || searchForm.action || '';
    baseUrl = baseUrl.replace(/\?.*$/, '');
    const sep = baseUrl.indexOf('?') >= 0 ? '&' : '?';
    const url = baseUrl + sep + 'search_query=' + encodeURIComponent(query);
    fetch(url, {
        method: 'GET',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' },
        credentials: 'same-origin'
    })
        .then((r) => r.text())
        .then((html) => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newTbody = doc.getElementById('horarios-tbody');
            if (newTbody && tbody) tbody.innerHTML = newTbody.innerHTML;
        })
        .catch(() => {});
}

// --- Horarios: Ver en modal (no navegar) ---
document.addEventListener('click', (e) => {
    const btn = e.target.closest('.btn-view[data-schedule-show-url]');
    if (!btn || !btn.closest('.schedule-table')) return;
    e.preventDefault();
    const url = btn.getAttribute('data-schedule-show-url');
    if (!url) return;
    const modal = document.getElementById('scheduleModal');
    const body = document.getElementById('scheduleModalBody');
    if (!modal || !body) return;
    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }, credentials: 'same-origin' })
        .then((r) => r.text())
        .then((html) => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const container = doc.querySelector('.modal-view-career__container, .container');
            const innerBody = doc.querySelector('.modal-view-career__body');
            const bodyContent = innerBody ? innerBody.innerHTML : (container ? container.innerHTML : doc.body.innerHTML);
            body.innerHTML = '<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">' +
                '<h5 style="text-align: center; color: #223f70; margin: 0; font-size: 1.25rem; flex: 1;">Informe de Horario</h5>' +
                '<button type="button" class="schedule-modal-close" aria-label="Cerrar" style="background: none; border: none; font-size: 1.5rem; font-weight: 700; cursor: pointer; color: #555; line-height: 1; padding: 0 0.5rem;">×</button>' +
                '</div>' + bodyContent;
            const closeLink = body.querySelector('.modal-view-career__close');
            if (closeLink) { closeLink.href = '#'; closeLink.onclick = (ev) => { ev.preventDefault(); modal.style.display = 'none'; }; }
            modal.style.display = 'flex';
        })
        .catch(() => { body.innerHTML = '<p style="padding:1rem; color:#b00;">No se pudo cargar.</p>'; modal.style.display = 'flex'; });
});
// --- Horarios: Editar en modal (no navegar) ---
document.addEventListener('click', (e) => {
    const btn = e.target.closest('.btn-edit[data-schedule-edit-url]');
    if (!btn || !btn.closest('.schedule-table')) return;
    e.preventDefault();
    const url = btn.getAttribute('data-schedule-edit-url');
    if (!url) return;
    const modal = document.getElementById('scheduleModal');
    const body = document.getElementById('scheduleModalBody');
    if (!modal || !body) return;
    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }, credentials: 'same-origin' })
        .then((r) => r.text())
        .then((html) => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const formWrap = doc.querySelector('.schedule-lists') || doc.querySelector('#schedule_form') || doc.querySelector('.creator-container');
            body.innerHTML = formWrap ? formWrap.outerHTML : doc.body.innerHTML;
            const form = body.querySelector('#schedule_form');
            if (form) {
                if (typeof scheduleGetState === 'function') scheduleGetState(form);
                if (typeof scheduleRenderPreview === 'function') scheduleRenderPreview(form);
                if (typeof window.initScheduleClockPickersForContainer === 'function') window.initScheduleClockPickersForContainer(body);
                if (typeof window.initScheduleFormIfNeeded === 'function') window.initScheduleFormIfNeeded();
                if (typeof window.initHorariosCareerFilter === 'function') window.initHorariosCareerFilter();
                body.addEventListener('click', function saveClick(ev) {
                    const saveBtn = ev.target.closest('#save_schedule_btn');
                    if (!saveBtn || !body.contains(saveBtn)) return;
                    ev.preventDefault();
                    ev.stopPropagation();
                    const f = body.querySelector('#schedule_form');
                    if (!f) return;
                    const state = typeof scheduleGetState === 'function' ? scheduleGetState(f) : {};
                    const franjas = state.franjas || (f.getAttribute('data-initial-franjas') ? JSON.parse(f.getAttribute('data-initial-franjas')) : []);
                    const formData = new FormData(f);
                    formData.set('_method', 'PUT');
                    formData.set('franjas_json', JSON.stringify(franjas));
                    const action = f.getAttribute('action') || f.action;
                    fetch(action, { method: 'POST', body: formData, headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
                        .then((res) => {
                            if (res.ok || res.redirected) {
                                modal.style.display = 'none';
                                body.removeEventListener('click', saveClick);
                                const searchForm = document.getElementById('search-form');
                                const tbody = document.getElementById('horarios-tbody');
                                if (searchForm && tbody) {
                                    const inp = document.getElementById('search-input');
                                    const q = inp ? inp.value.trim() : '';
                                    const u = (searchForm.getAttribute('action') || searchForm.action || '').replace(/\?.*$/, '') + (q ? '?search_query=' + encodeURIComponent(q) : '');
                                    fetch(u, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }, credentials: 'same-origin' })
                                        .then((r) => r.text())
                                        .then((html) => {
                                            const doc2 = new DOMParser().parseFromString(html, 'text/html');
                                            const newTbody = doc2.getElementById('horarios-tbody');
                                            if (newTbody) tbody.innerHTML = newTbody.innerHTML;
                                        });
                                }
                            } else {
                                alert('Error al actualizar.');
                            }
                        })
                        .catch(() => { alert('Error al actualizar.'); });
                });
            }
            modal.style.display = 'flex';
        })
        .catch(() => { body.innerHTML = '<p style="padding:1rem; color:#b00;">No se pudo cargar.</p>'; modal.style.display = 'flex'; });
});
// --- Cerrar modal horario: botón y clic fuera ---
document.addEventListener('click', (e) => {
    if (e.target.classList.contains('schedule-modal-close') || e.target.closest('.schedule-modal-close') ||
        e.target.classList.contains('schedule-edit-close') || e.target.closest('.schedule-edit-close')) {
        const modal = document.getElementById('scheduleModal');
        if (modal) modal.style.display = 'none';
    }
    if (e.target.id === 'scheduleModal') {
        e.target.style.display = 'none';
    }
});

// --- Horarios: botones de día (Lun, Mar, Mié…) — delegación para SPA y carga normal ---
document.addEventListener('click', (e) => {
    const btn = e.target.closest('.day-selection-buttons button[data-day]');
    if (!btn) return;
    btn.classList.toggle('selected');
    const form = btn.closest('form.schedule-form');
    if (form && typeof scheduleUpdatePreviewSelection === 'function') scheduleUpdatePreviewSelection(form);
});
// --- Horarios: actualizar vista previa cuando cambian las horas de inicio/fin ---
document.addEventListener('input', (e) => {
    if (e.target.matches('input[name="hora_inicio"], input[name="hora_fin"]')) {
        const form = e.target.closest('form.schedule-form');
        if (form && typeof scheduleUpdatePreviewSelection === 'function') scheduleUpdatePreviewSelection(form);
    }
});

// --- Horarios: formulario de franjas (Añadir franja, Eliminar, Guardar) — delegación para SPA y carga normal ---
const SCHEDULE_DAY_NAMES = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
function scheduleGetNombreDia(numeroDia) {
    return SCHEDULE_DAY_NAMES[numeroDia - 1] || 'Día';
}
function scheduleTimeTo12h(timeStr) {
    if (!timeStr) return '00:00';
    const parts = String(timeStr).trim().split(':');
    const h = parseInt(parts[0], 10) || 0;
    const m = parts[1] ? String(parseInt(parts[1], 10) || 0).padStart(2, '0') : '00';
    return (h < 10 ? '0' + h : String(h)) + ':' + m;
}
/** Actualiza la vista previa vacía con la selección actual (días + horario) del schedule-settings */
function scheduleUpdatePreviewSelection(form) {
    if (!form) return;
    const container = form.querySelector('.schedule-preview-cards') || form.querySelector('#time_slots_body') || form.querySelector('#modal_edit_time_slots_body');
    if (!container) return;
    const diasSeleccionados = [];
    form.querySelectorAll('.day-selection-buttons button.selected').forEach((btn) => {
        const day = parseInt(btn.getAttribute('data-day'), 10);
        if (!isNaN(day)) diasSeleccionados.push(day);
    });
    const inputInicio = form.querySelector('input[name="hora_inicio"]');
    const inputFin = form.querySelector('input[name="hora_fin"]');
    const horaInicio = (inputInicio && inputInicio.value ? inputInicio.value.trim() : '') || '00:00';
    const horaFin = (inputFin && inputFin.value ? inputFin.value.trim() : '') || '00:00';
    const hi = scheduleTimeTo12h(horaInicio);
    const hf = scheduleTimeTo12h(horaFin);
    const diaNombres = diasSeleccionados.length ? diasSeleccionados.sort((a, b) => a - b).map(scheduleGetNombreDia).join(' – ') : '';
    const texto = diaNombres ? `${diaNombres} -- ${hi} – ${hf}` : 'Lunes – Martes – Miércoles -- 07:00 – 08:00';
    const span = container.querySelector('.schedule-preview-empty span');
    if (span) span.textContent = texto;
}
function scheduleGroupFranjas(serverFranjas) {
    const byKey = {};
    (serverFranjas || []).forEach((f) => {
        const key = (f.hora_inicio || '').substring(0, 5) + '|' + (f.hora_fin || '').substring(0, 5);
        if (!byKey[key]) {
            byKey[key] = { dias_semana: [], hora_inicio: (f.hora_inicio || '').substring(0, 8), hora_fin: (f.hora_fin || '').substring(0, 8) };
        }
        const days = Array.isArray(f.dias_semana) ? f.dias_semana : [f.dias_semana];
        days.forEach((d) => {
            const n = parseInt(d, 10);
            if (!isNaN(n) && !byKey[key].dias_semana.includes(n)) byKey[key].dias_semana.push(n);
        });
    });
    let tid = 1;
    return Object.values(byKey).map((g) => ({ temp_id: tid++, dias_semana: g.dias_semana.sort((a, b) => a - b), hora_inicio: g.hora_inicio, hora_fin: g.hora_fin }));
}
function scheduleGetState(form) {
    if (!form) return { franjas: [], tempIdCounter: 1 };
    if (form._scheduleFranjas) return { franjas: form._scheduleFranjas, tempIdCounter: form._scheduleTempIdCounter };
    const raw = form.getAttribute('data-initial-franjas');
    const franjas = raw ? scheduleGroupFranjas(JSON.parse(raw)) : [];
    let tempIdCounter = franjas.length + 1;
    franjas.forEach((f, i) => { f.temp_id = i + 1; });
    form._scheduleFranjas = franjas;
    form._scheduleTempIdCounter = tempIdCounter;
    return { franjas: form._scheduleFranjas, tempIdCounter: form._scheduleTempIdCounter };
}
function scheduleRenderPreview(form) {
    const container = form ? (form.querySelector('.schedule-preview-cards') || form.querySelector('#time_slots_body') || form.querySelector('#modal_edit_time_slots_body')) : document.getElementById('time_slots_body');
    if (!container) return;
    const { franjas } = scheduleGetState(form);
    container.innerHTML = '';
    if (franjas.length === 0) {
        const table = document.createElement('table');
        table.className = 'schedule-preview-empty-table';
        table.innerHTML = '<tr><th class="schedule-preview-empty" style="color: #ACACAC; font-size: 0.9rem; font-weight: normal; margin: 0; padding: 8px 12px; text-align: left; border: none; background: transparent; text-transform: capitalize; display: flex; align-items: center; justify-content: space-between; gap: 10px;"><span>Lunes – Martes – Miércoles -- 07:00 – 08:00</span><img src="/images/icons/pen-to-square-solid-full.svg" class="schedule-preview-empty__icon" width="18" height="18" alt="Editar" style="flex-shrink: 0;" /></th></tr>';
        container.appendChild(table);
        scheduleUpdatePreviewSelection(form);
        return;
    }
    const deleteSvg = '<svg class="schedule-preview-card__icon" width="18" height="18" viewBox="0 0 640 640" xmlns="http://www.w3.org/2000/svg"><path d="M535.6 85.7C513.7 63.8 478.3 63.8 456.4 85.7L432 110.1L529.9 208L554.3 183.6C576.2 161.7 576.2 126.3 554.3 104.4L535.6 85.7zM236.4 305.7C230.3 311.8 225.6 319.3 222.9 327.6L193.3 416.4C190.4 425 192.7 434.5 199.1 441C205.5 447.5 215 449.7 223.7 446.8L312.5 417.2C320.7 414.5 328.2 409.8 334.4 403.7L496 241.9L398.1 144L236.4 305.7zM160 128C107 128 64 171 64 224L64 480C64 533 107 576 160 576L416 576C469 576 512 533 512 480L512 384C512 366.3 497.7 352 480 352C462.3 352 448 366.3 448 384L448 480C448 497.7 433.7 512 416 512L160 512C142.3 512 128 497.7 128 480L128 224C128 206.3 142.3 192 160 192L256 192C273.7 192 288 177.7 288 160C288 142.3 273.7 128 256 128L160 128z" fill="currentColor"/></svg>';
    franjas.forEach((franja) => {
        const diaNombres = (franja.dias_semana || []).map(scheduleGetNombreDia).join(' – ');
        const hi = scheduleTimeTo12h(franja.hora_inicio);
        const hf = scheduleTimeTo12h(franja.hora_fin);
        const texto = `${diaNombres} -- ${hi} – ${hf}`;
        const card = document.createElement('div');
        card.className = 'schedule-preview-card';
        card.innerHTML = `<span class="schedule-preview-card__text" style="color: #333; font-size: 1rem; font-weight: 600; text-transform: capitalize;">${texto}</span><button type="button" class="schedule-preview-card__action delete-franja" data-id="${franja.temp_id}" title="Eliminar" aria-label="Eliminar franja">${deleteSvg}</button>`;
        container.appendChild(card);
    });
}
function scheduleClearTimeForm(form) {
    if (!form) return;
    const hi = form.querySelector('input[name="hora_inicio"]');
    const hf = form.querySelector('input[name="hora_fin"]');
    if (hi) { hi.value = '00:00'; hi.dispatchEvent(new Event('input', { bubbles: true })); }
    if (hf) { hf.value = '00:00'; hf.dispatchEvent(new Event('input', { bubbles: true })); }
    form.querySelectorAll('.day-selection-buttons button.selected').forEach((btn) => btn.classList.remove('selected'));
}

document.addEventListener('click', (e) => {
    const addBtn = e.target.closest('.add-time-slot-btn');
    if (addBtn) {
        e.preventDefault();
        const form = addBtn.closest('form.schedule-form');
        if (!form) return;
        const { franjas, tempIdCounter } = scheduleGetState(form);
        const diasSeleccionados = [];
        form.querySelectorAll('.day-selection-buttons button.selected').forEach((button) => {
            const day = parseInt(button.getAttribute('data-day'), 10);
            if (!isNaN(day)) diasSeleccionados.push(day);
        });
        const inputInicio = form.querySelector('input[name="hora_inicio"]');
        const inputFin = form.querySelector('input[name="hora_fin"]');
        const horaInicio = (inputInicio && inputInicio.value ? inputInicio.value.trim() : '') || '';
        const horaFin = (inputFin && inputFin.value ? inputFin.value.trim() : '') || '';
        if (diasSeleccionados.length === 0 || !horaInicio || !horaFin) {
            alert('Por favor, selecciona al menos un día y las horas de inicio y fin.');
            return;
        }
        form._scheduleFranjas = franjas;
        form._scheduleTempIdCounter = tempIdCounter + 1;
        form._scheduleFranjas.push({
            temp_id: tempIdCounter,
            dias_semana: diasSeleccionados,
            hora_inicio: horaInicio.length <= 5 ? horaInicio + ':00' : horaInicio.substring(0, 8),
            hora_fin: horaFin.length <= 5 ? horaFin + ':00' : horaFin.substring(0, 8)
        });
        scheduleRenderPreview(form);
        scheduleClearTimeForm(form);
        addBtn.classList.remove('active');
        return;
    }
    const delBtn = e.target.closest('.delete-franja');
    if (delBtn) {
        const form = delBtn.closest('form.schedule-form');
        if (!form) return;
        const id = parseInt(delBtn.getAttribute('data-id'), 10);
        if (isNaN(id)) return;
        const { franjas } = scheduleGetState(form);
        form._scheduleFranjas = franjas.filter((f) => f.temp_id !== id);
        scheduleRenderPreview(form);
        return;
    }
    const saveBtn = e.target.closest('#save_schedule_btn');
    if (saveBtn) {
        const form = saveBtn.closest('form.schedule-form');
        if (!form) return;
        e.preventDefault();
        const { franjas } = scheduleGetState(form);
        let hidden = form.querySelector('input[name="franjas_json"]');
        if (!hidden) {
            hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'franjas_json';
            form.appendChild(hidden);
        }
        hidden.value = JSON.stringify(form._scheduleFranjas || franjas);
        form.submit();
    }
});

function initScheduleFormIfNeeded() {
    const form = document.getElementById('schedule_form');
    if (!form) return;
    if (!form.hasAttribute('data-initial-franjas')) return;
    if (form._scheduleFranjas) return;
    scheduleGetState(form);
    scheduleRenderPreview(form);
}
function scheduleSyncPreviewSelection() {
    const form = document.getElementById('schedule_form');
    if (form && typeof scheduleUpdatePreviewSelection === 'function') scheduleUpdatePreviewSelection(form);
}
function initHorariosCareerFilter() {
    const carreraSelect = document.getElementById('carrera_select');
    const materiaSelect = document.getElementById('materia_select');
    const docenteSelect = document.getElementById('docente_select');
    if (!carreraSelect || !materiaSelect || !docenteSelect) return;
    const form = document.getElementById('schedule_form');
    if (!form) return;
    if (form.dataset.careerFilterInit === '1') return;
    form.dataset.careerFilterInit = '1';

    const materiasData = [];
    const docentesData = [];
    Array.from(materiaSelect.options).forEach((opt, i) => {
        if (i === 0) return;
        materiasData.push({ value: opt.value, text: opt.textContent.trim(), careerId: String(opt.getAttribute('data-career-id') || '') });
    });
    Array.from(docenteSelect.options).forEach((opt, i) => {
        if (i === 0) return;
        docentesData.push({ value: opt.value, text: opt.textContent.trim(), careerId: String(opt.getAttribute('data-career-id') || '') });
    });

    function filterByCareer(resetValues = true) {
        const careerId = String(carreraSelect.value || '');
        const savedMateriaId = materiaSelect.value;
        const savedDocenteId = docenteSelect.value;
        const materiasFiltered = careerId ? materiasData.filter((m) => m.careerId === careerId) : materiasData;
        const docentesFiltered = careerId ? docentesData.filter((d) => d.careerId === careerId) : docentesData;
        materiaSelect.innerHTML = '';
        docenteSelect.innerHTML = '';
        materiaSelect.appendChild(new Option('Seleccione el nombre de la Materia', '', true));
        materiasFiltered.forEach((m) => materiaSelect.appendChild(new Option(m.text, m.value, false)));
        docenteSelect.appendChild(new Option('Seleccione el nombre del docente', '', true));
        docentesFiltered.forEach((d) => docenteSelect.appendChild(new Option(d.text, d.value, false)));
        if (!resetValues && savedMateriaId && materiasFiltered.some((m) => m.value === savedMateriaId)) materiaSelect.value = savedMateriaId;
        if (!resetValues && savedDocenteId && docentesFiltered.some((d) => d.value === savedDocenteId)) docenteSelect.value = savedDocenteId;
    }
    carreraSelect.addEventListener('change', () => filterByCareer(true));
    filterByCareer(false);
}

// --- Horarios: time-picker (reloj) — disponible para SPA y modal; así funciona al entrar al módulo sin refrescar ---
function scheduleClockPad2(n) { return (n < 10 ? '0' : '') + n; }
function scheduleClockTo24h(h12, ampm) {
    const h = parseInt(h12, 10);
    if (ampm === 'p. m.') return h === 12 ? 12 : h + 12;
    return h === 12 ? 0 : h;
}
function scheduleClockFrom24h(h24) {
    const h = parseInt(h24, 10);
    if (h === 0) return { h12: '12', ampm: 'a. m.' };
    if (h < 12) return { h12: scheduleClockPad2(h), ampm: 'a. m.' };
    if (h === 12) return { h12: '12', ampm: 'p. m.' };
    return { h12: scheduleClockPad2(h - 12), ampm: 'p. m.' };
}
function initScheduleClockPickerForOne(container, inputId, displayId, dropdownId) {
    const wrap = container.querySelector('.time-input-wrap[data-time-input="' + inputId + '"]');
    const input = container.querySelector('#' + inputId);
    const display = container.querySelector('#' + displayId);
    const dropdown = container.querySelector('#' + dropdownId);
    if (!wrap || !input || !display || !dropdown) return;
    if (wrap.dataset.scheduleClockInited === '1') return;
    wrap.dataset.scheduleClockInited = '1';

    const selectedRow = dropdown.querySelector('.time-picker-selected');
    const cols = dropdown.querySelectorAll('.time-picker-col');
    const colHour = cols[0]; const colMin = cols[1]; const colAmpm = cols[2];
    const cells = selectedRow.querySelectorAll('.time-picker-selected-cell');
    const cellHour = cells[0]; const cellMin = cells[1]; const cellAmpm = cells[2];

    if (colHour.children.length === 0) {
        ['12', '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11'].forEach((v) => {
            const o = document.createElement('div'); o.className = 'time-picker-option'; o.dataset.value = v; o.textContent = v; colHour.appendChild(o);
        });
        for (let m = 0; m < 60; m++) {
            const o = document.createElement('div'); o.className = 'time-picker-option'; o.dataset.value = scheduleClockPad2(m); o.textContent = scheduleClockPad2(m); colMin.appendChild(o);
        }
        ['a. m.', 'p. m.'].forEach((v) => {
            const o = document.createElement('div'); o.className = 'time-picker-option'; o.dataset.value = v; o.textContent = v; colAmpm.appendChild(o);
        });
    }

    function getState() {
        return { hour: cellHour.textContent, min: cellMin.textContent, ampm: cellAmpm.textContent };
    }
    function setState(hour, min, ampm) {
        cellHour.textContent = hour;
        cellMin.textContent = min;
        cellAmpm.textContent = ampm;
        colHour.querySelectorAll('.time-picker-option').forEach((o) => o.classList.toggle('selected', o.dataset.value === hour));
        colMin.querySelectorAll('.time-picker-option').forEach((o) => o.classList.toggle('selected', o.dataset.value === min));
        colAmpm.querySelectorAll('.time-picker-option').forEach((o) => o.classList.toggle('selected', o.dataset.value === ampm));
        const h24 = scheduleClockTo24h(hour, ampm);
        const val = scheduleClockPad2(h24) + ':' + min;
        input.value = val;
        input.dispatchEvent(new Event('input', { bubbles: true }));
        display.textContent = val;
    }
    function syncFromInput() {
        const v = (input.value || '00:00').trim().split(':');
        const h24 = Math.min(23, Math.max(0, parseInt(v[0], 10) || 0));
        const m = Math.min(59, Math.max(0, parseInt(v[1], 10) || 0));
        const s = scheduleClockFrom24h(h24);
        setState(s.h12, scheduleClockPad2(m), s.ampm);
        [colHour, colMin, colAmpm].forEach((col) => {
            const sel = col.querySelector('.time-picker-option.selected');
            if (sel) sel.scrollIntoView({ block: 'nearest', behavior: 'auto' });
        });
    }

    [colHour, colMin, colAmpm].forEach((col) => {
        col.addEventListener('click', (e) => {
            const opt = e.target.closest('.time-picker-option');
            if (!opt) return;
            const state = getState();
            const colName = col.dataset.col;
            if (colName === 'hour') state.hour = opt.dataset.value;
            if (colName === 'min') state.min = opt.dataset.value;
            if (colName === 'ampm') state.ampm = opt.dataset.value;
            setState(state.hour, state.min, state.ampm);
        });
    });
    wrap.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        const isOpen = dropdown.classList.contains('is-open');
        document.querySelectorAll('.time-picker-dropdown.is-open').forEach((el) => el.classList.remove('is-open'));
        if (!isOpen) {
            syncFromInput();
            dropdown.classList.add('is-open');
        }
    });
    dropdown.addEventListener('click', (e) => e.stopPropagation());
}
window.initScheduleClockPickersForContainer = function(container) {
    const root = container || document;
    const isModal = root.id === 'horarioEditModal';
    const pre = isModal ? 'modal_edit_' : '';
    const hi = pre + 'hora_inicio', hid = pre + 'hora_inicio_display', hidrop = pre + 'hora_inicio_dropdown';
    const hf = pre + 'hora_fin', hfd = pre + 'hora_fin_display', hfdrop = pre + 'hora_fin_dropdown';
    initScheduleClockPickerForOne(root, hi, hid, hidrop);
    initScheduleClockPickerForOne(root, hf, hfd, hfdrop);
    root.querySelectorAll('.time-input-wrap').forEach((w) => w.addEventListener('click', (e) => e.stopPropagation()));
};

document.addEventListener('DOMContentLoaded', () => {
    initScheduleFormIfNeeded();
    scheduleSyncPreviewSelection();
    initHorariosCareerFilter();
    const mainContent = document.getElementById('main-content');
    if (mainContent) {
        const scheduleObserver = new MutationObserver(() => {
            initScheduleFormIfNeeded();
            initHorariosCareerFilter();
        });
        scheduleObserver.observe(mainContent, { childList: true, subtree: true });
    }
});
document.addEventListener('DOMContentLoaded', initScheduleFormIfNeeded);
window.initScheduleFormIfNeeded = initScheduleFormIfNeeded;
window.scheduleGetState = scheduleGetState;
window.scheduleRenderPreview = scheduleRenderPreview;
