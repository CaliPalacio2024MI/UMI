<!DOCTYPE html>
<html lang="es">
<head>
    <link rel="stylesheet" href="{{ asset('css/programa.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@300;400;600&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <title>{{ $programa['nombre'] }} - UMI</title>
</head>
<body>

<!-- HERO -->
<section class="hero hero-gastronomia">


    <div class="overlay"></div>

    <div class="navbar">
        <div class="logo">
            <img src="{{ asset('images/LogoUMI-Blanco.png') }}">
        </div>
        <div class="nav-right">
            <div class="nav-links">
                <a href="{{ route('landing') }}">Inicio</a>
                <a href="{{ route('campus.acapulco') }}">Campus</a>
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
        <h3 class="script-text">Licenciatura en</h3>
        <h1>{{ $programa['nombre'] }}</h1>
        <p class="subtitle">{{ $programa['descripcion'] }}</p>
        <div class="buttons">
            <a href="{{ route('public.inscripcion.create') }}">
                <button class="btn1">Solicitar información</button>
            </a>
            <button class="btn2">Descarga un folleto</button>
        </div>
    </div>

</section>

<!-- RESUMEN DEL CURSO -->
<section class="resumen">

    <div class="resumen-container">

        <!-- LABEL IZQUIERDA -->
        <div class="resumen-label">
            <span>RESUMEN DEL CURSO</span>
        </div>

        <!-- CONTENIDO DERECHA -->
        <div class="resumen-content">

            <h2>Todo lo que necesitas<br>en una sola carrera</h2>

            <p>
                Esta licenciatura te abre el camino hacia una carrera profesional en 
                {{ $programa['nombre'] }}, ofreciendo un conocimiento completo de la 
                industria complementado con prácticas en organizaciones líderes del sector.
            </p>

            <p>
                A lo largo de tus estudios aplicarás lo aprendido en proyectos reales, 
                desarrollando habilidades prácticas desde el primer día en el campus UMI.
            </p>

            <!-- GRID DE FEATURES -->
            <div class="resumen-grid">

                <div class="resumen-item">
                    <div class="resumen-icon">📍</div>
                    <div>
                        <strong>Campus Acapulco</strong>
                        <p>Aprende en la Riviera Diamante</p>
                    </div>
                </div>

                <div class="resumen-item">
                    <div class="resumen-icon">💼</div>
                    <div>
                        <strong>Experiencia profesional</strong>
                        <p>Prácticas en empresas reales</p>
                    </div>
                </div>

                <div class="resumen-item">
                    <div class="resumen-icon">📅</div>
                    <div>
                        <strong>Próximo inicio</strong>
                        <p>Agosto 2026</p>
                    </div>
                </div>

                <div class="resumen-item">
                    <div class="resumen-icon">⏱</div>
                    <div>
                        <strong>Duración</strong>
                        <p>{{ $programa['duracion'] }}</p>
                    </div>
                </div>

                <div class="resumen-item">
                    <div class="resumen-icon">🎓</div>
                    <div>
                        <strong>Modalidad</strong>
                        <p>{{ $programa['modalidad'] }}</p>
                    </div>
                </div>

                <div class="resumen-item">
                    <div class="resumen-icon">🌍</div>
                    <div>
                        <strong>Enfoque internacional</strong>
                        <p>Visión global de la industria</p>
                    </div>
                </div>

                <div class="resumen-item">
                    <div class="resumen-icon">🏨</div>
                    <div>
                        <strong>Vinculación hotelera</strong>
                        <p>Convenios con hoteles de lujo</p>
                    </div>
                </div>

                <div class="resumen-item">
                    <div class="resumen-icon">🚀</div>
                    <div>
                        <strong>Emprendimiento</strong>
                        <p>Desarrolla tu propio proyecto</p>
                    </div>
                </div>

            </div>

        </div>

    </div>

</section>

<!-- GALERÍA GASTRONÓMICA -->
<section class="galeria-programa">

    <div class="galeria-header">
        <div class="galeria-texto">
            <span class="galeria-label">EXPERIENCIA CULINARIA</span>
            <h2>El arte de la gastronomía<br>en cada platillo</h2>
            <p>
                Nuestros estudiantes aprenden técnicas de alto nivel trabajando con 
                ingredientes de temporada, maridajes excepcionales y presentaciones 
                que elevan cada experiencia gastronómica a una obra de arte.
            </p>
        </div>
    </div>

    <div class="galeria-collage">

        
        <div class="galeria-item galeria-grande">
            <img src="{{ asset('images/gastro1.jpg') }}" alt="Platillo UMI">
        </div>

        
        <div class="galeria-columna">

            <div class="galeria-item galeria-mediana">
                <img src="{{ asset('images/gastro2.jpg') }}" alt="Gastronomía UMI">
            </div>

            <div class="galeria-fila">
                <div class="galeria-item galeria-pequeña">
                    <img src="{{ asset('images/gastro3.jpg') }}" alt="Bebidas UMI">
                </div>
                <div class="galeria-item galeria-pequeña">
                    <img src="{{ asset('images/gastro4.jpg') }}" alt="Cocina UMI">
                </div>
            </div>

        </div>

    </div>

</section>

<!-- LO QUE APRENDERÁS -->
<section class="distingue">
    <div class="distingue-header">
        <h2>Lo que aprenderás</h2>
        <p>Competencias y habilidades que desarrollarás a lo largo de la licenciatura.</p>
    </div>

    <div class="distingue-grid">
        @foreach($programa['competencias'] as $competencia)
        <div class="distingue-col">
            <h3>{{ $competencia['titulo'] }}</h3>
            <p>{{ $competencia['descripcion'] }}</p>
        </div>
        @endforeach
    </div>
</section>

<!-- CTA -->
<section class="cta-section">
    <div class="cta-box">
        <h2>¿Listo para estudiar {{ $programa['nombre'] }}?</h2>
        <a href="{{ route('public.inscripcion.create') }}">
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
            <p>Instagram</p>
            <p>Facebook</p>
            <p>TikTok</p>
        </div>
    </div>
    <div class="footer-bottom">
        <p>© 2026 UMI. Todos los derechos reservados.</p>
    </div>
</footer>

<script>
function abrirMenu(){ }
</script>

</body>
</html>