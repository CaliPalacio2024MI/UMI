<!DOCTYPE html>
<html lang="es">
<head>

    <!-- CSS -->
    <link rel="stylesheet" href="{{ asset('css/explora.css') }}">

    <!-- FUENTES -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@300;400;600&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">

    <meta charset="UTF-8">
    <title>Universidad</title>
</head>

<body>
 
<section class="hero">

    <!-- VIDEO -->
    <video autoplay muted loop class="video-bg">
        <source src="{{ asset('videos/aca_campus.mp4') }}" type="video/mp4">
    </video>

    <div class="overlay"></div>

    <div class="navbar">
        <div class="logo">
            <img src="{{ asset('images/LogoUMI-Blanco.png') }}">
        </div>

        <div class="nav-right">
            <div class="nav-links">
                <a href="#">Programas</a>
                <a href="#" onclick="abrirMenu('campus')">Campus</a>
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
    </div>

</section>

<section class="intro-campus">

    <div class="intro-container">

        <!-- LADO IZQUIERDO -->
        <div class="intro-label">
            INTRODUCCIÓN
        </div>

        <!-- LADO DERECHO -->
        <div class="intro-content">
            <h2>Presentamos nuestro campus UMI</h2>

            <p>
                Nuestro campus en Acapulco se encuentra en una de las zonas más exclusivas 
                de la Riviera Diamante, ofreciendo un entorno ideal para el aprendizaje 
                y el desarrollo profesional.
            </p>

            <p>
                Diseñado para la formación en hospitalidad, negocios y turismo, el campus 
                combina instalaciones modernas con conexión directa a la industria real.
            </p>

            <!-- FEATURES -->
            <div class="intro-features">

                <div class="feature-box">
                    <span class="dot"></span>
                    <p><strong>+20 programas académicos</strong> disponibles</p>
                </div>

                <div class="feature-box">
                    <span class="dot"></span>
                    <p><strong>Convenios con hoteles y empresas</strong></p>
                </div>

            </div>

            <!-- BOTONES -->
            <div class="intro-buttons">
                <a href="#" class="btn-dark">Visita nuestro campus</a>
                <a href="#" class="btn-light">Ver galería</a>
            </div>

        </div>

    </div>

</section>

<!-- JORNADAS DE PUERTAS ABIERTAS -->
<section class="jornadas">

    <div class="jornadas-container">

        <!-- IMAGEN IZQUIERDA -->
        <div class="jornadas-img">
            <img src="{{ asset('images/foto4.jpg') }}" alt="Puertas abiertas UMI">
        </div>

        <!-- CONTENIDO DERECHO -->
        <div class="jornadas-content">

            <h2>Próximas jornadas<br>de puertas abiertas</h2>

            <p class="jornadas-desc">
                Descubre nuestro campus en persona. Consulta los próximos días de 
                puertas abiertas y experimenta cómo es la vida estudiantil en UMI.
            </p>

            <!-- EVENTO 1 -->
            <div class="evento-item">
                <span class="evento-label">JORNADAS DE PUERTAS ABIERTAS</span>
                <p class="evento-fecha">Sábado, 17 de mayo de 2026</p>
                <p class="evento-detalle">09:00 - 14:00 · Campus UMI, Acapulco</p>
                <a href="{{ route('public.inscripcion.create') }}" class="evento-link">Regístrate ahora</a>
            </div>

            <!-- EVENTO 2 -->
            <div class="evento-item">
                <span class="evento-label">JORNADAS DE PUERTAS ABIERTAS</span>
                <p class="evento-fecha">Sábado, 21 de junio de 2026</p>
                <p class="evento-detalle">09:00 - 14:00 · Campus UMI, Acapulco</p>
                <a href="{{ route('public.inscripcion.create') }}" class="evento-link">Regístrate ahora</a>
            </div>

            <a href="#" class="jornadas-ver-todas">Consulta todas nuestras jornadas →</a>

        </div>

    </div>

</section>
<!-- INSTALACIONES -->
<section class="instalaciones">

    <div class="instalaciones-container">

        <!-- IZQUIERDA -->
        <div class="instalaciones-texto">
            <h2>Instalaciones increíbles y una gran variedad de actividades</h2>
            <p>
                Disfruta de instalaciones de primer nivel diseñadas para elevar 
                tu aprendizaje. Desde aulas equipadas con tecnología de punta 
                hasta espacios de convivencia, cada aspecto del campus UMI está 
                diseñado para inspirar, involucrar y enriquecer tu camino en la 
                hospitalidad y los negocios.
            </p>
        </div>

        <!-- DERECHA -->
        <div class="instalaciones-cta">
            <a href="#" class="btn-instalaciones">Ver todas las instalaciones y actividades</a>
        </div>

    </div>

</section>
<!-- VIDA EN ACAPULCO -->
<section class="vida-campus">

    <div class="vida-inner">

        <img src="{{ asset('images/foto5.jpg') }}" alt="Vida en Acapulco" class="vida-bg-img">
        <div class="vida-overlay"></div>

        <div class="vida-texto">
            <h2>Estudios en Acapulco</h2>
            <p>
                Acapulco ofrece un escenario único para estudiantes con vocación internacional,
                con sus playas espectaculares, su rica cultura y un estilo de vida vibrante.
                Desde explorar la Riviera Diamante hasta conectar con la industria hotelera,
                siempre hay algo que experimentar.
            </p>
        </div>

        <div class="vida-cta">
            <a href="#" class="btn-vida">Aprende sobre la vida en Acapulco</a>
        </div>

    </div>

