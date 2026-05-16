<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Users\Department;
use Illuminate\Http\Request;
use App\Models\Cursos\CourseSession;

class GroupsController extends Controller
{
    public function index()

{
    $groups = Group::all();
    $departments = Department::with('workstations')->get();

    return view('groups.index', compact('groups', 'departments'));
}

    public function store(Request $request)
    {
        $session = CourseSession::findOrFail($request->session_id);

        $group = $session->groups()->first();

        if (!$group) {
            $group = Group::create([
                'type' => $request->type,
                'min_participants' => $request->min_participants,
                'max_participants' => $request->max_participants,
                'institution_id' => session('active_institution_id'),
            ]);

            $group->sessions()->attach($session->id);
        }

        $group->departments()->sync($request->departments);
        $group->workstations()->sync($request->workstations);
    }

    public function destroy(Group $group)
    {
       $group->delete();

       return back()->with('success', 'Grupo eliminado correctamente');
    }

    public function edit(Group $group)
    {
        return view('groups.edit', compact('group'));
    }

    public function update(Request $request, Group $group)
    {
    $request->validate([
        'name' => 'required',
        'type' => 'required|in:abierto,cerrado',
        'min_participants' => 'required|integer',
        'max_participants' => 'required|integer',
    ]);

    $group->update($request->only([
        'name',
        'type',
        'min_participants',
        'max_participants'
    ]));

    return redirect()->route('groups.index')
        ->with('success', 'Grupo actualizado correctamente');
    }

    public function addParticipants(Request $request)
    {
    //  Obtener sesión
    $session = CourseSession::findOrFail($request->session_id);

    //  Obtener grupo ligado a ese horario
    $group = $session->groups()->first();

    if (!$group) {
        return back()->withErrors(['error' => 'Primero configura el grupo']);
    }

    // Guardar participantes
    $group->participants()->syncWithoutDetaching($request->users);
        return back()->with('success', 'Participantes agregados');
    }
}
