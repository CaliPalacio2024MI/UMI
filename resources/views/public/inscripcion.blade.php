<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscripción</title>
    @vite(['resources/css/app.css', 'resources/css/CRM/public.css', 'resources/js/app.js'])
</head>
<body>

    <!-- Header -->
    <header class="public-header">
        <div class="header-logo">
            <!-- Placeholder for Logo -->
            <img src="{{ asset('images/uhta-logo.png') }}" alt="UMI Universidad Mundo Imperial" onerror="this.style.display='none'; this.parentElement.innerText='UMI LOGO'">
        </div>
    </header>

    <!-- Main Content -->
    <main class="public-content">
        <div class="enrollment-card">
            
            <form method="POST" action="/inscripcion">
                @csrf

                <!-- Section: Datos del responsable o tutor -->
                <div class="form-section">
                    <h2 class="section-title">Datos del responsable o tutor</h2>

                    <div class="form-group">
                        <label class="form-label">Nombre(s):</label>
                        <div class="form-input-container">
                            <input type="text" name="tutor_nombre" class="form-control">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Apellido paterno:</label>
                        <div class="form-input-container">
                            <input type="text" name="tutor_paterno" class="form-control">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Apellido materno:</label>
                        <div class="form-input-container">
                            <input type="text" name="tutor_materno" class="form-control">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Telefono 1:</label>
                        <div class="form-input-container split-inputs">
                            <input type="text" name="telefono1" class="form-control">
                            <label class="split-label">Telefono 2:</label>
                            <input type="text" name="telefono2" class="form-control">
                        </div>
                    </div>
                </div>

                <!-- Section: Datos del Alumno -->
                <div class="form-section" style="margin-top: 50px;">
                    <h2 class="section-title">Datos del Alumno:</h2>

                    <div class="form-group">
                        <label class="form-label">Nombre(s):</label>
                        <div class="form-input-container">
                            <input type="text" name="alumno_nombre" class="form-control">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Apellido paterno:</label>
                        <div class="form-input-container">
                            <input type="text" name="alumno_paterno" class="form-control">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Apellido materno:</label>
                        <div class="form-input-container">
                            <input type="text" name="alumno_materno" class="form-control">
                        </div>
                    </div>

                     <div class="form-group">
                        <label class="form-label">RFC:</label>
                        <div class="form-input-container">
                            <input type="text" name="rfc" class="form-control">
                        </div>
                    </div>

                     <div class="form-group">
                        <label class="form-label">CURP:</label>
                        <div class="form-input-container">
                            <input type="text" name="curp" class="form-control">
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="form-actions">
                    <button type="submit" class="btn-submit">
                        Registrar
                    </button>
                </div>

            </form>
        </div>
    </main>

    <!-- Footer -->
    <footer class="public-footer">
        <div class="footer-logo">
             <!-- Placeholder for Footer Logo -->
             <img src="{{ asset('images/LOGO3.png') }}" alt="Mundo Imperial" onerror="this.style.display='none'; this.parentElement.innerText='MUNDO IMPERIAL'">
        </div>
    </footer>

</body>
</html>
	

