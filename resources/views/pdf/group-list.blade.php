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

        .logo-text {
            font-size: 15px;
            font-weight: bold;
            color: #071f38;
        }

        .logo-small {
            font-size: 6px;
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
            padding: 9px 6px;
            font-size: 10px;
            text-align: center;
            font-weight: bold;
            border: 1px solid #061d33;
        }

        .participants td {
            padding: 6px 6px;
            font-size: 10px;
            text-align: center;
            vertical-align: middle;
            border: 1px solid #c7d0da;
        }

        .participants tbody tr:nth-child(even) {
            background: #f8fafc;
        }

        .col-num {
            width: 35px;
        }

        .col-name {
            width: 240px;
        }

        .col-puesto {
            width: 170px;
        }

        .col-rfc {
            width: 120px;
        }

        .col-hour {
            width: 120px;
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

            $institutionId =
                $session->course->institution_id ?? null;

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

        <td class="value" colspan="7">
            {{ $session->course->title }}
        </td>

    </tr>

    <tr>

        <td class="label-small">
            Fecha<br>Inicio:
        </td>

        <td class="value">
            {{ \Carbon\Carbon::parse($session->date)->format('d/m/Y') }}
        </td>

        <td class="label-small">
            Fecha<br>Fin:
        </td>

        <td class="value">
            {{ \Carbon\Carbon::parse($session->date)->format('d/m/Y') }}
        </td>

        <td class="label-small">
            Hora<br>Inicio:
        </td>

        <td class="value">
            {{ \Carbon\Carbon::parse($session->start_time)->format('h:i a') }}
        </td>

        <td class="icon-cell">

            <img
                src="file://{{ public_path('images/icons/user-solid-full.svg') }}"
                width="42"
                height="42"
            >

        </td>

        <td class="label-small">
            Hora<br>Fin:
        </td>

        <td class="value">
            {{ \Carbon\Carbon::parse($session->end_time)->format('h:i a') }}
        </td>

    </tr>

    <tr>

        <td class="label">
            Instructor:
        </td>

        <td class="value" colspan="3">

            {{ optional($session->course->instructor)->nombre ?? 'N/A' }}
            {{ optional($session->course->instructor)->apellido_paterno ?? '' }}

        </td>

        <td class="label">
            Duración:
        </td>

        <td class="value" colspan="4">

            {{
                \Carbon\Carbon::parse($session->start_time)
                    ->diffInHours(
                        \Carbon\Carbon::parse($session->end_time)
                    )
            }}

            hrs.

        </td>

    </tr>

</table>

<div class="list-box">

    <table class="participants">

        <thead>

            <tr>

                <th class="col-num">#</th>

                <th class="col-name">
                    Nombre completo
                </th>

                <th class="col-puesto">
                    Puesto
                </th>

                <th class="col-rfc">
                    RFC
                </th>

                <th class="col-hour">
                    Hora Entrada
                </th>

            </tr>

        </thead>

        <tbody>

            @foreach($hosts as $index => $host)

                <tr>

                    <td>
                        {{ $index + 1 }}
                    </td>

                    <td>

                        {{ $host->nombre }}
                        {{ $host->apellido_paterno }}
                        {{ $host->apellido_materno }}

                    </td>

                    <td>
                        {{ optional($host->workstation)->name ?? 'N/A' }}
                    </td>

                    <td>
                        {{ $host->RFC ?? $host->rfc ?? 'N/A' }}
                    </td>

                    <td>

                        {{
                            \Carbon\Carbon::parse(
                                $session->start_time
                            )->format('h:i a')
                        }}

                    </td>

                </tr>

            @endforeach

        </tbody>

    </table>

</div>

<div class="footer">

    <p>
        Propiedad de Mundo Imperial
    </p>

</div>

</body>
</html>
