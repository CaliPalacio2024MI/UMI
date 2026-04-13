<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Users\Workstation;

class WorkstationController extends Controller
{
    public function byDepartments(Request $request)
    {
        $departments = $request->departments;

        $workstations = Workstation::whereIn('department_id', $departments)
            ->select('id', 'name')
            ->get();

        return response()->json($workstations);
    }
}
