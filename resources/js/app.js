import './bootstrap';
import axios from 'axios';
import '@fortawesome/fontawesome-free/css/all.min.css';

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
        
        // Estas páginas NUNCA se guardan en memoria
        const noCachePaths = ['/facturacion', '/crm', '/crm/leads', '/crm/prospectos', '/crm/comisiones', '/crm/estadisticas'];
        const currentPath = new URL(url, window.location.origin).pathname;

        // Si la URL contiene algo de la lista negra, NO usamos caché
        // control-administrativo: muchos formularios POST; caché de HTML deja _token viejo → 419 Page Expired
        const shouldUseCache = !noCachePaths.some(path => currentPath === path || currentPath.startsWith('/crm'))
            && !currentPath.startsWith('/control-administrativo');

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

            if (shouldUseCache) {
                this.cache.set(cacheKey, { content: content, timestamp: Date.now() });
                if (this.cache.size > 15) this.cleanupCache();
            }

            if (updateHistory) history.pushState({ page: url }, content.title, url);
            return content;
        } catch (error) {
            if (error.name === 'AbortError') throw new Error('Timeout');
            throw error;
        }
    }

    parsePageContent(html) {
        
        const scripts = [];
        const scriptRegex = /<script\b([^>]*)>([\s\S]*?)<\/script>/gi;
        let match;
        while ((match = scriptRegex.exec(html)) !== null) {
            const attrs = match[1];
            const content = match[2].trim();
            const srcMatch = attrs.match(/src=["']([^"']+)["']/);
            if (srcMatch) {
                scripts.push({ type: 'external', src: srcMatch[1] });
            } else if (content) {
                scripts.push({ type: 'inline', content: content });
            }
        }
    
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const mainContent = doc.querySelector('#main-content, .main-content');
        const title = doc.querySelector('title')?.textContent || '';
        if (!mainContent) return null;

        const headInjections = SimpleSPANavigation.collectHeadInjections(doc);
    
        return {
            main: mainContent.innerHTML,
            title: title,
            scripts: scripts,  // ✅ scripts extraídos del HTML crudo
            headInjections,
        };
    }

    /**
     * Estilos del <head> de la página destino (@push('css'), @vite por vista).
     * Sin esto, la navegación SPA solo cambia #main-content y la vista queda sin CSS hasta F5.
     */
    static collectHeadInjections(doc) {
        const styles = [];
        doc.head.querySelectorAll('style').forEach((el) => {
            styles.push(el.outerHTML);
        });
        const links = [];
        doc.head.querySelectorAll('link[rel="stylesheet"]').forEach((el) => {
            const href = el.getAttribute('href');
            if (!href) return;
            let absolute;
            try {
                absolute = new URL(href, window.location.origin).href;
            } catch {
                absolute = href;
            }
            links.push({ href: absolute, outerHTML: el.outerHTML });
        });
        return { styles, links };
    }

    static applyHeadInjections(injections) {
        // Caché antigua sin headInjections: no tocar el head (compatibilidad).
        if (injections === undefined) return;
        document.head.querySelectorAll('[data-spa-head-inject]').forEach((n) => n.remove());
        if (!injections) return;
        const template = document.createElement('template');
        injections.styles.forEach((html) => {
            template.innerHTML = html.trim();
            const el = template.content.firstElementChild;
            if (el && el.tagName === 'STYLE') {
                el.setAttribute('data-spa-head-inject', '1');
                document.head.appendChild(el);
            }
        });
        injections.links.forEach(({ href, outerHTML }) => {
            const already = [...document.querySelectorAll('link[rel="stylesheet"]')].some((l) => {
                const h = l.getAttribute('href');
                if (!h) return false;
                try {
                    return new URL(h, window.location.origin).href === href;
                } catch {
                    return false;
                }
            });
            if (already) return;
            template.innerHTML = outerHTML.trim();
            const el = template.content.querySelector('link[rel="stylesheet"]');
            if (el) {
                el.setAttribute('data-spa-head-inject', '1');
                document.head.appendChild(el);
            }
        });
    }

    updatePage(content, url) {
        const mainElement = document.querySelector('#main-content, .main-content');
        if (mainElement) {
            mainElement.style.transition = 'opacity 0.2s ease';
            mainElement.style.opacity = '0.7';
            setTimeout(() => {
                SimpleSPANavigation.applyHeadInjections(content.headInjections);
                mainElement.innerHTML = content.main;
                mainElement.style.opacity = '1';

                const tokenInput = mainElement.querySelector('input[name="_token"]');
                const metaCsrf = document.querySelector('meta[name="csrf-token"]');
                if (tokenInput?.value && metaCsrf) {
                    metaCsrf.setAttribute('content', tokenInput.value);
                }
    
                // Ejecutar scripts creando elementos reales en el DOM
                content.scripts.forEach(script => {
                    if (script.type === 'external') {
                        if (!document.querySelector(`script[src="${script.src}"]`)) {
                            const el = document.createElement('script');
                            el.src = script.src;
                            el.async = true;
                            document.head.appendChild(el);
                        }
                    } else {
                        const el = document.createElement('script');
                        el.textContent = script.content;
                        document.body.appendChild(el);
                        document.body.removeChild(el);
                    }
                });
    
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


    updateActiveMenuItem() {
        const currentPath = new URL(window.location.href).pathname.replace(/\/+$/, '') || '/';

        document.querySelectorAll('.menu li.active, .menu li.active-submenu, .menu li.open').forEach((item) => {
            item.classList.remove('active', 'active-submenu', 'open');
        });

        document.querySelectorAll('.menu a').forEach(link => {
            const li = link.closest('li');
            if (!li) return;
            const href = (link.getAttribute('href') || '').trim();
            if (!href || href === '#' || href.startsWith('javascript:') || href.startsWith('mailto:')) {
                return;
            }
            let linkPath = '/';
            try {
                linkPath = new URL(href, window.location.origin).pathname.replace(/\/+$/, '') || '/';
            } catch (e) {
                linkPath = href.replace(/\/+$/, '') || '/';
            }
            const isMatch = linkPath === currentPath ||
                            (linkPath !== '/' && currentPath.startsWith(linkPath + '/'));
            if (!isMatch) return;

            if (li.closest('.submenu')) {
                li.classList.add('active-submenu');
            } else {
                li.classList.add('active');
            }

            let parent = li.closest('li.has-submenu');
            while (parent) {
                parent.classList.add('active');
                parent.classList.remove('open');
                parent = parent.parentElement?.closest('li.has-submenu');
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

// Modal global #careerSuccessModal (Carreras, Materias, clasificación AJAX) — layout app.blade.php
function showCareerSuccessModalIfMessagePresent() {
    const successModal = document.getElementById('careerSuccessModal');
    const successModalMessage = document.getElementById('careerSuccessModalMessage');
    if (!successModal || !successModalMessage) return;
    if (typeof window.careerSuccessMessage === 'string' && window.careerSuccessMessage) {
        successModalMessage.textContent = window.careerSuccessMessage;
        successModal.style.display = 'flex';
    }
}

function bindCareerSuccessModalOkOnce() {
    const successModal = document.getElementById('careerSuccessModal');
    const successModalOk = document.getElementById('careerSuccessModalOk');
    if (!successModal || !successModalOk || successModalOk.dataset.boundCareerOk === '1') return;
    successModalOk.dataset.boundCareerOk = '1';
    successModalOk.addEventListener('click', () => {
        successModal.style.display = 'none';
        try {
            delete window.careerSuccessMessage;
        } catch (e) {
            window.careerSuccessMessage = undefined;
        }
        const afterOk = window.afterCareerSuccessModalOk;
        try {
            delete window.afterCareerSuccessModalOk;
        } catch (e) {
            window.afterCareerSuccessModalOk = undefined;
        }
        if (typeof afterOk === 'function') {
            try {
                afterOk();
            } catch (err) {
                console.error(err);
            }
        }
    });
}

function initCareerSuccessModal() {
    bindCareerSuccessModalOkOnce();
    showCareerSuccessModalIfMessagePresent();
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    initCareerSuccessModal();
    window.spaNav = new SimpleSPANavigation();
    window.navigateTo = (url) => window.spaNav.navigateTo(url);
});

// Tras restauración desde bfcache u orden de ejecución, el aviso debe mostrarse aunque no coincida con DOMContentLoaded solo.
window.addEventListener('pageshow', () => {
    bindCareerSuccessModalOkOnce();
    showCareerSuccessModalIfMessagePresent();
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

// --- Delegación: botón "Agregar clasificación" (carreras) ---
document.addEventListener('click', (e) => {
    const btn = e.target.closest('#openCreateClasificacionBtn');
    if (!btn) return;
    const modal = document.getElementById('createClassificationModal');
    if (modal) modal.style.display = 'flex';
});

// --- Clic en etiqueta "Clasificación" (modal agregar carrera): abrir modal de clasificaciones ---
document.addEventListener(
    'click',
    (e) => {
        const label = e.target.closest('label[for="career_classification_id"]');
        if (!label || !label.closest('#createCareerModal')) return;
        e.preventDefault();
        const modal = document.getElementById('createClassificationModal');
        if (modal) modal.style.display = 'flex';
    },
    true
);

function clearClassificationStoreAjaxFeedback(form) {
    const el = form.querySelector('#classification_name_ajax_error');
    if (el) {
        el.textContent = '';
        el.style.display = 'none';
    }
    const input = form.querySelector('#classification_name');
    if (input) input.classList.remove('validation-error');
}

function showClassificationStoreAjaxError(form, message) {
    const el = form.querySelector('#classification_name_ajax_error');
    if (el) {
        el.textContent = message;
        el.style.display = 'block';
    }
    const input = form.querySelector('#classification_name');
    if (input) input.classList.add('validation-error');
}

function appendClassificationModalListRow(classification) {
    const modal = document.getElementById('createClassificationModal');
    if (!modal) return;
    const scroll = modal.querySelector('.classification-modal-scroll');
    if (!scroll) return;
    scroll.querySelector('.classification-modal-empty')?.remove();
    let wrap = scroll.querySelector('.classification-modal-list');
    if (!wrap) {
        wrap = document.createElement('div');
        wrap.className = 'classification-modal-list';
        const title = document.createElement('span');
        title.className = 'classification-modal-list__title';
        title.textContent = 'Registradas en esta institución';
        const ul = document.createElement('ul');
        ul.className = 'classification-modal-list__items';
        wrap.appendChild(title);
        wrap.appendChild(ul);
        scroll.insertBefore(wrap, scroll.firstChild);
    }
    let ul = wrap.querySelector('.classification-modal-list__items');
    if (!ul) {
        ul = document.createElement('ul');
        ul.className = 'classification-modal-list__items';
        wrap.appendChild(ul);
    }
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const li = document.createElement('li');
    li.className = 'classification-modal-list__row';
    const nameSpan = document.createElement('span');
    nameSpan.className = 'classification-modal-list__name';
    nameSpan.textContent = classification.name;
    const delForm = document.createElement('form');
    delForm.method = 'post';
    delForm.className = 'classification-modal-list__delete-form';
    delForm.action = classification.destroy_url;
    delForm.addEventListener('submit', (ev) => {
        if (!confirm('¿Eliminar esta clasificación?')) ev.preventDefault();
    });
    const tok = document.createElement('input');
    tok.type = 'hidden';
    tok.name = '_token';
    tok.value = token;
    const method = document.createElement('input');
    method.type = 'hidden';
    method.name = '_method';
    method.value = 'DELETE';
    const delBtn = document.createElement('button');
    delBtn.type = 'submit';
    delBtn.className = 'classification-modal-delete-btn';
    delBtn.title = 'Eliminar clasificación';
    delBtn.setAttribute('aria-label', 'Eliminar clasificación');
    delBtn.textContent = '×';
    delForm.appendChild(tok);
    delForm.appendChild(method);
    delForm.appendChild(delBtn);
    li.appendChild(nameSpan);
    li.appendChild(delForm);
    ul.appendChild(li);
}

// Eliminar materia (lista): AJAX + careerSuccessModal (misma UX que redirect ?modal=success, sin recarga; compatible con SPA)
document.addEventListener('submit', (e) => {
    const form = e.target.closest('form.js-materia-delete-form');
    if (!form) return;
    e.preventDefault();
    if (!confirm('¿Estás seguro de eliminar este registro?')) return;
    const url = form.getAttribute('action');
    if (!url) return;
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const fd = new FormData(form);
    fetch(url, {
        method: 'POST',
        body: fd,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            Accept: 'application/json',
            'X-CSRF-TOKEN': token,
        },
    })
        .then(async (res) => {
            const data = await res.json().catch(() => ({}));
            if (res.ok && data.ok) {
                const tr = form.closest('tr');
                if (tr) tr.remove();
                const successModal = document.getElementById('careerSuccessModal');
                const successModalMessage = document.getElementById('careerSuccessModalMessage');
                if (successModal && successModalMessage) {
                    successModalMessage.textContent = data.message || 'Materia eliminada correctamente.';
                    successModal.style.display = 'flex';
                }
                if (window.spaNav && typeof window.spaNav.clearCache === 'function') {
                    window.spaNav.clearCache();
                }
                return;
            }
            const msg = (data && data.message) || 'No se pudo eliminar la materia.';
            window.alert(msg);
        })
        .catch(() => {
            window.alert('Error de red. Intenta de nuevo.');
        });
});

// Eliminar aula (infraestructura): AJAX + careerSuccessModal + recarga al OK (plantillas por fila en sync)
document.addEventListener('submit', (e) => {
    const form = e.target.closest('form.js-aula-delete-form');
    if (!form) return;
    e.preventDefault();
    if (!confirm('¿Eliminar esta aula?')) return;
    const url = form.getAttribute('action');
    if (!url) return;
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const fd = new FormData(form);
    fetch(url, {
        method: 'POST',
        body: fd,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            Accept: 'application/json',
            'X-CSRF-TOKEN': token,
        },
    })
        .then(async (res) => {
            const data = await res.json().catch(() => ({}));
            if (res.ok && data.ok) {
                const successModal = document.getElementById('careerSuccessModal');
                const successModalMessage = document.getElementById('careerSuccessModalMessage');
                if (successModal && successModalMessage) {
                    successModalMessage.textContent = data.message || 'Aula eliminada correctamente.';
                    window.afterCareerSuccessModalOk = function () {
                        window.location.reload();
                    };
                    successModal.style.display = 'flex';
                } else {
                    window.location.reload();
                }
                if (window.spaNav && typeof window.spaNav.clearCache === 'function') {
                    window.spaNav.clearCache();
                }
                return;
            }
            const msg = (data && data.message) || 'No se pudo eliminar la aula.';
            window.alert(msg);
        })
        .catch(() => {
            window.alert('Error de red. Intenta de nuevo.');
        });
});

// Eliminar docente (lista): AJAX + careerSuccessModal (misma UX que materias)
document.addEventListener('submit', (e) => {
    const form = e.target.closest('form.js-docente-delete-form');
    if (!form) return;
    e.preventDefault();
    if (!confirm('¿Estás seguro de eliminar este docente?')) return;
    const url = form.getAttribute('action');
    if (!url) return;
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const fd = new FormData(form);
    fetch(url, {
        method: 'POST',
        body: fd,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            Accept: 'application/json',
            'X-CSRF-TOKEN': token,
        },
    })
        .then(async (res) => {
            const data = await res.json().catch(() => ({}));
            if (res.ok && data.ok) {
                const tr = form.closest('tr');
                if (tr) tr.remove();
                const successModal = document.getElementById('careerSuccessModal');
                const successModalMessage = document.getElementById('careerSuccessModalMessage');
                if (successModal && successModalMessage) {
                    successModalMessage.textContent = data.message || 'Docente eliminado correctamente.';
                    successModal.style.display = 'flex';
                }
                if (window.spaNav && typeof window.spaNav.clearCache === 'function') {
                    window.spaNav.clearCache();
                }
                return;
            }
            const msg = (data && data.message) || 'No se pudo eliminar el docente.';
            window.alert(msg);
        })
        .catch(() => {
            window.alert('Error de red. Intenta de nuevo.');
        });
});



// Eliminar horario (lista Horarios): AJAX + careerSuccessModal (misma UX que materias/docentes)
document.addEventListener('submit', (e) => {
    const form = e.target.closest('form.js-horario-delete-form');
    if (!form) return;
    e.preventDefault();
    if (!confirm('¿Está seguro de eliminar este horario? Esta acción es irreversible.')) return;
    const url = form.getAttribute('action');
    if (!url) return;
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const fd = new FormData(form);
    fetch(url, {
        method: 'POST',
        body: fd,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            Accept: 'application/json',
            'X-CSRF-TOKEN': token,
        },
    })
        .then(async (res) => {
            const data = await res.json().catch(() => ({}));
            if (res.ok && (data.ok === true || data.success === true)) {
                const tr = form.closest('tr');
                if (tr) tr.remove();
                const successModal = document.getElementById('careerSuccessModal');
                const successModalMessage = document.getElementById('careerSuccessModalMessage');
                if (successModal && successModalMessage) {
                    successModalMessage.textContent = data.message || 'Horario eliminado correctamente.';
                    successModal.style.display = 'flex';
                }
                if (window.spaNav && typeof window.spaNav.clearCache === 'function') {
                    window.spaNav.clearCache();
                }
                return;
            }
            const msg = (data && data.message) || 'No se pudo eliminar el horario.';
            window.alert(msg);
        })
        .catch(() => {
            window.alert('Error de red. Intenta de nuevo.');
        });
});

// Actualizar materia (modal editar): AJAX + careerSuccessModal + refrescar celdas de la fila
document.addEventListener('submit', (e) => {
    const form = e.target.closest('form.js-materia-update-form');
    if (!form) return;
    e.preventDefault();
    const url = form.getAttribute('action');
    if (!url) return;
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const fd = new FormData(form);
    fetch(url, {
        method: 'POST',
        body: fd,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            Accept: 'application/json',
            'X-CSRF-TOKEN': token,
        },
    })
        .then(async (res) => {
            const data = await res.json().catch(() => ({}));
            if (res.ok && data.ok) {
                const modal = form.closest('.modal-overlay');
                if (modal) modal.style.display = 'none';
                const tr = form.closest('tr');
                if (tr && data.row) {
                    const tds = tr.querySelectorAll('td');
                    const r = data.row;
                    if (tds[0]) tds[0].textContent = r.nombre ?? '';
                    if (tds[1]) tds[1].textContent = r.career_name ?? ''; //Carrera
                    if (tds[2]) tds[2].textContent = r.classification_name ?? ''; //Clasificacion
                    if (tds[3]) tds[3].textContent = r.creditos ?? '';
                    if (tds[4]) tds[4].textContent = r.semestre ?? '';
                    if (tds[5]) tds[5].textContent = r.type ?? '';
                }
                const successModal = document.getElementById('careerSuccessModal');
                const successModalMessage = document.getElementById('careerSuccessModalMessage');
                if (successModal && successModalMessage) {
                    successModalMessage.textContent = data.message || '¡Materia actualizada exitosamente!';
                    successModal.style.display = 'flex';
                }
                if (window.spaNav && typeof window.spaNav.clearCache === 'function') {
                    window.spaNav.clearCache();
                }
                return;
            }
            if (res.status === 422) {
                window.alert(data.message || 'Revisa los datos del formulario.');
                return;
            }
            window.alert(data.message || 'No se pudo actualizar la materia.');
        })
        .catch(() => {
            window.alert('Error de red. Intenta de nuevo.');
        });
});

// Guardar clasificación por AJAX: no recargar la página (mantener modal "Agregar carrera" abierto)
document.addEventListener('submit', (e) => {
    const form = e.target.closest('#classificationStoreForm');
    if (!form) return;
    e.preventDefault();
    const url = form.getAttribute('action');
    if (!url) return;
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    clearClassificationStoreAjaxFeedback(form);
    const fd = new FormData(form);
    fetch(url, {
        method: 'POST',
        body: fd,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            Accept: 'application/json',
            'X-CSRF-TOKEN': token,
        },
    })
        .then(async (res) => {
            const data = await res.json().catch(() => ({}));
            if (res.ok && data.ok && data.classification) {
                const c = data.classification;
                const mainSel = document.getElementById('career_classification_id');
                if (mainSel) {
                    const exists = Array.from(mainSel.options).some((o) => String(o.value) === String(c.id));
                    if (!exists) {
                        const opt = document.createElement('option');
                        opt.value = c.id;
                        opt.textContent = c.name;
                        mainSel.appendChild(opt);
                    }
                    mainSel.value = String(c.id);
                }
                document.querySelectorAll('select[id^="career_classification_id_"]').forEach((s) => {
                    if (Array.from(s.options).some((o) => String(o.value) === String(c.id))) return;
                    const opt = document.createElement('option');
                    opt.value = c.id;
                    opt.textContent = c.name;
                    s.appendChild(opt);
                });
                appendClassificationModalListRow(c);
                const nameInput = form.querySelector('#classification_name');
                if (nameInput) nameInput.value = '';
                const classModal = document.getElementById('createClassificationModal');
                if (classModal) classModal.style.display = 'none';
                const successModal = document.getElementById('careerSuccessModal');
                const successModalMessage = document.getElementById('careerSuccessModalMessage');
                if (successModal && successModalMessage) {
                    successModalMessage.textContent = 'Clasificación guardada exitosamente.';
                    successModal.style.display = 'flex';
                }
                return;
            }
            let msg = data.message || 'No se pudo guardar.';
            if (data.errors && data.errors.classification_name && data.errors.classification_name[0]) {
                msg = data.errors.classification_name[0];
            }
            showClassificationStoreAjaxError(form, msg);
        })
        .catch(() => {
            showClassificationStoreAjaxError(form, 'Error de red. Intenta de nuevo.');
        });
});

// --- Delegación: botón "Agregar Carrera" (funciona con SPA al reemplazar contenido) ---
document.addEventListener('click', (e) => {
    const btn = e.target.closest('#openCreateCareerBtn');
    if (!btn) return;
    const modal = document.getElementById('createCareerModal');
    if (modal) modal.style.display = 'flex';
    const classSel = document.getElementById('career_classification_id');
    if (classSel) classSel.classList.toggle('placeholder', classSel.value === '');
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

function calcularEdadDesdeFechaNacimiento(isoDateStr) {
    if (!isoDateStr || !/^\d{4}-\d{2}-\d{2}$/.test(isoDateStr)) return null;
    const parts = isoDateStr.split('-').map(Number);
    const birth = new Date(parts[0], parts[1] - 1, parts[2]);
    if (Number.isNaN(birth.getTime())) return null;
    const today = new Date();
    let age = today.getFullYear() - birth.getFullYear();
    const monthDiff = today.getMonth() - birth.getMonth();
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) age -= 1;
    if (age < 0 || age > 150) return null;
    return age;
}

function syncDocenteEdadFromFecha(scopeRoot) {
    if (!scopeRoot) return;
    const fecha = scopeRoot.querySelector('#modal_fecha_nacimiento') || scopeRoot.querySelector('#fecha_nacimiento');
    const edadEl = scopeRoot.querySelector('#modal_edad_docente') || scopeRoot.querySelector('#registro_docente_edad') || scopeRoot.querySelector('#docente_edit_edad');
    if (!fecha || !edadEl) return;
    const age = calcularEdadDesdeFechaNacimiento(fecha.value);
    edadEl.value = age != null ? String(age) : '';
}

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
            const directForm = doc.querySelector('#form-registro-docente');
            content.innerHTML = formWrap
                ? formWrap.innerHTML
                : (directForm ? directForm.outerHTML : (doc.querySelector('#main-content')?.innerHTML || ''));
            syncDocenteEdadFromFecha(modal);
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

document.addEventListener('input', (e) => {
    if (e.target.id !== 'modal_fecha_nacimiento' && e.target.id !== 'fecha_nacimiento') return;
    const root = e.target.closest('#modalRegistroDocente') || e.target.closest('#form-registro-docente') || e.target.closest('#teacherEditModal') || e.target.closest('#form-edicion-docente');
    if (root) syncDocenteEdadFromFecha(root);
});
document.addEventListener('change', (e) => {
    if (e.target.id !== 'modal_fecha_nacimiento' && e.target.id !== 'fecha_nacimiento') return;
    const root = e.target.closest('#modalRegistroDocente') || e.target.closest('#form-registro-docente') || e.target.closest('#teacherEditModal') || e.target.closest('#form-edicion-docente');
    if (root) syncDocenteEdadFromFecha(root);
});

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('#form-registro-docente').forEach((form) => syncDocenteEdadFromFecha(form));
    document.querySelectorAll('#form-edicion-docente').forEach((form) => syncDocenteEdadFromFecha(form));
});

// Envío del formulario de registro de docente (modal): JSON + careerSuccessModal + refrescar lista
document.addEventListener('submit', (e) => {
    const form = (e.target instanceof HTMLFormElement && e.target.id === 'form-registro-docente') ? e.target : null;
    if (!form || form.id !== 'form-registro-docente') return;
    e.preventDefault();
    e.stopPropagation();
    const modal = document.getElementById('modalRegistroDocente');
    const content = document.getElementById('modalRegistroDocenteContent');
    const formData = new FormData(form);
    const action = form.getAttribute('action');
    const indexUrl = form.getAttribute('data-index-url');
    if (!action) return;
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    fetch(action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            Accept: 'application/json',
            'X-CSRF-TOKEN': token,
        },
    })
        .then(async (res) => {
            const data = await res.json().catch(() => ({}));
            if (res.ok && data.ok) {
                if (modal) {
                    modal.style.display = 'none';
                    modal.classList.remove('is-visible');
                    modal.setAttribute('aria-hidden', 'true');
                }
                if (content) content.innerHTML = '';
                const showSuccess = () => {
                    const successModal = document.getElementById('careerSuccessModal');
                    const successModalMessage = document.getElementById('careerSuccessModalMessage');
                    if (successModal && successModalMessage) {
                        successModalMessage.textContent = data.message || 'Docente registrado exitosamente.';
                        successModal.style.display = 'flex';
                    }
                };
                const refreshList = () => {
                    if (!indexUrl) {
                        showSuccess();
                        return;
                    }
                    fetch(indexUrl, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'text/html' },
                    })
                        .then((r) => r.text())
                        .then((html) => {
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(html, 'text/html');
                            const newMain = doc.querySelector('#main-content, .main-content');
                            const main = document.querySelector('#main-content, .main-content');
                            if (main && newMain) {
                                main.innerHTML = newMain.innerHTML;
                                const t = newMain.querySelector('input[name="_token"]');
                                const meta = document.querySelector('meta[name="csrf-token"]');
                                if (t && t.value && meta) meta.setAttribute('content', t.value);
                            }
                            document.title = doc.querySelector('title')?.textContent || document.title;
                            if (window.spaNav && typeof window.spaNav.updateActiveMenuItem === 'function') {
                                window.spaNav.updateActiveMenuItem();
                            }
                            if (window.spaNav && typeof window.spaNav.clearCache === 'function') {
                                window.spaNav.clearCache();
                            }
                            showSuccess();
                        })
                        .catch(() => showSuccess());
                };
                refreshList();
                return;
            }
            if (res.status === 422) {
                window.alert(data.message || 'Revisa los datos del formulario.');
                return;
            }
            window.alert(data.message || 'No se pudo registrar el docente.');
        })
        .catch((err) => {
            console.error(err);
            if (content) {
                content.innerHTML = '<div style="padding: 1rem; color:#b00020;">Error al guardar. Intente de nuevo.</div>';
            }
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
    if (e.target.id === 'career_classification_id' || (e.target.id && e.target.id.startsWith('career_classification_id_'))) {
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
    document.querySelectorAll('#career_classification_id, select[id^="career_classification_id_"]').forEach((el) => {
        if (el instanceof HTMLSelectElement) el.classList.toggle('placeholder', el.value === '');
    });
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

// --- Lista de Docentes: horario ya es vista (enlace). Modal solo para lista de Alumnos (Horario de Alumno). ---
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

    // Al exportar (imprimir): desde el modal Horario de Docente/Alumno o desde la vista completa de Horario de Docente.
    document.addEventListener('click', (e) => {
        if (!(e.target instanceof Element)) return;
        const btn = e.target.closest('.teacher-horario-modal-export-btn');
        if (!btn) return;
        e.preventDefault();
        const isInModal = document.getElementById('teacherHorariosModal')?.contains(btn);
        if (isInModal) {
            document.body.classList.add('print-teacher-horario');
        } else {
            document.body.classList.add('print-teacher-horario-page');
        }
        window.print();
    }, true);
}
window.initTeacherHorariosModal = initTeacherHorariosModal;
initTeacherHorariosModal();

window.addEventListener('afterprint', () => {
    document.body.classList.remove('print-teacher-horario', 'print-teacher-horario-page');
});

// --- Vista Horario (docente/alumno): botón Exportar → abrir diálogo imprimir (Guardar como PDF) ---
document.addEventListener('click', (e) => {
    if (!(e.target instanceof Element)) return;
    const btn = e.target.closest('.horario-export-pdf-btn');
    if (!btn) return;
    e.preventDefault();
    document.body.classList.add('print-teacher-horario-page');
    window.print();
}, true);

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
                const { modal: editModal } = getEditModalEls();
                if (editModal) syncDocenteEdadFromFecha(editModal);
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
        if (!form || form.id !== 'form-edicion-docente') return;
        e.preventDefault();
        e.stopPropagation();
        const formData = new FormData(form);
        const action = form.getAttribute('action');
        const indexUrl = form.getAttribute('data-index-url');
        if (!action) return;
        const { modal, content } = getEditModalEls();
        fetch(action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
            },
        })
            .then(async (res) => {
                const data = await res.json().catch(() => ({}));
                if (res.ok && data.ok) {
                    closeEditModal();
                    if (content) content.innerHTML = '';
                    const showSuccess = () => {
                        const successModal = document.getElementById('careerSuccessModal');
                        const successModalMessage = document.getElementById('careerSuccessModalMessage');
                        if (successModal && successModalMessage) {
                            successModalMessage.textContent = data.message || 'Docente actualizado correctamente.';
                            successModal.style.display = 'flex';
                        }
                    };
                    const refreshList = () => {
                        if (!indexUrl) {
                            showSuccess();
                            return;
                        }
                        fetch(indexUrl, {
                            headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'text/html' },
                        })
                            .then((r) => r.text())
                            .then((html) => {
                                const parser = new DOMParser();
                                const doc = parser.parseFromString(html, 'text/html');
                                const newMain = doc.querySelector('#main-content, .main-content');
                                const main = document.querySelector('#main-content, .main-content');
                                if (main && newMain) {
                                    main.innerHTML = newMain.innerHTML;
                                    const t = newMain.querySelector('input[name="_token"]');
                                    const meta = document.querySelector('meta[name="csrf-token"]');
                                    if (t && t.value && meta) meta.setAttribute('content', t.value);
                                }
                                document.title = doc.querySelector('title')?.textContent || document.title;
                                if (window.spaNav && typeof window.spaNav.updateActiveMenuItem === 'function') {
                                    window.spaNav.updateActiveMenuItem();
                                }
                                if (window.spaNav && typeof window.spaNav.clearCache === 'function') {
                                    window.spaNav.clearCache();
                                }
                                if (typeof window.bindRegistroDocenteModal === 'function') window.bindRegistroDocenteModal();
                                if (typeof window.openRegistroDocenteModalIfNeeded === 'function') window.openRegistroDocenteModalIfNeeded();
                                showSuccess();
                            })
                            .catch(() => showSuccess());
                    };
                    refreshList();
                    return;
                }
                if (res.status === 422) {
                    let msg = data.message || 'Revisa los datos del formulario.';
                    if (data.errors && typeof data.errors === 'object') {
                        const firstKey = Object.keys(data.errors)[0];
                        if (firstKey && data.errors[firstKey] && data.errors[firstKey][0]) {
                            msg = data.errors[firstKey][0];
                        }
                    }
                    window.alert(msg);
                    return;
                }
                window.alert(data.message || 'No se pudo actualizar el docente.');
            })
            .catch((err) => {
                console.error(err);
                if (content) {
                    content.innerHTML = '<div style="padding: 1rem; color:#b00020;">Error al guardar. Intente de nuevo.</div>';
                }
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
        for (let c = 1; c <= 6; c++) {
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

// --- Horarios: cuando la edición guardó (postMessage o evento horario-updated desde div) ---
function onHorarioUpdated() {
    const modal = document.getElementById('horarioEditModal');
    const content = document.getElementById('horarioEditContent');
    if (modal) { modal.style.display = 'none'; modal.setAttribute('aria-hidden', 'true'); }
    if (content) content.innerHTML = '';
    const searchForm = document.getElementById('search-form');
    const tbody = document.getElementById('horarios-tbody');
    if (searchForm && tbody) {
        const inp = document.getElementById('search-input');
        const q = inp ? inp.value.trim() : '';
        const baseUrl = (searchForm.getAttribute('action') || searchForm.action || '').replace(/\?.*$/, '');
        const url = baseUrl + (q ? '?search_query=' + encodeURIComponent(q) : '');
        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }, credentials: 'same-origin' })
            .then(r => r.text())
            .then(html => {
                const doc = new DOMParser().parseFromString(html, 'text/html');
                const newTbody = doc.getElementById('horarios-tbody');
                if (newTbody) tbody.innerHTML = newTbody.innerHTML;
            })
            .catch(() => {});
    }
}
window.addEventListener('message', function(e) {
    if (e.data && e.data.type === 'horario-updated') onHorarioUpdated();
});
document.addEventListener('horario-updated', onHorarioUpdated);

