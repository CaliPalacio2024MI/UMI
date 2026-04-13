{{-- Carga materias por carrera (fetch JSON). Reutilizable en índice y edición de aula. --}}
<script>
(function () {
    if (window.__umiAulasMateriasSelectInit) {
        return;
    }
    window.__umiAulasMateriasSelectInit = true;

    window.UMI_AULAS_MAT_URL = window.UMI_AULAS_MAT_URL || @json(route('control.facilities.materiasPorCarrera'));

    window.umiAulasFillMateriaSelect = function (careerId, selectEl, selectedNombre) {
        if (!selectEl) {
            return;
        }
        selectedNombre = selectedNombre ? String(selectedNombre) : '';
        selectEl.innerHTML = '';
        var opt0 = document.createElement('option');
        opt0.value = '';
        if (!careerId) {
            opt0.textContent = 'Seleccione la carrera';
            selectEl.appendChild(opt0);
            selectEl.disabled = true;
            return;
        }
        opt0.textContent = 'Seleccione una materia';
        selectEl.appendChild(opt0);
        selectEl.disabled = true;
        opt0.textContent = 'Cargando…';

        var u = window.UMI_AULAS_MAT_URL + '?career_id=' + encodeURIComponent(careerId);
        fetch(u, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        })
            .then(function (r) {
                if (!r.ok) {
                    throw new Error('HTTP ' + r.status);
                }
                return r.json();
            })
            .then(function (data) {
                selectEl.innerHTML = '';
                var o = document.createElement('option');
                o.value = '';
                o.textContent = 'Seleccione una materia';
                selectEl.appendChild(o);
                var list = (data && data.materias) ? data.materias : [];
                var found = false;
                list.forEach(function (m) {
                    var op = document.createElement('option');
                    op.value = m.nombre;
                    op.textContent = m.clave ? (m.nombre + ' (' + m.clave + ')') : m.nombre;
                    if (selectedNombre && selectedNombre === m.nombre) {
                        op.selected = true;
                        found = true;
                    }
                    selectEl.appendChild(op);
                });
                if (selectedNombre && !found) {
                    var legacy = document.createElement('option');
                    legacy.value = selectedNombre;
                    legacy.textContent = selectedNombre + ' (registro anterior)';
                    legacy.selected = true;
                    selectEl.insertBefore(legacy, selectEl.children[1] || null);
                }
                selectEl.disabled = false;
            })
            .catch(function () {
                selectEl.innerHTML = '';
                var err = document.createElement('option');
                err.value = '';
                err.textContent = 'No se pudieron cargar las materias';
                selectEl.appendChild(err);
                selectEl.disabled = true;
            });
    };

    document.addEventListener('change', function (e) {
        if (!e.target) {
            return;
        }
        if (e.target.id === 'career_id' && e.target.closest && e.target.closest('#createFacilityForm')) {
            var form = e.target.closest('#createFacilityForm');
            var mat = form.querySelector('#tipo_materia');
            window.umiAulasFillMateriaSelect(e.target.value, mat, null);
        }
        if (e.target.id === 'edit_career_id' && e.target.closest && e.target.closest('#editFacilityForm')) {
            var formE = e.target.closest('#editFacilityForm');
            var matE = formE.querySelector('#edit_tipo_materia');
            window.umiAulasFillMateriaSelect(e.target.value, matE, '');
        }
    });
})();
</script>
