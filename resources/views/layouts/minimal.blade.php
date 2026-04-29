<!DOCTYPE html>
<html lang="es" class="schedule-edit-page">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Editar horario')</title>
    @yield('vite')
</head>
<body class="schedule-edit-page">
    @yield('content')
</body>
</html>