// --- Horarios: Ver en modal (no navegar) — se abre el modal solo cuando ya está el contenido ---
// Usa horarioDetalleModal/horarioDetalleModalContent O horarioVerModal/horarioVerModalBody (vista Horarios index)
document.addEventListener('click', (e) => {
    const btn = e.target.closest('.btn-view[data-show-url]');
    if (!btn || !btn.closest('.schedule-table')) return;
    e.preventDefault();
    e.stopPropagation();
    const url = btn.getAttribute('data-show-url');
    if (!url) return;
    const modal = document.getElementById('horarioDetalleModal') || document.getElementById('horarioVerModal');
    const content = document.getElementById('horarioDetalleModalContent') || document.getElementById('horarioVerModalBody');
    if (!modal || !content) return;
    fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
        .then((r) => r.ok ? r.json() : Promise.reject(new Error('Error al cargar')))
        .then((data) => {
            function escapeHtml(text) {
                if (text == null) return '—';
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }
            let html = '<dl class="career-view-dl career-view-dl--styled">';
            html += '<div class="career-view-row"><dt>Carrera:</dt><dd>' + escapeHtml(data.carrera) + '</dd></div>';
            html += '<div class="career-view-row"><dt>Clasificación:</dt><dd>' + escapeHtml(data.clasificacion) + '</dd></div>';
            html += '<div class="career-view-row"><dt>Materia:</dt><dd>' + escapeHtml(data.materia) + '</dd></div>';
            html += '<div class="career-view-row"><dt>Docente:</dt><dd>' + escapeHtml(data.docente) + '</dd></div>';
            html += '<div class="career-view-row"><dt>Aula:</dt><dd>' + escapeHtml(data.aula) + '</dd></div>';
            html += '<div class="career-view-row career-view-row--no-border"><dt class="career-view-dt--gold">Horario:</dt><dd>';
            if (!data.franjas || data.franjas.length === 0) {
                html += '<p style="color: #666;">No hay franjas registradas.</p>';
            } else {
                html += '<table class="tabla-base tabla-rayas tabla-bordes" style="width: 100%; margin-top: 0.5rem;"><thead><tr><th>Día</th><th>Hora inicio</th><th>Hora fin</th></tr></thead><tbody>';
                data.franjas.forEach((f) => {
                    html += '<tr><td>' + escapeHtml(f.dia_str) + '</td><td>' + escapeHtml(f.hora_inicio) + '</td><td>' + escapeHtml(f.hora_fin) + '</td></tr>';
                });
                html += '</tbody></table>';
            }
            html += '</dd></div></dl>';
            content.innerHTML = html;
            modal.style.display = 'flex';
            modal.setAttribute('aria-hidden', 'false');
        })
        .catch(() => {
            content.innerHTML = '<div style="padding: 1rem; color: #c00;">Error al cargar el detalle del horario.</div>';
            modal.style.display = 'flex';
            modal.setAttribute('aria-hidden', 'false');
        });
}, true);
document.addEventListener('click', (e) => {
    const closeBtn = e.target.closest('.horario-detalle-modal-close') || e.target.closest('.horario-modal-close');
    if (closeBtn) {
        const modal = document.getElementById('horarioDetalleModal') || document.getElementById('horarioVerModal');
        if (modal) { modal.style.display = 'none'; modal.setAttribute('aria-hidden', 'true'); }
    }
    if (e.target.id === 'horarioDetalleModal' || e.target.id === 'horarioVerModal') {
        e.target.style.display = 'none';
        e.target.setAttribute('aria-hidden', 'true');
    }
});
document.addEventListener('keydown', (e) => {
    if (e.key !== 'Escape') return;
    const modal = document.getElementById('horarioDetalleModal') || document.getElementById('horarioVerModal');
    if (modal && modal.style.display === 'flex') {
        modal.style.display = 'none';
        modal.setAttribute('aria-hidden', 'true');
    }
    const editModal = document.getElementById('horarioEditModal');
    if (editModal && editModal.style.display === 'flex') {
        editModal.style.display = 'none';
        editModal.setAttribute('aria-hidden', 'true');
        const content = document.getElementById('horarioEditContent');
        if (content) content.innerHTML = '';
    }
});

