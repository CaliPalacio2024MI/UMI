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
     * Texto de la opción en el select de aula del formulario de horario.
     */
    public static function selectOptionSoloSeccion(Facility $f): string
    {
        $n = trim((string) ($f->nombre_aula ?? ''));

        return $n !== '' ? $n : '—';
    }

    /** Etiqueta legible completa (listados, API label, etc.). */
    public static function selectLabel(Facility $f): string
    {
        $parts = array_filter([
            trim((string) ($f->nombre_aula ?? '')) ?: null,
            $f->tipo_materia ? trim((string) $f->tipo_materia) : null,
        ]);

        return $parts !== [] ? implode(' — ', $parts) : '—';
    }

    /** Una línea legible en grillas / tarjetas (horario maestro o alumno). */
    public static function grillaLine(?Facility $f): string
    {
        if ($f === null) {
            return '—';
        }

        $n = trim((string) ($f->nombre_aula ?? ''));

        return $n !== '' ? $n : '—';
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
            'nombre_aula' => $f->nombre_aula,
            'tipo_materia' => $f->tipo_materia,
            'career_id' => $f->career_id,
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
            (string) ($f->nombre_aula ?? ''),
            (string) ($f->tipo_materia ?? ''),
        ]))));
    }
}
