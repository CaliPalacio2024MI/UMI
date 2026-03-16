@extends('layouts.app')
@section('title', 'CRM - Comisiones')
@push('css')
    @vite('resources/css/CRM/prospectos.css')
@endpush
@section('content')
<div class="crm-prospectos">

    <div class="header-top">
        <h1>COMISIONES</h1>
    </div>

    <div class="toolbar">
        <div class="filtros-izquierda">
            <div class="input-group-custom">
                <img src="{{ asset('images/icons/calendario.svg') }}" class="icon-calendar">
                <input type="date" class="input-custom" id="fecha-inicio">
            </div>
            <div class="input-group-custom">
                <img src="{{ asset('images/icons/calendario.svg') }}" class="icon-calendar">
                <input type="date" class="input-custom" id="fecha-fin">
            </div>
            <div class="input-group-custom search-wrapper">
                <img src="{{ asset('images/icons/search.svg') }}" alt="Search" width="16">
                <input type="text" class="input-custom buscador" placeholder="Buscar">
            </div>
        </div>
        <button class="btn-exportar">
            <img src="{{ asset('images/icons/export.svg') }}" alt="Exportar" width="16">
            Exportar
        </button>
    </div>

    <div class="table-container">
        <div class="table-card">
            <div class="table-row-header">
                <div class="col-curp">CURP</div>
                <div class="col-nombre">Nombre</div>
                <div class="col-paterno">Apellido<br>Paterno</div>
                <div class="col-materno">Carrera</div>
                <div class="col-ctp">CTP</div>
                <div class="col-tipo">Estatus</div>
                <div class="col-fecha">Fecha</div>
                <div class="col-acciones">Acciones</div>
            </div>
            <div class="table-body">
                <p>No hay comisiones registradas.</p>
            </div>
        </div>
    </div>

</div>
@endsection