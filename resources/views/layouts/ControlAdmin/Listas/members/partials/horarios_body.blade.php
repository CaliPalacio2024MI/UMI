<div class="teacher-horarios-body">
    @include('layouts.ControlAdmin.Listas.members.partials.horarios_grilla_semanal', ['horarios' => $horarios, 'esAlumno' => $esAlumno ?? false, 'materiaLabels' => $materiaLabels ?? [], 'horarioResumenPorClase' => $horarioResumenPorClase ?? []])
</div>
