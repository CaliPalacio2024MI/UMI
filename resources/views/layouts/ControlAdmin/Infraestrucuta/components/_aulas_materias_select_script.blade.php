{{-- Filtro clasificación → carreras, carga materias por carreras marcadas. --}}
<script>
(function () {
    if (window.__umiAulasCheckInit) return;
    window.__umiAulasCheckInit = true;

    var MAT_URL = @json(route('control.facilities.materiasPorCarrera'));

    function fetchJson(url) {
        return fetch(url, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        }).then(function (r) {
            if (!r.ok) throw new Error('HTTP ' + r.status);
            return r.json();
        });
    }

    /* ── Filtrar carreras por clasificación (show/hide) ── */
    window.umiAulasFilterCarreras = function (form, clasId) {
        var items = form.querySelectorAll('.aula-carreras-list .aula-check-item');
        items.forEach(function (item) {
            if (!clasId || clasId === '') {
                item.style.display = 'flex';
            } else {
                item.style.display = (item.dataset.classification == clasId) ? 'flex' : 'none';
            }
        });
    };

    /* ── Cargar materias por carreras marcadas ── */
    window.umiAulasLoadMaterias = function (form, preselectedIds) {
        preselectedIds = preselectedIds || [];
        var container = form.querySelector('.aula-materias-container');
        if (!container) return;

        var checks = form.querySelectorAll('.aula-career-check:checked');
        var ids = Array.from(checks).map(function (c) { return c.value; });

        if (ids.length === 0) {
            container.innerHTML = '<span class="text-muted">Seleccione al menos una carrera.</span>';
            return;
        }

        container.innerHTML = '<span class="text-muted">Cargando materias…</span>';

        var params = ids.map(function (id) { return 'career_ids[]=' + encodeURIComponent(id); }).join('&');

        fetchJson(MAT_URL + '?' + params)
            .then(function (data) {
                container.innerHTML = '';
                var list = (data && data.materias) ? data.materias : [];
                if (list.length === 0) {
                    container.innerHTML = '<span class="text-muted">No hay materias disponibles.</span>';
                    return;
                }
                list.forEach(function (m) {
                    var lbl = document.createElement('label');
                    lbl.className = 'aula-check-item';
                    lbl.style.cssText = 'display:flex;align-items:center;padding:8px 12px;margin:0;border-bottom:1px solid #f0f0f0;cursor:pointer;';

                    var inp = document.createElement('input');
                    inp.type = 'checkbox';
                    inp.className = 'aula-materia-check';
                    inp.name = 'materia_ids[]';
                    inp.value = m.id;
                    inp.style.cssText = 'margin-right:10px;width:16px;height:16px;accent-color:#b87a2b;';
                    if (preselectedIds.indexOf(m.id) !== -1) inp.checked = true;

                    var span = document.createElement('span');
                    span.textContent = m.clave ? (m.nombre + ' (' + m.clave + ')') : m.nombre;

                    lbl.appendChild(inp);
                    lbl.appendChild(span);
                    container.appendChild(lbl);
                });
            })
            .catch(function () {
                container.innerHTML = '<span class="text-danger">Error al cargar materias.</span>';
            });
    };

    /* ── Eventos delegados ── */
    document.addEventListener('change', function (e) {
        var t = e.target;
        if (!t) return;

        // Clasificación cambia → filtrar carreras visibles
        if (t.classList.contains('aula-clasificacion-select')) {
            var form = t.closest('form');
            if (form) {
                window.umiAulasFilterCarreras(form, t.value);
                // Desmarcar carreras ocultas para que no contaminen la carga de materias
                form.querySelectorAll('.aula-carreras-list .aula-check-item').forEach(function (item) {
                    if (item.style.display === 'none') {
                        var cb = item.querySelector('.aula-career-check');
                        if (cb) cb.checked = false;
                    }
                });
                window.umiAulasLoadMaterias(form, []);
            }
        }

        // Carrera checkbox cambia → recargar materias
        if (t.classList.contains('aula-career-check')) {
            var form2 = t.closest('form');
            if (form2) window.umiAulasLoadMaterias(form2, []);
        }
    });
})();
</script>