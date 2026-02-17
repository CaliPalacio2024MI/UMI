<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Lead;


class CRMController extends Controller
{
    public function leads()
    {
        $leads = \App\Models\Lead::with('seguimientos')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('crm.leads', compact('leads'));
    }

    public function estadisticas()
    {
        $leads = Lead::orderBy('created_at', 'desc')->get();
        return view('crm.estadisticas', compact('leads'));
    }
    public function destroy(Lead $lead)
{
    $lead->delete();
    return response()->json(['success' => true]);
}

public function guardarSeguimiento(Request $request, Lead $lead)
{
    $lead->seguimientos()->create([
        'estado' => $request->estado,
        'fecha' => now()->toDateString(),
        'hora' => now()->toTimeString(),
    ]);

    return response()->json(['success' => true]);
}
    public function prospectos(Request $request)
    {
        $query = Lead::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('rfc', 'like', "%$search%")
                  ->orWhere('alumno_nombre', 'like', "%$search%")
                  ->orWhere('alumno_paterno', 'like', "%$search%")
                  ->orWhere('alumno_materno', 'like', "%$search%")
                  ->orWhere('curp', 'like', "%$search%")
                  ->orWhere('clasificacion', 'like', "%$search%");
            });
        }

        $leads = $query->orderBy('created_at', 'desc')->get();

        return view('crm.prospectos', compact('leads'));
    }


}
