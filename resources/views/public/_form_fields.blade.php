@php
    $fc    = $formConfig ?? collect();
    $show  = fn($campo) => !isset($fc[$campo]) || $fc[$campo]['activo'];
    $req   = fn($campo) => !isset($fc[$campo]) || $fc[$campo]['obligatorio'] || $fc[$campo]['siempre_activo'];
    $label = fn($campo, $fallback = '') => isset($fc[$campo]) ? $fc[$campo]['etiqueta'] : $fallback;

    // Mapa de campos personalizados: [seccion][despues_de] => [campos]
    $cMap = [];
    foreach(($camposAdicionales ?? collect()) as $ca) {
        if ($ca->seccion) {
            $cMap[$ca->seccion][$ca->despues_de ?? ''][] = $ca;
        }
    }
@endphp

@php
if (!function_exists('renderCampoAdicional')):
function renderCampoAdicional($campo) {
    $name = 'campo_' . $campo->nombre_campo;
    $req  = $campo->obligatorio ? 'required' : '';
    $ph   = $campo->placeholder ?? '';
    if ($campo->tipo === 'select') {
        $opts = array_filter(array_map('trim', explode("\n", $campo->opciones ?? '')));
        $html = '<select name="'.$name.'" class="form-control" '.$req.'>'
              . '<option value="">'.($ph ?: 'Seleccione una opción').'</option>';
        foreach($opts as $o) $html .= '<option value="'.htmlspecialchars($o).'">'.htmlspecialchars($o).'</option>';
        $html .= '</select>';
    } elseif ($campo->tipo === 'textarea') {
        $html = '<textarea name="'.$name.'" class="form-control" rows="3" placeholder="'.htmlspecialchars($ph).'" '.$req.'></textarea>';
    } elseif ($campo->tipo === 'checkbox') {
        $html = '<label style="display:flex;align-items:center;gap:8px;cursor:pointer;">'
              . '<input type="checkbox" name="'.$name.'" value="1" '.$req.'>'
              . '<span>'.htmlspecialchars($ph ?: $campo->etiqueta).'</span></label>';
    } else {
        $html = '<input type="'.$campo->tipo.'" name="'.$name.'" class="form-control" placeholder="'.htmlspecialchars($ph).'" '.$req.'>';
    }
    return '<div class="form-group">'
         . '<label class="form-label">'.htmlspecialchars($campo->etiqueta).':'
         . ($campo->obligatorio ? ' <span style="color:#c0392b">*</span>' : '')
         . '</label><div class="form-input-container">'.$html.'</div></div>';
}
endif;
@endphp

