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
    $query = Lead::with('seguimientos');

    $rol = session('active_role_name');
    $userId = auth()->id();

    // 👉 SI ES CTP: solo sus leads asignados
    if ($rol === 'ctp') {
        $query->where('ctp_id', $userId);
    }

    // 👉 Master y Coordinador ven todo
    $leads = $query->orderBy('created_at', 'desc')->get();

    // Solo master y coordinador necesitan la lista de CTPs
    $ctps = [];
    if (in_array($rol, ['master', 'coordinador_ctp'])) {
        $ctps = \App\Models\Users\User::whereHas('roles', function ($q) {
            $q->where('name', 'ctp');
        })->get();
    }

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

    return response()->json([
        'success' => true,
        'seguimientos' => $lead->seguimientos()->orderBy('id')->get()
    ]);
}
public function prospectos(Request $request)
{
    $query = Lead::query();

    $rol = session('active_role_name');
    $userId = auth()->id();

    // 🔒 CTP solo ve SUS prospectos
    if ($rol === 'ctp') {
        $query->where('ctp_id', $userId);
    }

    // 🔍 Buscador
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('rfc', 'like', "%$search%")
              ->orWhere('alumno_nombre', 'like', "%$search%")
              ->orWhere('alumno_paterno', 'like', "%$search%")
              ->orWhere('alumno_materno', 'like', "%$search%")
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
