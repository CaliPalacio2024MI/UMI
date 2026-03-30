@extends('layouts.app')

@section('title', 'Grupos')

@section('content')

<div class="container">

    <h1>Grupos</h1>
    <p>Selecciona un departamento</p>

    <div class="groups-container">

        {{-- ===================== DEPARTAMENTOS ===================== --}}
        <div class="page-container">

            <div class="page-header">
                <h1> Departamentos</h1>
            </div>

            <div class="grid">

                @forelse($departments as $department)
                    <div class="card dept-card" data-id="{{ $department->id }}">
                        <div class="card-title">{{ $department->name }}</div>
                    </div>
                @empty
                    <div class="empty">
                        No hay departamentos registrados
                    </div>
                @endforelse

            </div>

        </div>


        {{-- ===================== PUESTOS ===================== --}}
        <div class="group-card">

            <h4> Puestos</h4>

            <div id="workstations-container" class="grid">

                <div class="empty">
                    Selecciona un departamento
                </div>

            </div>

        </div>

       {{-- ===================== Participantes ===================== --}}


        <div class="group-card">

            <h4> Participantes</h4>

            <div id="participants-container" class="grid">

                <div class="empty">
                   Selecciona un puesto
                </div>

            </div>

        </div>

    </div>

</div>

@endsection
<script>
document.addEventListener("DOMContentLoaded", function () {

    const departments = @json($departments ?? []);

    // ===== DEPARTAMENTOS =====
    document.querySelectorAll('.dept-card').forEach(card => {
        card.addEventListener('click', function () {

            let deptId = this.dataset.id;

            document.querySelectorAll('.dept-card')
                .forEach(c => c.classList.remove('active-card'));

            this.classList.add('active-card');

            let dept = departments.find(d => d.id == deptId);

            let html = '';

            if (!dept || !dept.workstations || dept.workstations.length === 0) {
                html = `<div class="empty">No hay puestos</div>`;
            } else {
                dept.workstations.forEach(w => {
                    html += `
                        <div class="card work-card" data-id="${w.id}">
                            <div class="card-title">${w.name}</div>
                            <div class="card-text">Haz clic para ver participantes</div>
                        </div>
                    `;
                });
            }

            document.getElementById('workstations-container').innerHTML = html;

            document.getElementById('participants-container').innerHTML =
                `<div class="empty">Selecciona un puesto</div>`;

            attachWorkEvents();
        });
    });


    function attachWorkEvents() {
        document.querySelectorAll('.work-card').forEach(card => {
            card.addEventListener('click', function () {

                document.querySelectorAll('.work-card')
                    .forEach(c => c.classList.remove('active-card'));

                this.classList.add('active-card');

                // 🔥 Simulación (aquí luego conectas backend real)
                let html = `
                    <div class="card">
                        <div class="card-title">Juan Pérez</div>
                        <div class="card-text">Participante activo</div>
                    </div>

                    <div class="card">
                        <div class="card-title">María López</div>
                        <div class="card-text">Participante activo</div>
                    </div>
                `;

                document.getElementById('participants-container').innerHTML = html;
            });
        });
    }

});
</script>
<style>
.page-container {
    padding: 25px;
    max-width: 1200px;
    margin: auto;
    background: #f8fafc;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    padding: 15px 20px;
    background: linear-gradient(135deg, #1e3a8a, #2563eb);
    border-radius: 12px;
    color: white;
}

.btn-primary {
    background: white;
    color: #1e3a8a;
    padding: 10px 14px;
    border-radius: 8px;
    font-weight: 600;
    text-decoration: none;
}

.grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 18px;
}

.card {
    background: white;
    border-radius: 14px;
    padding: 18px;
    box-shadow: 0 3px 12px rgba(0,0,0,0.08);
    border-left: 5px solid #2563eb;
    transition: 0.3s;
    cursor: pointer;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(37, 99, 235, 0.25);
}

.card-title {
    font-size: 17px;
    font-weight: 700;
    color: #1e3a8a;
}

.card-text {
    font-size: 14px;
    color: #64748b;
}

.active-card {
    background: #eaf2ff;
    border-left: 6px solid #1d4ed8;
}

.group-card {
    background: white;
    padding: 15px;
    border-radius: 12px;
    box-shadow: 0 3px 12px rgba(0,0,0,0.08);
    margin-top: 15px;
}

.empty {
    text-align: center;
    padding: 30px;
    color: #94a3b8;
}
<>

/* ===== GRID GENERAL ===== */
.grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 18px;
}

/* ===== CARD BASE (TODOS USAN LA MISMA) ===== */
.card {
    background: white;
    border-radius: 14px;
    padding: 18px;
    box-shadow: 0 3px 12px rgba(0,0,0,0.08);
    border-left: 5px solid #2563eb;
    transition: 0.3s;
    cursor: pointer;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(37, 99, 235, 0.25);
}

/* ===== TEXTO ===== */
.card-title {
    font-size: 17px;
    font-weight: 700;
    color: #1e3a8a;
}

.card-text {
    font-size: 14px;
    color: #64748b;
}

/* ===== ACTIVOS ===== */
.active-card {
    background: #eaf2ff;
    border-left: 6px solid #1d4ed8;
}

/* ===== EMPTY ===== */
.empty {
    text-align: center;
    padding: 30px;
    color: #94a3b8;
}

/* ===== GROUP CARD ===== */
.group-card {
    background: white;
    padding: 15px;
    border-radius: 12px;
    margin-top: 15px;
    box-shadow: 0 3px 12px rgba(0,0,0,0.08);
}

.group-card h4 {
    color: #1e3a8a;
}

</style>
