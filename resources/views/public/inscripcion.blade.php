<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Formulario Público</title>
</head>
<body>

<h1>Formulario público funcionando 🎉</h1>

<form method="POST" action="/inscripcion">
    @csrf

    <label>Nombre</label><br>
    <input type="text" name="nombre"><br><br>

    <button type="submit">Enviar</button>
</form>

</body>
</html>
