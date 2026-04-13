@extends('layouts.app')

@section('title', 'Aula ' . ($facility->nombre_aula ?? '—') . ' - ' . session('active_institution_name'))

@section('content')
<div class="container">
    <div class="content-header">
        <div class="content-title">
            <h3>Aula {{ $facility->nombre_aula ?? '—' }}</h3>
        </div>
        <div class="header-option" style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
            <a href="{{ route('control.facilities.index') }}" class="mi-boton" style="text-decoration: none; display: inline-block;">← Listado</a>
            @if(Auth::user()->hasAnyRole(['master']))
                <a href="{{ route('control.facilities.edit', $facility) }}" class="mi-boton" style="text-decoration: none; display: inline-block;">Editar</a>
            @endif
        </div>
    </div>

    <div style="margin-top: 1.25rem; max-width: 480px;">
        @include('layouts.ControlAdmin.Infraestrucuta.components.show_detail')
    </div>
</div>
@endsection
