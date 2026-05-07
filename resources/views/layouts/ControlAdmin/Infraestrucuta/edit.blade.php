@extends('layouts.app')

@section('title', 'Editar aula - ' . session('active_institution_name'))

@vite(['resources/css/Cursos/courses.css', 'resources/js/app.js'])

@section('content')
@php
    $carreras = $carreras ?? collect();
@endphp
<div class="container">
    <div class="content-header">
        <div class="content-title">
            <h3>Editar aula</h3>
        </div>
        <div class="header-option">
            <a href="{{ route('control.facilities.show', $facility) }}" class="mi-boton" style="text-decoration: none; display: inline-block;">Ver detalle</a>
            <a href="{{ route('control.facilities.index') }}" class="mi-boton" style="text-decoration: none; display: inline-block;">Listado</a>
        </div>
    </div>

    <div style="max-width: 480px; margin-top: 1.25rem;">
        <form id="facilityEditFullPageForm" method="POST" action="{{ route('control.facilities.update', $facility) }}">
            @csrf
            @method('PUT')

            <div class="form-group" style="margin-bottom: 1rem;">
                <label for="nombre_aula">Nombre del aula</label>
                <input type="text" id="nombre_aula" name="nombre_aula" class="form-control" maxlength="255" required value="{{ old('nombre_aula', $facility->nombre_aula) }}">
            </div>
            <div class="form-group" style="margin-bottom: 1rem;">
                <label for="career_id">Carrera</label>
                <select id="career_id" name="career_id" class="form-control">
                    <option value="">Ingrese la carrera</option>
                    @foreach ($carreras as $c)
                        <option value="{{ $c->id }}" @selected(old('career_id', $facility->career_id) == $c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 1rem;">
                <label for="tipo_materia">Materia</label>
                <select id="tipo_materia" name="tipo_materia" class="form-control" data-preselected="{{ e($facility->tipo_materia ?? '') }}">
                    <option value="">Cargando…</option>
                </select>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger" style="margin-bottom: 1rem;">
                    <ul style="margin: 0; padding-left: 1.25rem;">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <button type="submit" class="mi-boton" style="border: none; cursor: pointer;">+ Guardar</button>
        </form>
    </div>
</div>
@push('scripts')
@include('layouts.ControlAdmin.Infraestrucuta.components._aulas_materias_select_script')
<script>
(function () {
    function initAulaEditFullPageMaterias() {
        var car = document.getElementById('career_id');
        var mat = document.getElementById('tipo_materia');
        if (!car || !mat || typeof window.umiAulasFillMateriaSelect !== 'function') {
            return;
        }
        window.umiAulasFillMateriaSelect(car.value || '', mat, mat.getAttribute('data-preselected') || '');
        car.addEventListener('change', function () {
            window.umiAulasFillMateriaSelect(car.value || '', mat, '');
        });
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAulaEditFullPageMaterias);
    } else {
        initAulaEditFullPageMaterias();
    }
})();
</script>
<script>
(function () {
    if (window.__umiFacilityEditFullPageSubmitBound) {
        return;
    }
    window.__umiFacilityEditFullPageSubmitBound = true;
    document.addEventListener('submit', function (event) {
        if (!event.target || event.target.id !== 'facilityEditFullPageForm') {
            return;
        }
        event.preventDefault();
        var form = event.target;
        var formData = new FormData(form);
        axios.post(form.action, formData, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        })
            .then(function (response) {
                var msg = (response.data && response.data.message) ? response.data.message : 'Aula actualizada correctamente.';
                var successModal = document.getElementById('careerSuccessModal');
                var successModalMessage = document.getElementById('careerSuccessModalMessage');
                if (successModal && successModalMessage) {
                    successModalMessage.textContent = msg;
                    window.afterCareerSuccessModalOk = function () {
                        window.location.href = '{{ route('control.facilities.index') }}';
                    };
                    successModal.style.display = 'flex';
                } else {
                    window.location.href = '{{ route('control.facilities.index') }}';
                }
            })
            .catch(function (error) {
                if (error.response && error.response.status === 422 && error.response.data && error.response.data.errors) {
                    var list = document.createElement('ul');
                    list.style.cssText = 'margin:0;padding-left:1.25rem;color:#b00020;';
                    Object.values(error.response.data.errors).forEach(function (msgs) {
                        msgs.forEach(function (m) {
                            var li = document.createElement('li');
                            li.textContent = m;
                            list.appendChild(li);
                        });
                    });
                    var box = document.getElementById('facility-edit-fullpage-errors');
                    if (!box) {
                        box = document.createElement('div');
                        box.id = 'facility-edit-fullpage-errors';
                        box.className = 'alert alert-danger';
                        box.style.cssText = 'margin-bottom: 1rem;';
                        form.parentNode.insertBefore(box, form);
                    }
                    box.innerHTML = '';
                    box.appendChild(list);
                    return;
                }
                window.alert('Error al guardar. Intenta de nuevo.');
            });
    });
})();
</script>
@endpush
@endsection
