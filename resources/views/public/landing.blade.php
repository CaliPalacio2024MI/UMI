<!DOCTYPE html>
<html lang="es">
<head>

    <!-- CSS -->
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">

    <!-- FUENTES -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@300;400;600&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">

    <meta charset="UTF-8">
    <title>Universidad</title>
</head>

<body>
 
<section class="hero">

    <!-- VIDEO -->
    <video autoplay muted loop class="video-bg">
        <source src="{{ asset('videos/inicio_alumnos.mp4') }}" type="video/mp4">
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

    <div class="content">
        <h3 class="script-text">Excelentes en</h3>
        <h1>Hospitalidad y lujo<br>educación de negocios</h1>

        <p class="subtitle">
            UMI offers career-focused programs designed to develop global leaders 
            in hospitality and luxury business.
        </p>

        <div class="buttons">
            <button class="btn1">Descarga el folleto</button>
            <button class="btn2">Acerca de UMI</button>
        </div>
    </div>

</section>

<!-- PROGRAMAS -->
<section class="destinations-section">

    <div class="programs-title">
        <h2>Explora nuestros programas</h2>
    </div>

    <div class="programs-grid">

        <div class="card" onclick="mostrarProgramas('licenciatura')">
            <img src="{{ asset('images/IMAGEN.jpg') }}">
            <h3>Licenciaturas</h3>
        </div>

        <div class="card" onclick="mostrarProgramas('posgrado')">
            <img src="{{ asset('images/posgrado.jpg') }}">
            <h3>Posgrados</h3>
        </div>

        <div class="card" onclick="mostrarProgramas('ejecutivo')">
            <img src="{{ asset('images/edu_continua.jpg') }}">
            <h3>Educación continua</h3>
        </div>

        <div class="card" onclick="mostrarProgramas('online')">
            <img src="{{ asset('images/en_linea.jpg') }}">
            <h3>Cursos en línea</h3>
        </div>

    </div>

</section>
<section class="alumni">

<img src="{{ asset('images/foto1.jpg') }}" class="alumni-img img1 reveal">
<img src="{{ asset('images/foto2.jpg') }}" class="alumni-img img2 reveal">
<img src="{{ asset('images/foto3.jpg') }}" class="alumni-img img3 reveal">

<div class="alumni-content reveal">

    <span class="alumni-tag">ALUMNI NETWORK</span>

    <h2>
        After graduation, and for the rest of your life,
        you will be connected to an influential global network
        of professionals and entrepreneurs.
    </h2>

    <button>Learn about the Alumni Network</button>

</div>

</section>


<!-- FEATURES -->
<section class="features">
    <div class="feature"><h3>🌍 Enfoque global</h3><p>Programas con visión internacional</p></div>
    <div class="feature"><h3>🎓 Excelencia académica</h3><p>Profesores altamente capacitados</p></div>
    <div class="feature"><h3>💼 Alta empleabilidad</h3><p>Conexión con empresas reales</p></div>
    <div class="feature"><h3>🏨 Experiencia real</h3><p>Aprendizaje práctico en campo</p></div>
</section>

<!-- DESTINOS -->
<section class="destinations-section">

    <div class="section-intro">
        <p class="dest-label">Nuestros destinos</p>
        <h2 class="dest-title">Acapulco, donde el<br>Pacífico te enamora</h2>
    </div>

    <!-- DESTINO 1 -->
    <div class="dest-item">
        <div class="dest-img-wrap">
            <img src="{{ asset('images/foto1.jpg') }}">
        </div>
        <div class="dest-info">
            <span class="dest-num">01</span>
            <span class="dest-tag">México · Acapulco</span>
            <h3>Palacio</h3>
            <p>
                Una franja de costa espectacular al sur de Nápoles, con pueblos de colores 
                que se aferran a los acantilados y vistas al mar Tirreno que quitan el aliento.
            </p>

            <div class="dest-features">
                <div class="dest-feature"><span class="dot"></span> 7 días / 6 noches</div>
                <div class="dest-feature"><span class="dot"></span> Todo incluido</div>
                <div class="dest-feature"><span class="dot"></span> Vuelo directo</div>
            </div>

            <a href="#" class="dest-cta">Explorar destino →</a>
        </div>
    </div>

    <!-- DESTINO 2 -->
    <div class="dest-item dest-reverse">
        <div class="dest-img-wrap">
            <img src="{{ asset('images/foto2.jpg') }}">
        </div>
        <div class="dest-info">
            <span class="dest-num">02</span>
            <span class="dest-tag">México · Acapulco</span>
            <h3>Pierre</h3>
            <p>
                Isla de los dioses, donde los templos entre arrozales, las playas volcánicas 
                y la cultura hindú crean una atmósfera mística sin igual.
            </p>

            <div class="dest-features">
                <div class="dest-feature"><span class="dot"></span> 10 días / 9 noches</div>
                <div class="dest-feature"><span class="dot"></span> Hotel boutique</div>
                <div class="dest-feature"><span class="dot"></span> Guía local</div>
            </div>

            <a href="#" class="dest-cta">Explorar destino →</a>
        </div>
    </div>

    <!-- DESTINO 3 -->
    <div class="dest-item">
        <div class="dest-img-wrap">
            <img src="{{ asset('images/foto3.jpg') }}">
        </div>
        <div class="dest-info">
            <span class="dest-num">03</span>
            <span class="dest-tag">México · Acapulco</span>
            <h3>Princess</h3>
            <p>
                Donde el desierto dorado se funde con la arquitectura del futuro. 
                Una ciudad de récords mundiales y hospitalidad sin fronteras.
            </p>

            <div class="dest-features">
                <div class="dest-feature"><span class="dot"></span> 5 días / 4 noches</div>
                <div class="dest-feature"><span class="dot"></span> Hotel 5 estrellas</div>
                <div class="dest-feature"><span class="dot"></span> Traslados incluidos</div>
            </div>

            <a href="#" class="dest-cta">Explorar destino →</a>
        </div>
    </div>

</section>

<!-- CAREERS -->
<section class="careers" id="careersSection">
    <h2 id="tituloCarreras">Programas</h2>
    <div class="careers-grid" id="careersGrid"></div>
</section>

<!-- CTA -->
<section class="cta-section">
    <div class="cta-box">
        <h2>¿Listo para comenzar tu futuro?</h2>
        <a href="/registro-publico">
            <button>Solicitar información</button>
        </a>
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