// --- Horarios: Editar en modal — contenido en div horarioEditContent (fetch) ---
document.addEventListener('click', (e) => {
    const btn = e.target.closest('.btn-edit[data-edit-url]');
    if (!btn || !btn.closest('.schedule-table')) return;
    const modal = document.getElementById('horarioEditModal');
    const content = document.getElementById('horarioEditContent');
    if (!modal || !content) return;

    const url = btn.getAttribute('data-edit-url');
    if (!url) return;

    e.preventDefault();
    e.stopPropagation();

    // Mostrar el modal solo cuando el contenido esté cargado (no mostrar "Cargando...")
    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }, credentials: 'same-origin' })
        .then((r) => r.ok ? r.text() : Promise.reject(new Error('Error')))
        .then((html) => {
            content.innerHTML = html;
            if (typeof window.initHorarioEditForm === 'function') window.initHorarioEditForm(content);
            modal.style.display = 'flex';
            modal.setAttribute('aria-hidden', 'false');
        })
        .catch(() => {
            content.innerHTML = '<p style="padding:1rem; color:#b00;">No se pudo cargar el formulario.</p>';
            modal.style.display = 'flex';
            modal.setAttribute('aria-hidden', 'false');
        });
}, true);

window.initHorarioEditForm = function(container) {
    if (!container) return;
    if (typeof window.initScheduleClockPickersForContainer === 'function') window.initScheduleClockPickersForContainer(container);
    const form = container.querySelector('#schedule_form');
    if (form && form.getAttribute('data-initial-franjas')) {
        if (typeof scheduleGetState === 'function') scheduleGetState(form);
        if (typeof scheduleRenderPreview === 'function') scheduleRenderPreview(form);
    }
    const carreraSelect = container.querySelector('#carrera_select');
    const materiaSelect = container.querySelector('#materia_select');
    const docenteSelect = container.querySelector('#docente_select');
    if (carreraSelect && materiaSelect && docenteSelect && typeof initHorariosCareerFilterForContainer === 'function') {
        initHorariosCareerFilterForContainer(container);
    }
    if (form && container.closest('#horarioEditModal')) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const { franjas } = typeof scheduleGetState === 'function' ? scheduleGetState(form) : { franjas: [] };
            let hidden = form.querySelector('input[name="franjas_json"]');
            if (!hidden) {
                hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'franjas_json';
                form.appendChild(hidden);
            }
            hidden.value = JSON.stringify(form._scheduleFranjas || franjas);
            const submitBtn = form.querySelector('#save_schedule_btn');
            if (submitBtn) submitBtn.disabled = true;
            const formData = new FormData(form);
            fetch(form.action, { method: 'POST', body: formData, headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }, credentials: 'same-origin' })
                .then((r) => r.json().then((data) => ({ ok: r.ok, data })))
                .then(({ ok, data }) => {
                    if (submitBtn) submitBtn.disabled = false;
                    if (ok && data.success) {
                        if (typeof onHorarioUpdated === 'function') onHorarioUpdated();
                    } else {
                        scheduleShowMessage(form, data.message || 'Error al actualizar el horario.');
                    }
                })
                .catch(() => {
                    if (submitBtn) submitBtn.disabled = false;
                    
                });
        });
    }
};
/** data-career-id puede ser un id o varios separados por coma (docentes con varias carreras). */
function horarioDocenteMatchesCareer(careerIdsCsv, selectedCareerId) {
    const sel = String(selectedCareerId || '').trim();
    if (!sel) return true;
    const csv = String(careerIdsCsv || '').trim();
    if (!csv) return false;
    return csv.split(',').map((s) => s.trim()).filter(Boolean).includes(sel);
}

