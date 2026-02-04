@extends('layouts.app')

@section('title', 'CRM - Leads')
@vite(['resources/css/CRM/leads.css'])
@section('content')

<div class="container-fluid crm-leads">

    <!-- ENCABEZADO -->
    <div class="row mb-3 align-items-center">
        <div class="col-md-6">
            <h1 class="fw-bold">LEADS</h1>
        </div>
        <div class="col-md-6 text-end">
            <input type="text" class="form-control buscador" placeholder="Buscar">
        </div>
    </div>

    <!-- CONTENIDO PRINCIPAL -->
    <div class="row">

        <!-- LISTA DE LEADS -->
        <div class="col-md-8">
            <div class="card card-leads">
                <div class="card-header encabezado-tabla">
                <div class="row encabezado-fila text-center">
                        <div class="col">Nombre</div>
                        <div class="col">Apellido Paterno</div>
                        <div class="col">Apellido Materno</div>
                        <div class="col">Teléfono 1</div>
                        <div class="col">Teléfono 2</div>
                        <div class="col">Acciones</div>
                    </div>
                </div>

                <div class="card-body cuerpo-tabla">
    @forelse($leads ?? [] as $lead)
        <div class="row fila-lead align-items-center">
            <div class="col">{{ $lead->nombre }}</div>
            <div class="col">{{ $lead->apellido_paterno }}</div>
            <div class="col">{{ $lead->apellido_materno }}</div>
            <div class="col">{{ $lead->telefono_1 }}</div>
            <div class="col">{{ $lead->telefono_2 }}</div>
            <div class="col acciones">
                <button class="btn btn-icon">📄</button>
                <button class="btn btn-icon">👁</button>
                <button class="btn btn-icon btn-danger">✖</button>
            </div>
        </div>
    @empty
        <p class="text-center text-muted">No hay leads registrados</p>
    @endforelse
</div>


            </div>
        </div>

        <!-- PANEL DERECHO -->
        <div class="col-md-4">
            <div class="card card-detalle">
                <div class="card-header encabezado-detalle">
                    <div class="row fw-semibold text-center">
                        <div class="col">RFC</div>
                        <div class="col">CLASIFICACIÓN</div>
                    </div>
                </div>
                <div class="card-body">
                    {{-- Aquí irá el detalle del lead seleccionado --}}
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
