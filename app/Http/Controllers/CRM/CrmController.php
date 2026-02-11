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

    public function prospectos()
    {
        return view('crm.prospectos');
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

}
