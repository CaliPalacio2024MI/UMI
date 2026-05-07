<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Registro de Aspirante')</title>
    @vite(['resources/css/ControlEsc/base.css', 'resources/js/app.js'])
</head>
<body style="margin:0; padding:0;">
    @yield('content')
</body>
</html>
