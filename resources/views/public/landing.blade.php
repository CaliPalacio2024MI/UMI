<!DOCTYPE html>
<html lang="es">
<head>
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@300;400;600&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <meta charset="UTF-8">
    <title>Universidad</title>
</head>

<body>

    <section class="hero">

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
                    <a href="#" onclick="abrirMenu('programas')">Programas</a>
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

        <div class="alumni-collage">
            <div class="collage-grande">
                <img src="{{ asset('images/foto13.jpg') }}">
            </div>
            <div class="collage-pequeñas">
                <img src="{{ asset('images/foto12.jpg') }}">
                <img src="{{ asset('images/foto14.jpg') }}">
            </div>
            <div class="collage-ancha">
                <img src="{{ asset('images/foto11.jpg') }}">
            </div>
        </div>

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

    <!-- DESTINOS -->
    <section class="destinations-section">

        <div class="section-intro">
            <p class="dest-label">Nuestras instalaciones</p>
            <h2 class="dest-title">Espacios diseñados para<br>impulsar tu aprendizaje</h2>
        </div>

        <div class="dest-item">

            <div class="dest-img-wrap carrusel-wrap">
                <div class="carrusel-track" id="carruselTrack">
                    <img src="{{ asset('images/foto1.jpg') }}">
                    <img src="{{ asset('images/foto6.jpg') }}">
                    <img src="{{ asset('images/foto10.jpg') }}">
                    <img src="{{ asset('images/foto9.jpg') }}">
                </div>

                <button class="carrusel-btn prev" onclick="moverCarrusel(-1)">‹</button>
                <button class="carrusel-btn next" onclick="moverCarrusel(1)">›</button>

                <div class="carrusel-dots" id="carruselDots"></div>
            </div>

            <div class="dest-info">
                <span class="dest-num">01</span>
                <span class="dest-tag">México · Acapulco</span>
                <h3>Campus Mundo Imperial</h3>
                <p>
                    Un campus universitario moderno ubicado en la Riviera Diamante de Acapulco, 
                    con instalaciones de primer nivel, tecnología innovadora y un entorno que 
                    impulsa el aprendizaje, la creatividad y el desarrollo profesional.
                </p>

                <div class="dest-features">
                    <div class="dest-feature"><span class="dot"></span> Instalaciones modernas</div>
                    <div class="dest-feature"><span class="dot"></span> Tecnología de vanguardia</div>
                    <div class="dest-feature"><span class="dot"></span> Vinculación profesional</div>
                </div>

                <a href="{{ route('campus.acapulco') }}">Explorar nuestro campus →</a>
            </div>

        </div>

    </section>

    <!-- LO QUE NOS DISTINGUE -->
    <section class="distingue">

        <div class="distingue-header">
            <h2>Lo que nos distingue</h2>
            <p>Formamos líderes con visión global en hospitalidad y negocios de lujo, combinando rigor académico con experiencia práctica real.</p>
        </div>

        <div class="distingue-grid">

            <div class="distingue-col">
                <h3>Enfoque global</h3>
                <p>Programas con visión internacional que preparan a nuestros egresados para competir en los mercados más exigentes del mundo.</p>
            </div>

            <div class="distingue-col">
                <h3>Excelencia académica</h3>
                <p>Profesores altamente capacitados con experiencia en la industria real, comprometidos con el desarrollo integral del alumno.</p>
            </div>

            <div class="distingue-col">
                <h3>Alta empleabilidad</h3>
                <p>Vinculación directa con empresas líderes del sector hotelero y de negocios, con prácticas profesionales desde el primer año.</p>
            </div>

            <div class="distingue-col">
                <h3>Experiencia real</h3>
                <p>Aprendizaje práctico en campo con acceso directo a hoteles, restaurantes y empresas de lujo para desarrollar habilidades desde el primer día.</p>
            </div>

        </div>

    </section>

    <!-- AÑOS DE EXCELENCIA -->
    <section class="excelencia">

        <div class="excelencia-container">

            <div class="excelencia-img-wrap">

                <div class="excelencia-badge">
                    <img src="{{ asset('images/LogoUMI-Blanco.png') }}" alt="UMI">
                    <span>Universidad Mundo Imperial</span>
                </div>

                <img src="{{ asset('images/foto1.jpg') }}" alt="Estudiantes UMI" class="excelencia-img">

            </div>

            <div class="excelencia-texto">
                <h2>Años de excelencia</h2>

                <p>
                    Formando líderes en hospitalidad y negocios de lujo, estamos 
                    firmemente establecidos como una de las instituciones educativas 
                    más destacadas de México en gestión hotelera y turismo.
                </p>

                <p>
                    Ofrecemos educación empresarial aplicada, combinando rigor 
                    académico, habilidades prácticas y vinculación profesional, 
                    todo ello con un enfoque en desarrollar las competencias que 
                    demanda la economía global moderna.
                </p>

                <a href="#" class="excelencia-link">Conoce a nosotros</a>
            </div>

        </div>

        <!-- REDES -->
        <div class="footer-social">
            <h4>Síguenos</h4>
            <div class="social-icons">
                <a href="https://www.linkedin.com" target="_blank">
                    <i class="fab fa-linkedin"></i>
                </a>
                <a href="https://www.instagram.com" target="_blank">
                    <i class="fab fa-instagram"></i>
                </a>
                <a href="https://www.tiktok.com" target="_blank">
                    <i class="fab fa-tiktok"></i>
                </a>
            </div>
        </div>

    </section>

    <!-- CAREERS -->
    <section class="careers" id="careersSection">

        <div class="volver-container">
            <button onclick="volverProgramas()" class="btn-volver">
                ← Volver a programas
            </button>
        </div>

        <h2 id="tituloCarreras">Programas</h2>

        <div class="careers-grid" id="careersGrid"></div>

    </section>

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

            <div class="footer-left">
                <h2>UMI</h2>
                <p>Universidad Mundo Imperial</p>
            </div>

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

            <div class="footer-social">
                <h4>Síguenos</h4>
                <div class="social-icons">
                    <a href="https://www.linkedin.com" target="_blank">
                        <i class="fab fa-linkedin"></i>
                    </a>
                    <a href="https://www.instagram.com" target="_blank">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="https://www.tiktok.com" target="_blank">
                        <i class="fab fa-tiktok"></i>
                    </a>
                </div>
            </div>

        </div>

        <div class="footer-bottom">
            <p>© 2026 UMI. Todos los derechos reservados.</p>
        </div>

    </footer>

    <!-- MENÚ FULL -->
    <div class="menu-full" id="menuFull">

        <div class="menu-header">
            <div class="menu-logo">
                <img src="{{ asset('images/LogoUMI-Blanco.png') }}">
            </div>
            <div class="close-btn" onclick="cerrarMenu()">✕</div>
        </div>

        <div class="menu-content">

            <div class="menu-left">
                <h2 onclick="cambiarSeccion('programas', this)">Programas</h2>
                <h2 class="active" onclick="cambiarSeccion('campus', this)">Campus</h2>
                <h2 onclick="cambiarSeccion('admisiones', this)">Admisiones</h2>
                <h2 onclick="cambiarSeccion('acerca', this)">Acerca de</h2>
                <h2>Alumnado</h2>
            </div>

            <div class="menu-right" id="menuContenido">
                <a href="{{ route('campus.acapulco') }}" class="campus-card">
                    <div class="img-container">
                        <img src="{{ asset('images/foto1.jpg') }}">
                    </div>
                    <h3>Acapulco</h3>
                    <p>Guerrero, México</p>
                </a>
            </div>

        </div>

        <div class="menu-social">
            <a href="https://www.linkedin.com" target="_blank">
                <i class="fab fa-linkedin"></i>
            </a>
            <a href="https://www.instagram.com" target="_blank">
                <i class="fab fa-instagram"></i>
            </a>
            <a href="https://www.tiktok.com" target="_blank">
                <i class="fab fa-tiktok"></i>
            </a>
        </div>

    </div>

    <!-- JS -->
    <script>
    function abrirMenu(seccion = null){
        const menu = document.getElementById("menuFull");
        menu.classList.add("active");

        const items = document.querySelectorAll(".menu-left h2");
        items.forEach(el => el.classList.remove("active"));

        if(seccion === "campus"){
            items[1].classList.add("active");
            cambiarSeccion('campus', items[1]);
        }

        if(seccion === "programas"){
            items[0].classList.add("active");
            cambiarSeccion('programas', items[0]);
        }
    }

    function cerrarMenu(){
        document.getElementById("menuFull").classList.remove("active");
    }

    function mostrarProgramas(tipo){
        const contenedor = document.getElementById("careersGrid");
        const titulo = document.getElementById("tituloCarreras");

        const data = {
            licenciatura: {
                titulo: "Licenciaturas",
                items: [
                    { nombre: "Administración Hotelera",  img: "images/Princess-85.jpg", slug: "administracion-hotelera"  },
                    { nombre: "Negocios Internacionales", img: "images/foto6.jpg",  slug: "negocios-internacionales"  },
                    { nombre: "Gastronomía",          img: "images/Princess-17.jpg",  slug: "gastronomía" }
                ]
            },
            posgrado: {
                titulo: "Posgrados",
                items: [
                    { nombre: "Maestría en Hospitality", img: "images/foto9.jpg" },
                    { nombre: "Maestría en Finanzas",    img: "images/Pierre-23.jpg" },
                    { nombre: "Maestría en Marketing",   img: "images/foto7.jpg" }
                ]
            },
            ejecutivo: {
                titulo: "Educación continua",
                items: [
                    { nombre: "Diplomado en Negocios", img: "images/foto1.jpg" },
                    { nombre: "Gestión de lujo",       img: "images/bebida.jpg" },
                    { nombre: "Leadership Program",    img: "images/bebida2.jpg" }
                ]
            },
            online: {
                titulo: "Cursos en línea",
                items: [
                    { nombre: "Curso de Hospitality",      img: "images/building.png" },
                    { nombre: "Curso de lujo",             img: "images/foto4.jpg" },
                    { nombre: "Online Business Program",   img: "images/foto5.jpg" }
                ]
            }
        };

        const info = data[tipo];
        titulo.innerText = info.titulo;

       contenedor.innerHTML = info.items.map(item => `
            <a href="/programas/${item.slug}" class="career-card" style="text-decoration:none; color:inherit;">
                <img src="${item.img}" alt="${item.nombre}">
                <h3>${item.nombre}</h3>
            </a>
        `).join("");

        document.getElementById("careersSection").scrollIntoView({ behavior: "smooth" });
    }

    function volverProgramas(){
        document.querySelector(".programs-grid").scrollIntoView({ behavior: "smooth" });
    }

    function irAPrograma(tipo){
        cerrarMenu();
        mostrarProgramas(tipo);
        setTimeout(() => {
            document.getElementById("careersSection").scrollIntoView({ behavior: "smooth" });
        }, 300);
    }
    </script>

    <script>
    function revealOnScroll(){
        const section = document.querySelector(".alumni");
        const content = document.querySelector(".alumni-content");
        const rect = section.getBoundingClientRect();
        const windowHeight = window.innerHeight;

        if(rect.top < windowHeight - 150){
            content.classList.add("active");
        } else {
            content.classList.remove("active");
        }
    }

    window.addEventListener("scroll", revealOnScroll);
    window.addEventListener("load", revealOnScroll);
    </script>

    <script>
    let current = 0;
    let target = 0;

    window.addEventListener("scroll", () => { target = window.scrollY; });

    function animateParallax(){
        current += (target - current) * 0.08;

        const section = document.querySelector(".alumni");
        const rect = section.getBoundingClientRect();

        const img1 = document.querySelector(".img1");
        const img2 = document.querySelector(".img2");
        const img3 = document.querySelector(".img3");

        if(rect.top < window.innerHeight && rect.bottom > 0){
            const progress = Math.min(Math.max((window.innerHeight - rect.top) / window.innerHeight, 0), 1);
            if(img1) img1.style.transform = `translateY(${progress * 40}px)`;
            if(img2) img2.style.transform = `translateY(${progress * 60}px)`;
            if(img3) img3.style.transform = `translateY(${progress * 30}px)`;
        }

        requestAnimationFrame(animateParallax);
    }

    animateParallax();
    </script>

    <script>
    function cambiarSeccion(seccion, el){
        const contenedor = document.getElementById("menuContenido");

        document.querySelectorAll(".menu-left h2").forEach(item => {
            item.classList.remove("active");
        });

        el.classList.add("active");

        if(seccion === "campus"){
            contenedor.innerHTML = `
                <a href="{{ route('campus.acapulco') }}" class="campus-card">
                    <div class="img-container">
                        <img src="{{ asset('images/foto1.jpg') }}">
                    </div>
                    <h3>Acapulco</h3>
                    <p>Guerrero, México</p>
                </a>
            `;
        }

        if(seccion === "programas"){
            contenedor.innerHTML = `
                <div class="menu-programas">

                    <div class="col">
                        <span class="menu-subtitle">LICENCIATURAS</span>
                        <p onclick="irAPrograma('licenciatura')">Administración Hotelera</p>
                        <p onclick="irAPrograma('licenciatura')">Negocios Internacionales</p>
                        <p onclick="irAPrograma('licenciatura')">Gastronomía</p>
                    </div>

                    <div class="col">
                        <span class="menu-subtitle">POSGRADOS</span>
                        <p onclick="irAPrograma('posgrado')">Maestría en Hospitality</p>
                        <p onclick="irAPrograma('posgrado')">Maestría en Finanzas</p>
                        <p onclick="irAPrograma('posgrado')">Maestría en Marketing</p>
                    </div>

                    <div class="col">
                        <span class="menu-subtitle">EDUCACIÓN EJECUTIVA</span>
                        <p onclick="irAPrograma('ejecutivo')">Diplomado en Negocios</p>
                        <p onclick="irAPrograma('ejecutivo')">Gestión de lujo</p>
                        <p onclick="irAPrograma('ejecutivo')">Leadership Program</p>
                    </div>

                    <div class="col">
                        <span class="menu-subtitle">CURSOS</span>
                        <p onclick="irAPrograma('online')">Curso de Hospitality</p>
                        <p onclick="irAPrograma('online')">Curso de lujo</p>
                        <p onclick="irAPrograma('online')">Online programs</p>
                    </div>

                </div>
            `;
        }
    }
    </script>

    <script>
    let indice = 0;
    const track = document.getElementById("carruselTrack");
    const totalImagenes = track.children.length;
    const dotsContainer = document.getElementById("carruselDots");

    for(let i = 0; i < totalImagenes; i++){
        const dot = document.createElement("span");
        if(i === 0) dot.classList.add("active");
        dot.onclick = () => irA(i);
        dotsContainer.appendChild(dot);
    }

    function actualizarDots(){
        document.querySelectorAll(".carrusel-dots span").forEach((d, i) => {
            d.classList.toggle("active", i === indice);
        });
    }

    function irA(n){
        indice = n;
        track.style.transform = `translateX(-${indice * 100}%)`;
        actualizarDots();
    }

    function moverCarrusel(dir){
        indice = (indice + dir + totalImagenes) % totalImagenes;
        irA(indice);
    }

    setInterval(() => moverCarrusel(1), 4000);
    </script>

</body>
</html>