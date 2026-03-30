@extends('layouts.app')

@section('content')

<div style="padding:30px; max-width:900px; margin:auto;">

    <h2 style="margin-bottom:20px; font-weight:bold;">Crear Tema Plantilla</h2>

    <form method="POST" action="{{ route('subtopics_template.store') }}" enctype="multipart/form-data"
        style="background-color:#ffffff; padding:25px; border:1px solid #e1e1e1; border-radius:8px; box-shadow:0 2px 4px rgba(0,0,0,0.05);">

        <h3 style="margin=-bottom:25px; font-weight:bold; font-size:1.25rem;">Añadir Nuevo Tema</h3>

        @csrf

        <div style="margin-bottom:20px;">
            <label style="display:block; margin-bottom:px; font-weight:bold; color:#555;">Título del Tema</label>
            <input type="text" name="title"
                style="width:100%; padding:10px; border:1px solid #ccc; border-radius:4px; box-sizing:border-box; font-size:1rem">
        </div>
        
        <div style="margin-bottom:20px;">
            <label style="display:block; margin-bottom:8px; font-weight:bold; color:#555;">Descripción Detallada del Tema</label>
            <textarea name="description"
                style="width:100%; padding:10px; border:1px solid #ccc; border-radius:4px; box-sizing:border-box; font-size:1rem; min-height:120px;"></textarea>
        </div>
        
        <div style="margin-bottom:20px;">
            <label style="display:block; margin-bottom:8px; font-weight:bold; color:#555;">Adjuntar Archivo (PDF o Videos)</label>
            <input type="file" name="file"
                style="width:100%; padding:8px 10px; border:1px solid:#ccc; border-radius:4px; box-sizing:border-box; font-size:1rem;">
        </div>

        <div style="text-aligh:left;">
        <button type="submit" style="background-color:#004085; color:white; border:none; padding:10px 20px; border-radius:4px; font-size:1rem; font-weight:bold; cursos:pointer; transition: background-color 0.2s;">Guardar Tema</button>
        </div>
    
    </form>

</div>

@endsection