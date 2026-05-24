<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">

    <style>
        @page {
            margin: 22px 28px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #061d33;
        }

        .header-table {
            width: 100%;
            margin-bottom: 34px;
        }

        .logo-left {
            width: 120px;
            text-align: left;
            vertical-align: top;
        }

        .logo-right {
            width: 140px;
            text-align: right;
            vertical-align: top;
        }

        .icon-cell {
            width: 70px;
            text-align: center;
            vertical-align: middle;
        }

        .title {
            text-align: center;
            font-size: 18px;
            font-weight: 700;
            letter-spacing: .5px;
            color: #071f38;
        }

        .info-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 8px;
            margin-bottom: 20px;
        }

        .label {
            background: #061d33;
            color: #ffffff;
            font-weight: bold;
            padding: 8px 9px;
            border-radius: 5px;
            text-align: center;
            width: 118px;
            font-size: 10px;
        }

        .label-small {
            background: #061d33;
            color: #ffffff;
            font-weight: bold;
            padding: 7px 7px;
            border-radius: 5px;
            text-align: center;
            width: 65px;
            font-size: 10px;
        }

        .value {
            border: 1px solid #aeb7c2;
            padding: 7px 10px;
            border-radius: 5px;
            text-align: center;
            background: #ffffff;
            font-size: 10px;
        }

        .list-box {
            border: 1px solid #aeb7c2;
            border-radius: 5px;
            min-height: 455px;
            overflow: hidden;
        }

        table.participants {
            width: 100%;
            border-collapse: collapse;
        }

        .participants thead tr {
            background: #061d33;
        }

        .participants th {
            color: #ffffff;
            padding: 7px 4px;
            font-size: 8px;
            text-align: center;
            font-weight: bold;
            border: 1px solid #061d33;
        }
        .participants td {
            padding: 5px 4px;
            font-size: 8px;
            text-align: center;
            vertical-align: middle;
            border: 1px solid #c7d0da;
        }

        .col-num {
            width: 25px;
        }

        .col-name {
            width: 180px;
        }

        .col-puesto {
            width: 115px;
        }

        .col-rfc {
           width: 90px;
        }

        .col-date {
            width: 80px;
        }

        .col-score {
            width: 65px;
        }

        .footer {
            position: fixed;
            bottom: 12px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 7px;
            color: #061d33;
        }

        .footer p {
            margin: 2px 0;
        }
    </style>
</head>

<body>

<table class="header-table">
    <tr>
        <td class="logo-left">
            <img
                src="file://{{ public_path('images/logos/logomundoimperial.png') }}"
                style="width: 95px; height: auto;"
            >
        </td>

        <td class="title">
            LISTA DE PARTICIPANTES
        </td>

        @php
            $institutionId = $course->institution_id ?? null;

            $propertyLogo = match($institutionId) {
                2 => 'images/logos/Princess Mundo Imperial.png',
                1 => 'images/logos/Palacio Mundo Imperial.png',
                3 => 'images/logos/Pierre Mundo Imperial.png',
                default => 'images/logos/Universidad Mundo Imperial.png',
            };
        @endphp

        <td class="logo-right">
            <img
                src="file://{{ public_path($propertyLogo) }}"
                style="width: 120px; height: auto;"
            >
        </td>
    </tr>
</table>

<table class="info-table">

    <tr>
        <td class="label">
            Nombre del curso:
        </td>

        <td class="value" colspan="6">
            {{ $course->title ?? 'N/A' }}
        </td>

        <td class="icon-cell" colspan="2">
            <img
                src="file://{{ public_path('images/icons/desktop-solid-full.svg') }}"
                width="36"
                height="36"
            >
        </td>
    </tr>

    <tr>
        <td class="label">
            Instructor:
        </td>

        <td class="value" colspan="3">
            {{ $instructor ?? 'N/A' }}
        </td>

        <td class="label-small">
            Fecha<br>Inicio:
        </td>

        <td class="value">
            {{ $fecha_inicio ?? 'N/A' }}
        </td>

        <td class="label-small">
            Fecha<br>Fin:
        </td>

        <td class="value">
            {{ $fecha_fin ?? 'N/A' }}
        </td>
    </tr>

</table>

<div class="list-box">

    <table class="participants">

        <thead>
            <tr>
                <th class="col-num">#</th>
                <th class="col-name">Nombre completo</th>
                <th class="col-puesto">Puesto</th>
                <th class="col-rfc">RFC</th>
                <th class="col-date">Fecha Inicio</th>
                <th class="col-date">Fecha Fin</th>
                <th class="col-score">Calificación</th>
            </tr>
        </thead>

        <tbody>
            @forelse($attendances as $index => $attendance)
                <tr>
                    <td>
                        {{ $index + 1 }}
                    </td>

                    <td>
                        {{ $attendance['nombre'] ?? 'N/A' }}
                    </td>

                    <td>
                        {{ $attendance['puesto'] ?? 'N/A' }}
                    </td>

                    <td>
                        {{ $attendance['rfc'] ?? 'N/A' }}
                    </td>

                    <td>
                        @if(!empty($attendance['started_at']))
                            {{ \Carbon\Carbon::parse($attendance['started_at'])->format('d/m/Y') }}
                        @else
                            —
                        @endif
                    </td>

                    <td>
                        @if(!empty($attendance['completed_at']))
                            {{ \Carbon\Carbon::parse($attendance['completed_at'])->format('d/m/Y') }}
                        @else
                            —
                        @endif
                    </td>

                    <td>
                        @if(array_key_exists('final_score', $attendance) && $attendance['final_score'] !== null)
                            {{ $attendance['final_score'] }}%
                        @else
                            —
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">
                        No hay participantes inscritos
                    </td>
                </tr>
            @endforelse
        </tbody>

    </table>

</div>

<div class="footer">
    <p>
        Propiedad de {{ $institution_name ?? 'Mundo Imperial' }}
    </p>
</div>

</body>
</html>
