<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CRMController extends Controller
{
    public function index()
    {
        return view('crm.index');
    }

    public function clientes()
    {
        return view('crm.clientes');
    }

    public function seguimiento()
    {
        return view('crm.seguimiento');
    }

    public function reportes()
    {
        return view('crm.reportes');
    }
}
