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
            <img src="{{ asset('images/uhta-logo.png') }}" alt="UMI Universidad Mundo Imperial" onerror="this.style.display='none'; this.parentElement.innerText='UMI LOGO'">
        </div>
    </header>

    <!-- Main Content -->
    <main class="public-content">
        <div class="enrollment-card">
            
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="/registro-publico" enctype="multipart/form-data">
                @csrf
                <div id="form-fields">
                    @include('public._form_fields')
                </div>
            </form>
        </div>
    </main>

    <!-- Footer -->
    <footer class="public-footer">
        <div class="footer-logo">
            <img src="{{ asset('images/LOGO3.png') }}" alt="Mundo Imperial" onerror="this.style.display='none'; this.parentElement.innerText='MUNDO IMPERIAL'">
        </div>
    </footer>

    <script>
        function bindClasificacion() {
            const selectClasificacion = document.getElementById('select-clasificacion');
            const selectCarrera       = document.getElementById('select-carrera');
            const grupoCarrera        = document.getElementById('grupo-carrera');
            if (!selectClasificacion || !selectCarrera) return;

            const opcionesCarrera = Array.from(selectCarrera.querySelectorAll('option'));

            selectClasificacion.addEventListener('change', function () {
                const clasificacionId = this.value;
                selectCarrera.value = '';
                if (!clasificacionId) { grupoCarrera.style.display = 'none'; return; }
                opcionesCarrera.forEach(option => {
                    if (!option.value) { option.style.display = ''; }
                    else if (option.dataset.clasificacion === clasificacionId) { option.style.display = ''; }
                    else { option.style.display = 'none'; }
                });
                grupoCarrera.style.display = '';
            });
        }

        bindClasificacion();

        // ── Polling: actualiza el formulario cuando cambia la configuración ──
        let _formHash = null;

        async function pollFormConfig() {
            try {
                const res  = await fetch('/registro-publico/config', { cache: 'no-store' });
                const data = await res.json();
                if (_formHash === null) {
                    _formHash = data.hash;
                } else if (data.hash !== _formHash) {
                    _formHash = data.hash;
                    const fragRes = await fetch('/registro-publico/fragment', { cache: 'no-store' });
                    const html    = await fragRes.text();
                    document.getElementById('form-fields').innerHTML = html;
                    bindClasificacion();
                }
            } catch (_) {}
            setTimeout(pollFormConfig, 2000);
        }

        pollFormConfig();
    </script>

</body>
</html>