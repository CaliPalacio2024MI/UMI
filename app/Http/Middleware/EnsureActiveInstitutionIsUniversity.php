<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restringe el acceso a rutas que solo aplican en contexto Universidad Mundo Imperial.
 */
class EnsureActiveInstitutionIsUniversity
{
    public const UNIVERSITY_NAME = 'Universidad Mundo Imperial';

    public function handle(Request $request, Closure $next): Response
    {
        $active = (string) session('active_institution_name', '');

        if ($active !== self::UNIVERSITY_NAME) {
            abort(403, 'El módulo de facturación solo está disponible en la unidad Universidad Mundo Imperial.');
        }

        return $next($request);
    }
}