<div class="form-section">
    <h2 class="section-title">Datos del postulante:</h2>

    @if($show('alumno_curp'))
    <div class="form-group">
        <label class="form-label">{{ $label('alumno_curp','CURP') }}:@if($req('alumno_curp')) <span style="color:#c0392b">*</span>@endif</label>
        <div class="form-input-container">
            <input type="text" name="alumno_curp" class="form-control" oninput="this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '')" style="text-transform:uppercase;" maxlength="18" {{ $req('alumno_curp') ? 'required' : '' }}>
        </div>
    </div>
    @endif
    @foreach($cMap['postulante']['alumno_curp'] ?? [] as $ca) {!! renderCampoAdicional($ca) !!} @endforeach

    @if($show('alumno_nombre'))
    <div class="form-group">
        <label class="form-label">{{ $label('alumno_nombre','Nombre(s)') }}: <span style="color:#c0392b">*</span></label>
        <div class="form-input-container">
            <input type="text" name="alumno_nombre" class="form-control" oninput="this.value = this.value.replace(/[^a-zA-ZñÑáéíóúÁÉÍÓÚ\s]/g, '')" required>
        </div>
    </div>
    @endif
    @foreach($cMap['postulante']['alumno_nombre'] ?? [] as $ca) {!! renderCampoAdicional($ca) !!} @endforeach

    @if($show('alumno_paterno'))
    <div class="form-group">
        <label class="form-label">{{ $label('alumno_paterno','Apellido paterno') }}:@if($req('alumno_paterno')) <span style="color:#c0392b">*</span>@endif</label>
        <div class="form-input-container">
            <input type="text" name="alumno_paterno" class="form-control" oninput="this.value = this.value.replace(/[^a-zA-ZñÑáéíóúÁÉÍÓÚ\s]/g, '')" {{ $req('alumno_paterno') ? 'required' : '' }}>
        </div>
    </div>
    @endif
    @foreach($cMap['postulante']['alumno_paterno'] ?? [] as $ca) {!! renderCampoAdicional($ca) !!} @endforeach

    @if($show('alumno_materno'))
    <div class="form-group">
        <label class="form-label">{{ $label('alumno_materno','Apellido materno') }}:@if($req('alumno_materno')) <span style="color:#c0392b">*</span>@endif</label>
        <div class="form-input-container">
            <input type="text" name="alumno_materno" class="form-control" oninput="this.value = this.value.replace(/[^a-zA-ZñÑáéíóúÁÉÍÓÚ\s]/g, '')" {{ $req('alumno_materno') ? 'required' : '' }}>
        </div>
    </div>
    @endif
    @foreach($cMap['postulante']['alumno_materno'] ?? [] as $ca) {!! renderCampoAdicional($ca) !!} @endforeach

    @if($show('nivel_educativo'))
    <div class="form-group">
        <label class="form-label">{{ $label('nivel_educativo','Nivel educativo') }}:</label>
        <div class="form-input-container">
            <select name="nivel_educativo" id="select-clasificacion" class="form-control">
                <option value="">Seleccione un nivel</option>
                @foreach($clasificaciones as $clasificacion)
                    <option value="{{ $clasificacion->id }}">{{ $clasificacion->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
    @endif
    @foreach($cMap['postulante']['nivel_educativo'] ?? [] as $ca) {!! renderCampoAdicional($ca) !!} @endforeach

    <div class="form-group" id="grupo-carrera" @if(!$show('nivel_educativo')) style="display:block;" @else style="display:none;" @endif>
        <label class="form-label">{{ $label('carrera_id','Plan de Estudio / Carrera') }}: <span style="color:#c0392b">*</span></label>
        <div class="form-input-container">
            <select name="carrera_id" id="select-carrera" class="form-control" required>
                <option value="">Seleccione una carrera</option>
                @foreach($carreras as $carrera)
                    <option value="{{ $carrera->id }}" data-clasificacion="{{ $carrera->career_classification_id }}">
                        {{ $carrera->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
    @foreach($cMap['postulante']['carrera_id'] ?? [] as $ca) {!! renderCampoAdicional($ca) !!} @endforeach
</div>

@php $hayTutor = $show('tutor_curp')||$show('tutor_nombre')||$show('tutor_paterno')||$show('tutor_materno')||$show('telefono1')||$show('telefono2')||$show('tutor_email'); @endphp
@if($hayTutor)
<div class="form-section">
    <h2 class="section-title">Datos del padre o tutor interesado:</h2>

    @if($show('tutor_curp'))
    <div class="form-group">
        <label class="form-label">{{ $label('tutor_curp','CURP') }}:@if($req('tutor_curp')) <span style="color:#c0392b">*</span>@endif</label>
        <div class="form-input-container">
            <input type="text" name="tutor_curp" class="form-control" oninput="this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '')" style="text-transform:uppercase;" maxlength="18" {{ $req('tutor_curp') ? 'required' : '' }}>
        </div>
    </div>
    @endif
    @foreach($cMap['tutor']['tutor_curp'] ?? [] as $ca) {!! renderCampoAdicional($ca) !!} @endforeach

    @if($show('tutor_nombre'))
    <div class="form-group">
        <label class="form-label">{{ $label('tutor_nombre','Nombre(s)') }}:@if($req('tutor_nombre')) <span style="color:#c0392b">*</span>@endif</label>
        <div class="form-input-container">
            <input type="text" name="tutor_nombre" class="form-control" oninput="this.value = this.value.replace(/[^a-zA-ZñÑáéíóúÁÉÍÓÚ\s]/g, '')" {{ $req('tutor_nombre') ? 'required' : '' }}>
        </div>
    </div>
    @endif
    @foreach($cMap['tutor']['tutor_nombre'] ?? [] as $ca) {!! renderCampoAdicional($ca) !!} @endforeach

    @if($show('tutor_paterno'))
    <div class="form-group">
        <label class="form-label">{{ $label('tutor_paterno','Apellido paterno') }}:@if($req('tutor_paterno')) <span style="color:#c0392b">*</span>@endif</label>
        <div class="form-input-container">
            <input type="text" name="tutor_paterno" class="form-control" oninput="this.value = this.value.replace(/[^a-zA-ZñÑáéíóúÁÉÍÓÚ\s]/g, '')" {{ $req('tutor_paterno') ? 'required' : '' }}>
        </div>
    </div>
    @endif
    @foreach($cMap['tutor']['tutor_paterno'] ?? [] as $ca) {!! renderCampoAdicional($ca) !!} @endforeach

    @if($show('tutor_materno'))
    <div class="form-group">
        <label class="form-label">{{ $label('tutor_materno','Apellido materno') }}:@if($req('tutor_materno')) <span style="color:#c0392b">*</span>@endif</label>
        <div class="form-input-container">
            <input type="text" name="tutor_materno" class="form-control" oninput="this.value = this.value.replace(/[^a-zA-ZñÑáéíóúÁÉÍÓÚ\s]/g, '')" {{ $req('tutor_materno') ? 'required' : '' }}>
        </div>
    </div>
    @endif
    @foreach($cMap['tutor']['tutor_materno'] ?? [] as $ca) {!! renderCampoAdicional($ca) !!} @endforeach

    @if($show('telefono1') || $show('telefono2'))
    <div class="form-group">
        <div class="form-input-container split-inputs">
            @if($show('telefono1'))
            <div class="phone-field">
                <label class="split-label">{{ $label('telefono1','Teléfono 1') }}:@if($req('telefono1')) *@endif</label>
                <input type="text" name="telefono1" class="form-control" oninput="this.value = this.value.replace(/[^0-9]/g, '')" inputmode="numeric" maxlength="10" {{ $req('telefono1') ? 'required' : '' }}>
            </div>
            @endif
            @if($show('telefono2'))
            <div class="phone-field">
                <label class="split-label">{{ $label('telefono2','Teléfono 2') }}:</label>
                <input type="text" name="telefono2" class="form-control" oninput="this.value = this.value.replace(/[^0-9]/g, '')" inputmode="numeric" maxlength="10">
            </div>
            @endif
        </div>
    </div>
    @endif
    @foreach($cMap['tutor']['telefono1'] ?? [] as $ca) {!! renderCampoAdicional($ca) !!} @endforeach

    @if($show('tutor_email'))
    <div class="form-group">
        <label class="form-label">{{ $label('tutor_email','Correo electrónico') }}:@if($req('tutor_email')) <span style="color:#c0392b">*</span>@endif</label>
        <div class="form-input-container">
            <input type="email" name="tutor_email" class="form-control" {{ $req('tutor_email') ? 'required' : '' }}>
        </div>
    </div>
    @endif
    @foreach($cMap['tutor']['tutor_email'] ?? [] as $ca) {!! renderCampoAdicional($ca) !!} @endforeach
</div>
@endif

<div class="form-actions">
    <button type="submit" class="btn-submit">Registrar</button>
</div>
