<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Lead;
use App\Models\Users\User;


class CRMController extends Controller
{
    public function leads()
{
    $leads = Lead::with('seguimientos')
        ->orderBy('created_at', 'desc')
        ->get();

    // 🔵 OBTENER SOLO USUARIOS CON ROL CTP
    $ctps = User::whereHas('roles', function ($q) {
        $q->where('name', 'ctp');
    })->get();

    return view('crm.leads', compact('leads', 'ctps'));
}


    public function estadisticas()
    {
        return view('crm.estadisticas');
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
    public function asignarCTP(Request $request, Lead $lead)
    {
        $lead->ctp_id = $request->ctp_id;
        $lead->save();
    
        return response()->json(['success' => true]);
    }
    

}
