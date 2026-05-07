<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Lista de Participantes</title>

<style>

body {
    font-family: Arial, sans-serif;
    font-size: 12pt;
    color: #1a2942;
    margin: 0;
}

/* HEADER */
.header-table {
    width: 100%;
    margin-bottom: 12px;
}
.header-table td {
    vertical-align: middle;
}
.title {
    font-size: 16pt;
    font-weight: normal;
    text-align: center;
}
.logo-left { height: 60px; }
.logo-right { height: 60px; }

/* BLOQUES */
.label-blue {
    background: #1a2942;
    color: #fff;
    padding: 8px 14px;   /* 🔥 más alto */
    border-radius: 8px;
    font-size: 12pt;
    text-align: center;
    white-space: nowrap;
    font-weight: bold;
}

.value-white {
    background: #eef2f6;
    padding: 8px 14px;   /* 🔥 más alto */
    border-radius: 8px;
    text-align: center;
    font-weight: normal;
    font-size: 12pt;
}

.info-row td {
    vertical-align: middle;

}

/* FILAS */
.course-row {
    width: 85%; /* Tu configuración actual */
    margin-bottom: 8px;
    border-collapse: collapse; /* Recomendado para que no se separen las celdas */
}

.info-row {
    width: 100%;
    margin-bottom: 12px;
}

/* TABLA */
table.main {
    width: 100%;
    border-collapse: collapse;
    font-size: 10pt;
    border: 1px solid #1a2942;
}
thead { display: table-header-group; }

th {
    background: #1a2942;
    color: white;
    padding: 6px 4px;
    text-align: center;
    font-weight: bold;
    border: none;
    border-bottom: 2px solid #1a2942;
}

td {
    padding: 6px 4px;
    border: none;
    text-align: center;
}

tr:nth-child(even) {
    background: #f7f9fb;
}

tr {
    page-break-inside: avoid;
}

/* FOOTER */
.footer {
    position: fixed;
    bottom: 8mm;
    width: 100%;
    text-align: center;
    font-size: 7pt;
    color: #555;
}

.bordered-field {
    border: 1px solid black;        
    background-color: transparent;  
    padding: 8px 14px;              
    border-radius: 8px;
    color: #1a2942;                 
    font-weight: normal;           
    text-align: center;
}

</style>
</head>

<body>

<!-- HEADER -->
<table class="header-table">
<tr>
    <td width="30%">
        <img src="{{ public_path('storage/logos/MI.png') }}" class="logo-left">
    </td>
    <td width="40%" class="title">
        LISTA DE PARTICIPANTES
    </td>
    <td width="30%" style="text-align:right;">
        <img src="{{ public_path('storage/logos/palacio-mundo-imperial.jpg') }}" class="logo-right">
    </td>
</tr>
</table>

    <table style="width: 100%; border-collapse: collapse; margin-bottom: 8px;">
    <tr>
        <td style="width: 85%; vertical-align: top;">
            <table class="course-row" style="width: 100%;">
                <tr>
                    <td width="120">
                        <div class="label-blue">Nombre del curso:</div>
                    </td>
                    <td>
                        <div class="bordered-field">{{ $course->title }}</div>
                    </td>
                </tr>
            </table>
        </td>

        <td style="width: 15%; text-align: center; vertical-align: middle;">
            <img src="{{ public_path('storage/logos/ordenador-portatil.png') }}" style="width: 40px; height: auto;">
        </td>
    </tr>
</table>

<table class="info-row">
<tr>
    <!-- Instructor -->
    <td width="50">
        <div class="label-blue">Instructor:</div>
    </td>
    <td width="100">
        <div class="bordered-field">{{ $instructor }}</div>
    </td>

    <!-- Inicio -->
    <td width="30">
        <div class="label-blue">Fecha<br>Inicio:</div>
    </td>
    <td width="70">
        <div class="bordered-field">{{ $fecha_inicio }}</div>
    </td>

    <!-- Fin -->
    <td width="30">
        <div class="label-blue">Fecha<br>Fin:</div>
    </td>
    <td width="70">
        <div class="bordered-field">{{ $fecha_fin }}</div>
    </td>
</tr>
</table>

<!-- TABLA -->
<table class="main">
<thead>
<tr>
    <th style="width: 5%;">#</th>
    <th style="width: 32%;">Nombre completo</th>
    <th style="width: 18%;">Puesto</th>
    <th style="width: 15%;">RFC</th>
    <th style="width: 15%;">Fecha y Hora Inicio</th>
    <th style="width: 15%;">Fecha y Hora Fin</th>
    <th style="width: 10%;">Calificación</th>
</tr>
</thead>

<tbody>
@forelse($attendances as $index => $attendance)
<tr>
    <td>{{ $index + 1 }}</td>
    <td style="text-align:left;">{{ $attendance['nombre'] }}</td>
    <td>{{ $attendance['puesto'] }}</td>
    <td>{{ $attendance['rfc'] }}</td>
    <td>{{ $attendance['inicio'] ?? '—' }}</td>
    <td>{{ $attendance['fin'] ?? '—' }}</td>
    <td>{{ $attendance['final_score'] ?? '—' }}</td>
</tr>
@empty
<tr>
    <td colspan="7">No hay participantes inscritos</td>
</tr>
@endforelse
</tbody>
</table>

<!-- FOOTER -->
<div class="footer">
        Propiedad de {{ $institution_name }}<br>
        Prohibida su reproducción total o parcial sin previa autorización
    </div>

</body>
</html>