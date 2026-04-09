@extends('layouts.app')

@section('title', 'Editar Grupo')

@section('content')

<div class="container" style="max-width:600px; margin-top:30px;">

    <h2>Editar Grupo</h2>

    <form action="{{ route('groups.update', $group) }}" method="POST">
        @csrf
        @method('PUT')

        <!-- NOMBRE -->
        <div class="mb-3">
            <label>Nombre</label>
            <input type="text" name="name" class="form-control"
                value="{{ $group->name }}" required>
        </div>

        <!-- TIPO -->
        <div class="mb-3">
            <label>Tipo</label>
            <select name="type" class="form-control">
                <option value="abierto" {{ $group->type == 'abierto' ? 'selected' : '' }}>Abierto</option>
                <option value="cerrado" {{ $group->type == 'cerrado' ? 'selected' : '' }}>Cerrado</option>
            </select>
        </div>

        <!-- MIN / MAX -->
        <div class="row">
            <div class="col">
                <label>Mínimo</label>
                <input type="number" name="min_participants" class="form-control"
                    value="{{ $group->min_participants }}">
            </div>
            <div class="col">
                <label>Máximo</label>
                <input type="number" name="max_participants" class="form-control"
                    value="{{ $group->max_participants }}">
            </div>
        </div>

        <button class="btn btn-primary mt-3 w-100">
            Actualizar Grupo
        </button>

    </form>

</div>

@endsection
