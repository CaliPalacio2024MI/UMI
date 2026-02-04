<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CRMController extends Controller
{
    public function leads()
    {
        return view('crm.leads');
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