</section>
<!-- FOOTER -->
<footer class="footer">

    <div class="footer-container">

        <!-- IZQUIERDA -->
        <div class="footer-left">
            <h2>UMI</h2>
            <p>Universidad Mundo Imperial</p>
        </div>

        <!-- LINKS -->
        <div class="footer-links">

            <div>
                <h4>Información</h4>
                <a href="#">Acerca de</a>
                <a href="#">Programas</a>
                <a href="#">Campus</a>
            </div>

            <div>
                <h4>Admisiones</h4>
                <a href="#">Solicitar info</a>
                <a href="#">Becas</a>
                <a href="#">Requisitos</a>
            </div>

            <div>
                <h4>Contacto</h4>
                <a href="#">Email</a>
                <a href="#">Teléfono</a>
                <a href="#">Ubicación</a>
            </div>

        </div>

        <!-- REDES -->
        <div class="footer-social">
            <h4>Síguenos</h4>
            <p>Instagram</p>
            <p>Facebook</p>
            <p>TikTok</p>
        </div>

    </div>

    <!-- LINEA FINAL -->
    <div class="footer-bottom">
        <p>© 2026 UMI. Todos los derechos reservados.</p>
    </div>

</footer>

<!-- MENU -->
<div class="menu-full" id="menuFull">

    <!-- HEADER -->
    <div class="menu-header">
        <div class="menu-logo">
            <img src="{{ asset('images/LogoUMI-Blanco.png') }}">
        </div>

        <div class="close-btn" onclick="cerrarMenu()">✕</div>
    </div>

    <!-- CONTENIDO -->
    <div class="menu-content">

        <!-- IZQUIERDA -->
        <div class="menu-left">
    <h2 onclick="cambiarSeccion('programas')">Programas</h2>
    <h2 class="active" onclick="cambiarSeccion('campus')">Campus</h2>
    <h2 onclick="cambiarSeccion('admisiones')">Admisiones</h2>
    <h2 onclick="cambiarSeccion('acerca')">Acerca de</h2>
    <h2>Alumnado</h2>
</div>

        <!-- DERECHA  -->
        <div class="menu-right">

            <a href="{{ route('campus.acapulco') }}" class="campus-card">
                <div class="img-container">
                    <img src="{{ asset('images/foto1.jpg') }}">
                </div>
                <h3>Acapulco</h3>
                <p>Guerrero, México</p>
            </a>

        </div>

    </div>

</div>

<!-- JS -->
<script>
function abrirMenu(seccion = null){
    const menu = document.getElementById("menuFull");
    menu.classList.add("active");

    // Reset estilos
    document.querySelectorAll(".menu-left h2").forEach(el => {
        el.classList.remove("active");
    });

    // Activar sección
    if(seccion === "campus"){
        document.querySelector(".menu-left h2:nth-child(2)").classList.add("active");
    }
}

function cerrarMenu(){
    document.getElementById("menuFull").classList.remove("active");
}
function mostrarProgramas(tipo){
    const contenedor = document.getElementById("careersGrid");
    const titulo = document.getElementById("tituloCarreras");

    const nombres = {
        licenciatura: "Licenciaturas",
        posgrado: "Posgrados",
        ejecutivo: "Educación continua",
        online: "Cursos en línea"
    };

    titulo.innerText = nombres[tipo] || tipo;

    contenedor.innerHTML = `
        <div class="career-card"><h3>${nombres[tipo]} 1</h3></div>
        <div class="career-card"><h3>${nombres[tipo]} 2</h3></div>
        <div class="career-card"><h3>${nombres[tipo]} 3</h3></div>
    `;

    
    document.getElementById("careersSection").scrollIntoView({
        behavior: "smooth"
    });
}
</script>
<script>
function revealOnScroll(){
    const reveals = document.querySelectorAll(".reveal");

    reveals.forEach((el) => {
        const windowHeight = window.innerHeight;
        const elementTop = el.getBoundingClientRect().top;

        if(elementTop < windowHeight - 120){
            el.classList.add("active");
        }
    });
}

// activar al cargar
window.addEventListener("load", revealOnScroll);

// activar al hacer scroll
window.addEventListener("scroll", revealOnScroll);
</script>
<script>
let current = 0;
let target = 0;

window.addEventListener("scroll", () => {
    target = window.scrollY;
});

function animate(){
    current += (target - current) * 0.08;

    const img1 = document.querySelector(".img1");
    const img2 = document.querySelector(".img2");
    const img3 = document.querySelector(".img3");

    if(img1){
        img1.style.transform = `translateY(${current * 0.2}px)`;
    }

    if(img2){
        img2.style.transform = `translateY(${current * 0.35}px)`;
    }

    if(img3){
        img3.style.transform = `translateY(${current * 0.15}px)`;
    }

    requestAnimationFrame(animate);
}

animate();
</script>
</body>
</html>