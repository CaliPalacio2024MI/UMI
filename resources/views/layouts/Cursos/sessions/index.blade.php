@extends('layouts.app')

@section('title', 'Horarios - ' . $course->title)

@section('content')

<div class="container">

    <h1>Horarios del curso: {{ $course->title }}</h1>

    {{-- MENSAJES --}}
    @if(session('success'))
        <div class="alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert-error">
            {{ session('error') }}
        </div>
    @endif


<div class="horarios-layout">


{{-- IZQUIERDA: TABLA DE HORARIOS --}}
<div class="horarios-tabla">

    <div class="card mt-4">
        <h3>Horarios registrados</h3>

        @if($sessions->isEmpty())
            <p>No hay horarios registrados.</p>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Inicio</th>
                        <th>Fin</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>
                @foreach($sessions as $session)

                <tr style="{{ !$session->attendance_enabled ? 'background:#f2f2f2;' : '' }}">
                    <td>{{ $session->date }}</td>
                    <td>{{ $session->start_time }}</td>
                    <td>{{ $session->end_time }}</td>

                    <td style="display:flex; gap:10px; justify-content:center;">


                        {{-- TOGGLE --}}
                        <form action="{{ route('courses.sessions.toggle', [$course, $session]) }}"
                              method="POST">
                            @csrf
                            @method('PATCH')

                            <button type="submit"
                                    style="border:none; background:none; cursor:pointer;">
                                @if($session->attendance_enabled)
                                    <i class="fa-solid fa-toggle-on"
                                       style="color:green; font-size:20px;"></i>
                                @else
                                    <i class="fa-solid fa-toggle-off"
                                       style="color:gray; font-size:20px;"></i>
                                @endif
                            </button>
                        </form>

                        {{-- EDITAR --}}
                        @if($session->attendance_enabled)
                            <button type="button"
                                onclick="document.getElementById('edit-{{ $session->id }}').style.display='table-row';"
                                style="border:none; background:none; cursor:pointer;">
                                <i class="fa-regular fa-pen-to-square"
                                   style="color:#2980b9; font-size:18px;"></i>
                            </button>
                        @else
                            <i class="fa-regular fa-pen-to-square"
                               style="color:#bdc3c7; font-size:18px;"></i>
                        @endif

                        {{-- ELIMINAR --}}
                        @if($session->attendance_enabled)
                            <form action="{{ route('courses.sessions.destroy', [$course, $session]) }}"
                                  method="POST">
                                @csrf
                                @method('DELETE')

                                <button type="submit"
                                    onclick="return confirm('¿Seguro que quieres eliminar este horario?')"
                                    class="btn-delete">
                                    <i class="fa-solid fa-delete-left"></i>
                                </button>
                            </form>
                        @else
                            <i class="fa-solid fa-delete-left"
                               style="color:#bdc3c7;"></i>
                        @endif
                    </td>
                </tr>

                {{-- FILA EDITABLE --}}
                @if($session->attendance_enabled)
                <tr id="edit-{{ $session->id }}"
                    style="display:none; background:#eef2f7;">
                    <td colspan="4">

                        <form action="{{ route('courses.sessions.update', [$course, $session]) }}"
                              method="POST"
                              style="display:flex; gap:10px; align-items:center; justify-content:center;">
                            @csrf
                            @method('PUT')

                            <input type="date"
                                   name="date"
                                   value="{{ $session->date }}"
                                   required>

                            <input type="time"
                                   name="start_time"
                                   value="{{ $session->start_time }}"
                                   required>

                            <input type="time"
                                   name="end_time"
                                   value="{{ $session->end_time }}"
                                   required>

                            <button type="submit" class="btn-create">
                                Guardar
                            </button>

                            <button type="button"
                                class="btn-cancel"
                                onclick="document.getElementById('edit-{{ $session->id }}').style.display='none';">
                                Cancelar
                            </button>

                        </form>

                    </td>
                </tr>
                @endif

                @endforeach
                </tbody>
            </table>
        @endif
    </div>

</div>



{{-- DERECHA: FORMULARIO --}}
<div class="horarios-form">

    <div class="card">
        <h3>Agregar nuevo horario</h3>

        <form action="{{ route('courses.sessions.store', $course) }}" method="POST">
            @csrf

            <div>
                <label>Fecha</label>
                <input type="date" name="date" required>
            </div>

            <div>
                <label>Hora inicio</label>
                <input type="time" name="start_time" required>
            </div>

            <div>
                <label>Hora fin</label>
                <input type="time" name="end_time" required>
            </div>

            <button type="submit" class="btn-create">
                Agregar Horario
            </button>
        </form>
    </div>

</div>


<style>
.horarios-layout{
    display:flex;
    gap:30px;
    margin-top:20px;
}

/* TARJETAS */
.card{
    background:#fff;
    border-radius:12px;
    padding:20px;
    box-shadow:0 4px 12px rgba(0,0,0,0.08);
}

/* TABLA */
table{
    width:100%;
    border-collapse:collapse;
    margin-top:15px;
}

thead{
    background:#2c4a7a;
    color:#fff;
}

th{
    padding:12px;
    font-size:14px;
    letter-spacing:1px;
}

td{
    padding:12px;
    text-align:center;
    border-bottom:1px solid #eee;
}

/* FILAS */
tbody tr:hover{
    background:#f8f9fc;
    transition:0.2s;
}

/* ACCIONES */
td:last-child{
    display:flex;
    justify-content:center;
    align-items:center;
    gap:12px;
}

/* BOTONES ICONOS */
td button{
    border:none;
    background:none;
    cursor:pointer;
}

/* ICONOS */
.fa-toggle-on{ color:#27ae60; }
.fa-toggle-off{ color:#95a5a6; }

.fa-pen-to-square{
    color:#2980b9;
    transition:0.2s;
}

.fa-pen-to-square:hover{
    transform:scale(1.2);
}

.btn-delete i{
    color:#e74c3c;
    transition:0.2s;
}

.btn-delete:hover i{
    transform:scale(1.2);
}

/* FORMULARIO */
.horarios-form{
    flex:1;
}

.horarios-form form{
    display:flex;
    flex-direction:column;
    gap:12px;
}

.horarios-form label{
    font-weight:600;
    font-size:14px;
}

.horarios-form input{
    width:100%;
    padding:10px;
    border-radius:8px;
    border:1px solid #ccc;
    outline:none;
    transition:0.2s;
}

.horarios-form input:focus{
    border-color:#2c4a7a;
    box-shadow:0 0 0 2px rgba(44,74,122,0.2);
}

/* BOTON PRINCIPAL */
.btn-create{
    background:#2c4a7a;
    color:#fff;
    border:none;
    padding:12px;
    border-radius:10px;
    font-weight:bold;
    cursor:pointer;
    transition:0.2s;
}

.btn-create:hover{
    background:#1f3560;
    transform:scale(1.03);
}

/* BOTON CANCELAR */
.btn-cancel {
    background-color: #e74c3c;
    color: white;
    border: none;
    padding: 8px 14px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
}

.btn-cancel:hover {
    background-color: #c0392b;
}

/* ALERTAS */
.alert-success {
    background-color: #d4edda;
    padding: 12px;
    border-radius: 8px;
    margin-bottom: 10px;
}

.alert-error {
    background-color: #f8d7da;
    padding: 12px;
    border-radius: 8px;
    margin-bottom: 10px;
}

/* RESPONSIVE */
@media (max-width: 900px){
    .horarios-layout{
        flex-direction:column;
    }
}

</style>

@endsection