function initHorariosCareerFilterForContainer(container) {
    const clasificacionSelect = container.querySelector('#clasificacion_select');
    const carreraSelect = container.querySelector('#carrera_select');
    const materiaSelect = container.querySelector('#materia_select');
    const docenteSelect = container.querySelector('#docente_select');
    if (!carreraSelect || !materiaSelect || !docenteSelect) return;
    const form = container.querySelector('#schedule_form');
    if (!form || form.dataset.careerFilterInit === '1') return;
    form.dataset.careerFilterInit = '1';
    const materiasData = [];
    const docentesData = [];
    const carrerasData = [];
    Array.from(carreraSelect.options).forEach((opt, i) => {
        if (i === 0) return;
        carrerasData.push({
            value: opt.value,
            text: opt.textContent.trim(),
            classificationId: String(opt.getAttribute('data-classification-id') || ''),
        });
    });
    Array.from(materiaSelect.options).forEach((opt, i) => {
        if (i === 0) return;
       materiasData.push({ value: opt.value, text: opt.textContent.trim(), careerId: String(opt.getAttribute('data-career-id') || ''), semestre: String(opt.getAttribute('data-semestre') || '') });
    });
    Array.from(docenteSelect.options).forEach((opt, i) => {
        if (i === 0) return;
        docentesData.push({ value: opt.value, text: opt.textContent.trim(), careerId: String(opt.getAttribute('data-career-id') || '') });
    });
    function filterCarrerasByClassification(resetValues = true) {
        if (!clasificacionSelect) return;
        const classificationId = String(clasificacionSelect.value || '');
        const savedCareerId = carreraSelect.value;
        const carrerasFiltered = classificationId
            ? carrerasData.filter((c) => c.classificationId === classificationId)
            : carrerasData;
        carreraSelect.innerHTML = '';
        carreraSelect.appendChild(new Option('Seleccione una Carrera', '', true));
        carrerasFiltered.forEach((c) => {
            const o = new Option(c.text, c.value, false);
            o.setAttribute('data-classification-id', c.classificationId || '');
            carreraSelect.appendChild(o);
        });
        if (!resetValues && savedCareerId && carrerasFiltered.some((c) => c.value === savedCareerId)) {
            carreraSelect.value = savedCareerId;
        } else if (resetValues) {
            carreraSelect.value = '';
        }
    }
    function populateSemestres(resetValues) {
        const semestreSelect = document.getElementById('semestre_filter_select');
        if (!semestreSelect) return;
        const careerId = String(carreraSelect.value || '');
        const materiasDeCarrera = careerId ? materiasData.filter((m) => m.careerId === careerId) : materiasData;
        const semestresUnicos = [...new Set(materiasDeCarrera.map((m) => m.semestre).filter(Boolean))].sort((a, b) => parseInt(a) - parseInt(b));
        const savedSemestre = semestreSelect.value;
        semestreSelect.innerHTML = '';
        semestreSelect.appendChild(new Option('Todos los semestres', '', true));
        semestresUnicos.forEach((s) => {
            semestreSelect.appendChild(new Option('Semestre ' + s, s, false));
        });
        if (!resetValues && savedSemestre && semestresUnicos.includes(savedSemestre)) {
            semestreSelect.value = savedSemestre;
        } else if (resetValues) {
            semestreSelect.value = '';
        }
    }

    function filterByCareer(resetValues = true) {
        const careerId = String(carreraSelect.value || '');
        const semestreSelect = document.getElementById('semestre_filter_select');
        const semestreValue = semestreSelect ? String(semestreSelect.value || '') : '';
        const savedMateriaId = materiaSelect.value;
        const savedDocenteId = docenteSelect.value;

        let materiasFiltered = careerId ? materiasData.filter((m) => m.careerId === careerId) : materiasData;
        if (semestreValue) {
            materiasFiltered = materiasFiltered.filter((m) => m.semestre === semestreValue);
        }
        const docentesFiltered = careerId ? docentesData.filter((d) => horarioDocenteMatchesCareer(d.careerId, careerId)) : docentesData;

        materiaSelect.innerHTML = '';
        docenteSelect.innerHTML = '';
        materiaSelect.appendChild(new Option('Seleccione el nombre de la Materia', '', true));
        materiasFiltered.forEach((m) => {
            const o = new Option(m.text, m.value, false);
            o.setAttribute('data-career-id', m.careerId || '');
            o.setAttribute('data-semestre', m.semestre || '');
            materiaSelect.appendChild(o);
        });
        docenteSelect.appendChild(new Option('Seleccione el nombre del docente', '', true));
        docentesFiltered.forEach((d) => docenteSelect.appendChild(new Option(d.text, d.value, false)));
        if (!resetValues && savedMateriaId && materiasFiltered.some((m) => m.value === savedMateriaId)) materiaSelect.value = savedMateriaId;
        if (!resetValues && savedDocenteId && docentesFiltered.some((d) => d.value === savedDocenteId)) docenteSelect.value = savedDocenteId;
        if (typeof window.refreshScheduleAulaOptionsFromForm === 'function') {
            window.refreshScheduleAulaOptionsFromForm(form);
        }
    }
    if (clasificacionSelect) {
        clasificacionSelect.addEventListener('change', () => {
            filterCarrerasByClassification(true);
            filterByCareer(true);
        });
    }
    carreraSelect.addEventListener('change', () => filterByCareer(true));
    materiaSelect.addEventListener('change', () => {
        if (typeof window.refreshScheduleAulaOptionsFromForm === 'function') {
            window.refreshScheduleAulaOptionsFromForm(form);
        }
    });
    filterCarrerasByClassification(false);
    populateSemestres(false);
    filterByCareer(false);
}
document.addEventListener('click', (e) => {
    const closeBtn = e.target.closest('.horario-edit-modal-close') || e.target.closest('#horarioEditModal .schedule-edit-close');
    if (closeBtn || (e.target.closest('.horario-modal-close') && e.target.closest('#horarioEditModal'))) {
        const modal = document.getElementById('horarioEditModal');
        if (modal) {
            modal.style.display = 'none';
            modal.setAttribute('aria-hidden', 'true');
            const content = document.getElementById('horarioEditContent');
            if (content) content.innerHTML = '';
        }
    }
    if (e.target.id === 'horarioEditModal') {
        e.target.style.display = 'none';
        e.target.setAttribute('aria-hidden', 'true');
        const content = document.getElementById('horarioEditContent');
        if (content) content.innerHTML = '';
    }
});

