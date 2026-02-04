<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CRMController extends Controller
{
    public function leads()
    {
        $leads = \App\Models\Lead::orderBy('created_at', 'desc')->get();
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
}
