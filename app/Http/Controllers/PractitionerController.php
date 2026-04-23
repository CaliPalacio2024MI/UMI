<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class PractitionerController extends Controller
{
    public function filter(Request $request)
    {
        $department = $request->department_id;
        $position = $request->position_id;

        $users = User::where('department_id', $department)
                     ->where('position_id', $position)
                     ->get();

        return response()->json($users);
    }
}