<?php

namespace App\Support;

use App\Models\AdmonCont\Facility;

/**
 * Textos y payloads de aulas reutilizables en:
 * Control → Horarios, horario de docente y horario de alumno.
 */
class AulaHorarioPresenter
{
    /**
     * Texto del <option> en el select de aula del formulario de horario:
     * solo la sección (ej. 2~A). Si no hay sección, el número de aula.
     */
    public static function selectOptionSoloSeccion(Facility $f): string
    {
        $seccion = trim((string) ($f->seccion ?? ''));
        if ($seccion !== '' && $seccion !== 'Sin sección') {
            return $seccion;
        }

        return (string) ($f->numero_aula ?? '');
    }

    /** Etiqueta legible completa (listados, API label, etc.). */
    public static function selectLabel(Facility $f): string
    {
        $seccion = trim((string) ($f->seccion ?? ''));
        $seccionPart = ($seccion !== '' && $seccion !== 'Sin sección') ? $seccion : null;

        $parts = array_filter([
            $f->numero_aula,
            $seccionPart,
            ($f->tipo ?? '') !== '' && ($f->tipo ?? '') !== 'Aula' ? $f->tipo : null,
            $f->ubicacion ? (string) $f->ubicacion : null,
            $f->capacidad !== null ? 'Cap. ' . $f->capacidad : null,
        ]);

        return $parts !== [] ? implode(' — ', $parts) : (string) ($f->numero_aula ?? '');
    }

    /** Una línea legible en grillas / tarjetas (horario maestro o alumno). */
    public static function grillaLine(?Facility $f): string
    {
        if ($f === null) {
            return '—';
        }

        $base = 'Aula ' . $f->numero_aula;
        $extra = array_filter([
            $f->ubicacion ? '(' . $f->ubicacion . ')' : null,
            ($f->tipo ?? '') !== '' && ($f->tipo ?? '') !== 'Aula' ? $f->tipo : null,
        ]);

        return $extra === [] ? $base : $base . ' ' . implode(' ', $extra);
    }

    /** Celda tipo tabla (listados compactos). */
    public static function tablaResumen(?Facility $f): string
    {
        return $f === null ? '—' : self::selectLabel($f);
    }

    /**
     * Payload JSON para modales/API (módulo Horarios u otros consumidores).
     *
     * @return array<string, mixed>|null
     */
    public static function toApiArray(?Facility $f): ?array
    {
        if ($f === null) {
            return null;
        }

        return [
            'id' => $f->id,
            'numero_aula' => $f->numero_aula,
            'seccion' => $f->seccion,
            'ubicacion' => $f->ubicacion,
            'tipo' => $f->tipo,
            'capacidad' => $f->capacidad,
            'label' => self::selectLabel($f),
        ];
    }

    /** Texto en minúsculas para filtros de búsqueda (ej. alumno por aula). */
    public static function searchBlob(?Facility $f): string
    {
        if ($f === null) {
            return '';
        }

        return mb_strtolower(trim(implode(' ', array_filter([
            (string) ($f->numero_aula ?? ''),
            (string) ($f->seccion ?? ''),
            (string) ($f->ubicacion ?? ''),
            (string) ($f->tipo ?? ''),
            $f->capacidad !== null ? (string) $f->capacidad : '',
        ]))));
    }
}
