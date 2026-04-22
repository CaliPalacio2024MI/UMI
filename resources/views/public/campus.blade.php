<!DOCTYPE html>
<html lang="es">
<head>
    <link rel="stylesheet" href="{{ asset('css/campus.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@300;400;600&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <title>Campus - UMI</title>
</head>

<body>

    <section class="campus-hero">

        <!-- IMAGEN O VIDEO DE FONDO -->
        <div class="campus-overlay"></div>
            <video autoplay muted loop class="video-bg">
                <source src="{{ asset('videos/inicio_alumnos.mp4') }}" type="video/mp4">
            </video>
        

        <!-- NAVBAR (igual que landing) -->
        <div class="navbar">
            <div class="logo">
                <img src="{{ asset('images/LogoUMI-Blanco.png') }}">
            </div>

            <div class="nav-right">
                <div class="nav-links">
                    <a href="#">Programas</a>
                    <a href="{{ route('public.campus') }}">Campus</a>
                    <a href="#">Admisiones</a>
                </div>

                <div class="menu" onclick="abrirMenu()">
                    <span>Menu</span>
                    <div class="hamburger">
                        <div></div><div></div><div></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CONTENIDO HERO -->
        <div class="campus-content">
            <h1 class="campus-title">Acapulco</h1>
            <p class="campus-desc">Estudia frente al Pacífico en uno de los destinos más emblemáticos de México.</p>
            <p class="campus-desc">
                UMI ofrece formación en hospitalidad y negocios de lujo en un entorno costero único,
                con instalaciones modernas, acceso directo a la industria hotelera y una comunidad
                estudiantil vibrante en la bahía de Acapulco.
            </p>

            <div class="buttons">
                <button class="btn1">Descarga el folleto</button>
                <button class="btn2">Reserva una visita privada</button>
            </div>

        </div>

    </section>

</body>
</html>