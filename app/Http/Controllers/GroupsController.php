<?php

namespace App\Http\Controllers;
use App\Models\Users\Department;

class GroupsController extends Controller
{
public function index()
{
    $departments = Department::with('workstations')->get();

    return view('groups.index', compact('departments'));
}
}
