<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Universidad Mundo Imperial</title>
    @vite(['resources/css/app.css', 'resources/css/CRM/public.css', 'resources/js/app.js'])
    <style>
        .landing-hero {
            text-align: center;
            padding: 60px 20px;
            max-width: 800px;
            margin: 0 auto;
        }
        .hero-title {
            font-size: 3rem;
            font-weight: 700;
            color: #0d1e38;
            margin-bottom: 20px;
        }
        .hero-subtitle {
            font-size: 1.2rem;
            color: #555;
            margin-bottom: 40px;
            line-height: 1.6;
        }
        .btn-cta {
            background-color: #B08955;
            color: white;
            text-decoration: none;
            padding: 15px 40px;
            border-radius: 50px;
            font-size: 1.2rem;
            font-weight: 700;
            display: inline-block;
            box-shadow: 0 4px 15px rgba(176, 137, 85, 0.4);
            transition: transform 0.2s, background-color 0.3s;
        }
        .btn-cta:hover {
            background-color: #9a7647;
            transform: translateY(-2px);
        }
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin-top: 60px;
            text-align: center;
        }
        .feature-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }
        .feature-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #0d1e38;
            margin-bottom: 15px;
        }
        .feature-text {
            color: #666;
            font-size: 0.95rem;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <header class="public-header" style="justify-content: space-between;">
        <div class="header-logo">
            <img src="{{ asset('images/uhta-logo.png') }}" alt="UMI Universidad Mundo Imperial" onerror="this.style.display='none'; this.parentElement.innerText='UMI LOGO'">
        </div>
        <div>
            <a href="{{ route('login') }}" style="color: white; text-decoration: none; font-weight: 500;">Iniciar Sesión</a>
        </div>
    </header>

    <!-- Main Content -->
    <main class="public-content" style="flex-direction: column; align-items: center;">
        
        <div class="landing-hero">
            <h1 class="hero-title">Bienvenidos a Universidad Mundo Imperial</h1>
            <p class="hero-subtitle">
                Formamos líderes con visión global, excelencia académica y un compromiso inquebrantable con la innovación y el servicio.
            </p>
            
            <a href="/registro-publico" class="btn-cta">
                Inscribirse Ahora
            </a>

            <div class="features-grid">
                <div class="feature-card">
                    <h3 class="feature-title">Excelencia Académica</h3>
                    <p class="feature-text">Programas educativos de vanguardia diseñados para el éxito profesional.</p>
                </div>
                <div class="feature-card">
                    <h3 class="feature-title">Instalaciones Modernas</h3>
                    <p class="feature-text">Campus equipado con tecnología de punta para un aprendizaje integral.</p>
                </div>
                <div class="feature-card">
                    <h3 class="feature-title">Comunidad Global</h3>
                    <p class="feature-text">Sé parte de una red diversa y conecta con oportunidades internacionales.</p>
                </div>
            </div>
        </div>

    </main>

    <!-- Footer -->
    <footer class="public-footer">
        <div class="footer-logo">
             <img src="{{ asset('images/LOGO3.png') }}" alt="Mundo Imperial" onerror="this.style.display='none'; this.parentElement.innerText='MUNDO IMPERIAL'">
        </div>
    </footer>

</body>
</html>
