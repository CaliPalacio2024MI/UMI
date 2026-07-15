<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; color: #1E1E1E; padding: 20px; }
        h1 { color: #223F70; font-size: 20px; margin-bottom: 4px; }
        .meta { color: #666; font-size: 12px; margin-bottom: 20px; }
        ol { padding-left: 20px; }
        li { margin-bottom: 8px; font-size: 13px; }
    </style>
</head>
<body>
    <h1>Temario: {{ $materia->nombre ?? 'Materia' }}</h1>
    <div class="meta">Créditos: {{ $materia->creditos ?? '—' }} &middot; Semestre: {{ $materia->semestre ?? '—' }}</div>

    @if(count($temario))
        <ol>
            @foreach($temario as $tema)
                <li>{{ $tema }}</li>
            @endforeach
        </ol>
    @else
        <p>Esta materia aún no tiene temario registrado.</p>
    @endif
</body>
</html>
