<?php

namespace App\Support;

use Carbon\Carbon;

/**
 * Parsea textos como "Martes 00:00 – 15:00, Jueves 08:00 – 10:00" (módulo Clases).
 */
final class HorarioResumenParser
{
    /**
     * @return array<int, array{dia: int, startMin: int, endMin: int}>
     */
    public static function intervalsFromResumen(?string $text): array
    {
        if ($text === null || trim($text) === '') {
            return [];
        }

        $dias = [
            'lunes' => 1,
            'martes' => 2,
            'miercoles' => 3,
            'jueves' => 4,
            'viernes' => 5,
            'sabado' => 6,
            'domingo' => 7,
        ];

        $out = [];
        $parts = preg_split('/\s*,\s*/u', $text) ?: [];

        foreach ($parts as $part) {
            $part = trim($part);
            if ($part === '') {
                continue;
            }

            if (!preg_match(
                '/^(?P<dia>[^\d]+?)\s+(?P<hi>\d{1,2}:\d{2})\s*[–\-\x{2013}]\s*(?P<hf>\d{1,2}:\d{2})\s*$/u',
                $part,
                $m
            )) {
                continue;
            }

            $diaNorm = str_replace(
                ['á', 'é', 'í', 'ó', 'ú'],
                ['a', 'e', 'i', 'o', 'u'],
                mb_strtolower(trim($m['dia']))
            );
            $diaNorm = (string) (preg_replace('/\s+/u', '', $diaNorm) ?? $diaNorm);

            if (!isset($dias[$diaNorm])) {
                continue;
            }

            try {
                $t1 = Carbon::createFromFormat('H:i', $m['hi']);
                $t2 = Carbon::createFromFormat('H:i', $m['hf']);
            } catch (\Throwable) {
                continue;
            }

            $startMin = $t1->hour * 60 + $t1->minute;
            $endMin = $t2->hour * 60 + $t2->minute;
            if ($endMin <= $startMin) {
                continue;
            }

            $out[] = [
                'dia' => $dias[$diaNorm],
                'startMin' => $startMin,
                'endMin' => $endMin,
            ];
        }

        return $out;
    }
}