// --- Horarios: Ver en modal (legacy: data-schedule-show-url + scheduleModal) ---
document.addEventListener('click', (e) => {
    const btn = e.target.closest('.btn-view[data-schedule-show-url], .btn-view[data-show-url]');
    if (!btn || !btn.closest('.schedule-table')) return;
    e.preventDefault();
    const url = btn.getAttribute('data-schedule-show-url') || btn.getAttribute('data-show-url');
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
    const btn = e.target.closest('.btn-edit[data-schedule-edit-url], .btn-edit[data-edit-url]');
    if (!btn || !btn.closest('.schedule-table')) return;
    e.preventDefault();
    const url = btn.getAttribute('data-schedule-edit-url') || btn.getAttribute('data-edit-url');
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

// --- Horarios: botones de día (Lun, Mar, Mié…) — delegación ---
document.addEventListener('click', (e) => {
    const btn = e.target.closest('.day-selection-buttons button[data-day]');
    if (!btn) return;
    btn.classList.toggle('selected');
    const form = btn.closest('#schedule_form') || btn.closest('form.schedule-form');
    if (form && typeof scheduleUpdatePreviewSelection === 'function') scheduleUpdatePreviewSelection(form);
});
// --- Horarios: actualizar vista previa cuando cambian las horas de inicio/fin ---
document.addEventListener('input', (e) => {
    if (e.target.matches('input[name="hora_inicio"], input[name="hora_fin"]')) {
        const form = e.target.closest('#schedule_form') || e.target.closest('form.schedule-form');
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
        table.innerHTML = '<tr><th class="schedule-preview-empty" style="color: #ACACAC; font-size: 0.9rem; font-weight: normal; margin: 0; padding: 8px 12px; text-align: left; border: none; background: transparent; text-transform: capitalize; display: flex; align-items: center; justify-content: space-between; gap: 10px;"><span>Lunes – Martes – Miércoles -- 07:00 – 08:00</span><img src="/images/icons/trash-solid-full.svg" class="schedule-preview-empty__icon" width="18" height="18" alt="Eliminar" style="flex-shrink: 0;" /></th></tr>';
        container.appendChild(table);
        scheduleUpdatePreviewSelection(form);
        return;
    }
    const deleteSvg = '<img src="/images/icons/Vector.svg" class="schedule-preview-card__icon schedule-preview-empty__icon" width="18" height="18" alt="" style="flex-shrink: 0;" />';
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
/** Muestra un mensaje estilizado temporal en el formulario de horarios (reemplaza alert). */
function scheduleShowMessage(form, message, type) {
    type = type || 'error';
    var container = form ? form.closest('.schedule-lists') || form.parentElement : document.body;
    var existing = container.querySelector('.schedule-inline-msg');
    if (existing) existing.remove();
    var div = document.createElement('div');
    div.className = 'schedule-inline-msg';
    var bgColor = type === 'error' ? '#fff3f3' : '#f3fff3';
    var borderColor = type === 'error' ? '#e74c3c' : '#27ae60';
    var textColor = type === 'error' ? '#c0392b' : '#1e8449';
    div.style.cssText = 'padding:12px 16px;margin:10px 0;border-radius:8px;border:1px solid ' + borderColor + ';background:' + bgColor + ';color:' + textColor + ';font-size:0.9rem;font-weight:500;display:flex;align-items:center;justify-content:space-between;gap:8px;';
    var span = document.createElement('span');
    span.textContent = message;
    var closeBtn = document.createElement('button');
    closeBtn.type = 'button';
    closeBtn.textContent = '✕';
    closeBtn.style.cssText = 'background:none;border:none;font-size:1.1rem;cursor:pointer;color:' + textColor + ';padding:0 4px;';
    closeBtn.addEventListener('click', function() { div.remove(); });
    div.appendChild(span);
    div.appendChild(closeBtn);
    var scheduleSettings = form ? form.querySelector('.schedule-settings') : null;
    if (scheduleSettings) {
        scheduleSettings.parentNode.insertBefore(div, scheduleSettings);
    } else {
        container.prepend(div);
    }
    setTimeout(function() { if (div.parentNode) div.remove(); }, 5000);
}
function scheduleClearTimeForm(form) {
    if (!form) return;
    document.querySelectorAll('.time-picker-dropdown.is-open').forEach(function(el) { el.classList.remove('is-open'); });
    const hi = form.querySelector('input[name="hora_inicio"]');
    const hf = form.querySelector('input[name="hora_fin"]');
    if (hi) { hi.value = '00:00'; hi.dispatchEvent(new Event('input', { bubbles: true })); }
    if (hf) { hf.value = '00:00'; hf.dispatchEvent(new Event('input', { bubbles: true })); }
    form.querySelectorAll('.day-selection-buttons button.selected').forEach((btn) => btn.classList.remove('selected'));
    form.querySelectorAll('.time-input-wrap[data-time-input]').forEach((wrap) => {
        if (typeof wrap._scheduleSyncFromInput === 'function') wrap._scheduleSyncFromInput();
    });
}

document.addEventListener('click', (e) => {
    const addBtn = e.target.closest('.add-time-slot-btn');
    if (addBtn) {
        e.preventDefault();
        const form = addBtn.closest('#schedule_form') || addBtn.closest('form.schedule-form');
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
            scheduleShowMessage(form, 'Por favor, selecciona al menos un día y las horas de inicio y fin.');
            return;
        }
        if (horaInicio === horaFin) {
            scheduleShowMessage(form, 'La hora de inicio y la hora de fin deben ser distintas.');
            return;
        }
   // Validar solapamiento de horarios en el mismo día (permite mismo día si no se cruzan las horas)
        var conflictos = [];
        franjas.forEach(function (f) {
            var existingDays = (f.dias_semana || []).map(function (d) { return parseInt(d, 10); });
            var fInicio = (f.hora_inicio || '').substring(0, 5);
            var fFin = (f.hora_fin || '').substring(0, 5);
            diasSeleccionados.forEach(function (newDay) {
                if (existingDays.indexOf(newDay) !== -1) {
                    if (horaInicio < fFin && fInicio < horaFin) {
                        conflictos.push(scheduleGetNombreDia(newDay));
                    }
                }
            });
        });
        if (conflictos.length > 0) {
            var nombresConflicto = [];
            conflictos.forEach(function (n) { if (nombresConflicto.indexOf(n) === -1) nombresConflicto.push(n); });
            scheduleShowMessage(form, 'Hay un conflicto de horario en: ' + nombresConflicto.join(', ') + '. Los horarios se solapan en el mismo día.');
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
        const form = delBtn.closest('#schedule_form') || delBtn.closest('form.schedule-form');
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
        const form = saveBtn.closest('#schedule_form') || saveBtn.closest('form.schedule-form');
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
        const action = form.getAttribute('action');
        if (!action) return;
        const indexUrl = form.getAttribute('data-index-url');
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const fd = new FormData(form);
        fetch(action, {
            method: 'POST',
            body: fd,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                Accept: 'application/json',
                'X-CSRF-TOKEN': token,
            },
        })
            .then(async (res) => {
                const data = await res.json().catch(() => ({}));
                const ok = res.ok && (data.ok === true || data.success === true);
                if (ok) {
                    try {
                        delete window.afterCareerSuccessModalOk;
                    } catch (e) {
                        window.afterCareerSuccessModalOk = undefined;
                    }
                    const isScheduleMainForm = form.id === 'schedule_form' && !form.closest('#horarioEditModal');
                    if (isScheduleMainForm) {
                        window.afterCareerSuccessModalOk = function () {
                            window.location.reload();
                        };
                    }
                    const msg = data.message || 'Horario guardado correctamente.';
                    const showSuccessModal = () => {
                        const successModal = document.getElementById('careerSuccessModal');
                        const successModalMessage = document.getElementById('careerSuccessModalMessage');
                        if (successModal && successModalMessage) {
                            successModalMessage.textContent = msg;
                            successModal.style.display = 'flex';
                        }
                    };
                    if (indexUrl) {
                        fetch(indexUrl, {
                            headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'text/html' },
                        })
                            .then((r) => r.text())
                            .then((html) => {
                                const parser = new DOMParser();
                                const doc = parser.parseFromString(html, 'text/html');
                                const newMain = doc.querySelector('#main-content, .main-content');
                                const main = document.querySelector('#main-content, .main-content');
                                if (main && newMain) {
                                    main.innerHTML = newMain.innerHTML;
                                    const t = newMain.querySelector('input[name="_token"]');
                                    const meta = document.querySelector('meta[name="csrf-token"]');
                                    if (t && t.value && meta) meta.setAttribute('content', t.value);
                                    if (typeof window.initScheduleClockPickersForContainer === 'function') {
                                        window.initScheduleClockPickersForContainer(main);
                                    }
                                }
                                document.title = doc.querySelector('title')?.textContent || document.title;
                                if (window.spaNav && typeof window.spaNav.updateActiveMenuItem === 'function') {
                                    window.spaNav.updateActiveMenuItem();
                                }
                                if (window.spaNav && typeof window.spaNav.clearCache === 'function') {
                                    window.spaNav.clearCache();
                                }
                                showSuccessModal();
                            })
                            .catch(() => showSuccessModal());
                    } else {
                        if (form.closest('#horarioEditModal')) {
                            window.afterCareerSuccessModalOk = function () {
                                if (typeof onHorarioUpdated === 'function') onHorarioUpdated();
                            };
                        }
                        showSuccessModal();
                    }
                    return;
                }
                if (res.status === 422) {
                    let errMsg = data.message || '';
                    if (!errMsg && data.errors) {
                        const first = Object.values(data.errors).flat()[0];
                        errMsg = first || '';
                    }
                    scheduleShowMessage(form, errMsg || 'Revisa el formulario de horario.');
                    return;
                }
            })
            .catch(() => {
                scheduleShowMessage(form || document.querySelector('#schedule_form'), 'Error de red. Intente de nuevo.');
            });
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
function scheduleUpdateAulaPlaceholderStyle(aulaSel) {
    if (!aulaSel) return;
    if (aulaSel.value === '') aulaSel.classList.add('select-placeholder');
    else aulaSel.classList.remove('select-placeholder');
}

window.refreshScheduleAulaOptionsFromForm = function (form) {
    if (!form) form = document.getElementById('schedule_form');
    if (!form) return;
    const url = form.getAttribute('data-aulas-url');
    const aulaSel = form.querySelector('#aula_select');
    const carreraSel = form.querySelector('#carrera_select');
    const materiaSel = form.querySelector('#materia_select');
    if (!aulaSel) return;
    if (!url) {
        scheduleUpdateAulaPlaceholderStyle(aulaSel);
        return;
    }
    let careerId = carreraSel && carreraSel.value;
    const materiaId = materiaSel && materiaSel.value;
    if (materiaSel && materiaSel.selectedIndex > 0 && (!careerId || String(careerId).trim() === '')) {
        const opt = materiaSel.options[materiaSel.selectedIndex];
        const fromMat = opt && opt.getAttribute('data-career-id');
        if (fromMat) careerId = fromMat;
    }
    let prevAula = String(aulaSel.value || '').trim();
    if (!prevAula) {
        const persisted = form.getAttribute('data-initial-aula-id');
        if (persisted && String(persisted).trim() !== '') {
            prevAula = String(persisted).trim();
        }
    }
    if (!materiaId) {
        aulaSel.innerHTML = '';
        aulaSel.appendChild(new Option('Seleccione el Aula', '', true, true));
        aulaSel.disabled = false;
        scheduleUpdateAulaPlaceholderStyle(aulaSel);
        return;
    }
    aulaSel.disabled = true;
    aulaSel.innerHTML = '';
    aulaSel.appendChild(new Option('Cargando…', '', true, true));
    let u;
    try {
        u = new URL(url, window.location.origin);
    } catch (e) {
        aulaSel.innerHTML = '';
        aulaSel.appendChild(new Option('Error de configuración', '', true, true));
        aulaSel.disabled = false;
        return;
    }
    u.searchParams.set('materia_id', materiaId);
    if (careerId) u.searchParams.set('career_id', String(careerId));
    fetch(u.toString(), {
        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin',
    })
        .then((r) => {
            if (!r.ok) throw new Error('bad');
            return r.json();
        })
        .then((data) => {
            const list = data && data.aulas ? data.aulas : [];
            aulaSel.innerHTML = '';
            if (!list.length) {
                aulaSel.appendChild(new Option('Seleccione el Aula', '', true, true));
                if (prevAula) {
                    const fb = form.getAttribute('data-initial-aula-label');
                    if (fb) {
                        aulaSel.appendChild(new Option(fb, String(prevAula), false, false));
                        aulaSel.value = String(prevAula);
                    }
                }
                if (form.getAttribute('data-initial-aula-id')) {
                    form.removeAttribute('data-initial-aula-id');
                    form.removeAttribute('data-initial-aula-label');
                }
                aulaSel.disabled = false;
                scheduleUpdateAulaPlaceholderStyle(aulaSel);
                return;
            }
            aulaSel.appendChild(new Option('Seleccione el Aula', '', true, true));
            list.forEach((a) => {
                aulaSel.appendChild(new Option(a.label, String(a.id), false));
            });
            const inList = prevAula && list.some((x) => String(x.id) === String(prevAula));
            if (prevAula && !inList) {
                const fb = form.getAttribute('data-initial-aula-label');
                if (fb) {
                    aulaSel.appendChild(new Option(fb, String(prevAula), false, false));
                }
            }
            const canSelect = prevAula && Array.from(aulaSel.options).some((o) => String(o.value) === String(prevAula));
            if (canSelect) {
                aulaSel.value = String(prevAula);
            } else {
                aulaSel.value = '';
            }
            if (form.getAttribute('data-initial-aula-id')) {
                form.removeAttribute('data-initial-aula-id');
                form.removeAttribute('data-initial-aula-label');
            }
            aulaSel.disabled = false;
            scheduleUpdateAulaPlaceholderStyle(aulaSel);
        })
        .catch(() => {
            aulaSel.innerHTML = '';
            aulaSel.appendChild(new Option('Error al cargar aulas', '', true, true));
            aulaSel.disabled = false;
            scheduleUpdateAulaPlaceholderStyle(aulaSel);
        });
};

function initHorariosCareerFilter() {
    const clasificacionSelect = document.getElementById('clasificacion_select');
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
    const carrerasData = [];
    Array.from(carreraSelect.options).forEach((opt, i) => {
        if (i === 0) return;
        carrerasData.push({
            value: opt.value,
            text: opt.textContent.trim(),
            classificationId: String(opt.getAttribute('data-classification-id') || ''),
        });
    });
    Array.from(materiaSelect.options).forEach((opt, i) => {
        if (i === 0) return;
       materiasData.push({ value: opt.value, text: opt.textContent.trim(), careerId: String(opt.getAttribute('data-career-id') || ''), semestre: String(opt.getAttribute('data-semestre') || '') });
    });
    Array.from(docenteSelect.options).forEach((opt, i) => {
        if (i === 0) return;
        docentesData.push({ value: opt.value, text: opt.textContent.trim(), careerId: String(opt.getAttribute('data-career-id') || '') });
    });

    function filterCarrerasByClassification(resetValues = true) {
        if (!clasificacionSelect) return;
        const classificationId = String(clasificacionSelect.value || '');
        const savedCareerId = carreraSelect.value;
        const carrerasFiltered = classificationId
            ? carrerasData.filter((c) => c.classificationId === classificationId)
            : carrerasData;   
        carreraSelect.innerHTML = '';
        carreraSelect.appendChild(new Option('Seleccione una Carrera', '', true));
        carrerasFiltered.forEach((c) => {
            const o = new Option(c.text, c.value, false);
            o.setAttribute('data-classification-id', c.classificationId || '');
            carreraSelect.appendChild(o);
        });
        if (!resetValues && savedCareerId && carrerasFiltered.some((c) => c.value === savedCareerId)) {
            carreraSelect.value = savedCareerId;
        } else if (resetValues) {
            carreraSelect.value = '';
        }
    }
function populateSemestres(resetValues) {
        const semestreSelect = document.getElementById('semestre_filter_select');
        if (!semestreSelect) return;
        const careerId = String(carreraSelect.value || '');
        const materiasDeCarrera = careerId ? materiasData.filter((m) => m.careerId === careerId) : materiasData;
        const semestresUnicos = [...new Set(materiasDeCarrera.map((m) => m.semestre).filter(Boolean))].sort((a, b) => parseInt(a) - parseInt(b));
        const savedSemestre = semestreSelect.value;
        semestreSelect.innerHTML = '';
        semestreSelect.appendChild(new Option('Todos los semestres', '', true));
        semestresUnicos.forEach((s) => {
            semestreSelect.appendChild(new Option('Semestre ' + s, s, false));
        });
        if (!resetValues && savedSemestre && semestresUnicos.includes(savedSemestre)) {
            semestreSelect.value = savedSemestre;
        } else if (resetValues) {
            semestreSelect.value = '';
        }
    }
    function filterByCareer(resetValues = true) {
        const careerId = String(carreraSelect.value || '');
        const savedMateriaId = materiaSelect.value;
        const savedDocenteId = docenteSelect.value;
        const materiasFiltered = careerId ? materiasData.filter((m) => m.careerId === careerId) : materiasData;
        const docentesFiltered = careerId ? docentesData.filter((d) => horarioDocenteMatchesCareer(d.careerId, careerId)) : docentesData;
        materiaSelect.innerHTML = '';
        docenteSelect.innerHTML = '';
        materiaSelect.appendChild(new Option('Seleccione el nombre de la Materia', '', true));
        materiasFiltered.forEach((m) => {
            const o = new Option(m.text, m.value, false);
            o.setAttribute('data-career-id', m.careerId || '');
            materiaSelect.appendChild(o);
        });
        docenteSelect.appendChild(new Option('Seleccione el nombre del docente', '', true));
        docentesFiltered.forEach((d) => docenteSelect.appendChild(new Option(d.text, d.value, false)));
        if (!resetValues && savedMateriaId && materiasFiltered.some((m) => m.value === savedMateriaId)) materiaSelect.value = savedMateriaId;
        if (!resetValues && savedDocenteId && docentesFiltered.some((d) => d.value === savedDocenteId)) docenteSelect.value = savedDocenteId;
        if (typeof window.refreshScheduleAulaOptionsFromForm === 'function') {
            window.refreshScheduleAulaOptionsFromForm(form);
        }
    }
    if (clasificacionSelect) {
        clasificacionSelect.addEventListener('change', () => {
            filterCarrerasByClassification(true);
            populateSemestres(true);
            filterByCareer(true);
        });
    }
    carreraSelect.addEventListener('change', () => {
        populateSemestres(true);
        filterByCareer(true);
    });
    const semestreFilterSelect = document.getElementById('semestre_filter_select');
    if (semestreFilterSelect) {
        semestreFilterSelect.addEventListener('change', () => filterByCareer(true));
    }
    materiaSelect.addEventListener('change', () => {
        if (typeof window.refreshScheduleAulaOptionsFromForm === 'function') {
            window.refreshScheduleAulaOptionsFromForm(form);
        }
    });
    
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
    wrap._scheduleSyncFromInput = syncFromInput;
    syncFromInput();
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
