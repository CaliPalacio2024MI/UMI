<?php

namespace App\Http\Controllers\Cursos;

use App\Http\Controllers\Controller;
use App\Models\Cursos\Course;
use App\Models\Cursos\CoursePeriod;
use Illuminate\Http\Request;

class CoursePeriodsController extends Controller
{
    /**
     * Mostrar períodos del curso
     */
    public function index(Course $course)
    {
        $this->authorize('update', $course);
        
        $periods = $course->periods()->orderBy('start_date', 'desc')->get();
        
        return view('layouts.Cursos.periods.index', compact('course', 'periods'));
    }

    /**
     * Crear nuevo período
     */
    public function store(Request $request, Course $course)
    {
        $this->authorize('update', $course);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'is_active' => 'boolean'
        ]);
        
        $course->periods()->create($validated);
        
        return redirect()->back()->with('success', 'Período creado exitosamente');
    }

    /**
     * Actualizar período
     */
    public function update(Request $request, Course $course, CoursePeriod $period)
    {
        $this->authorize('update', $course);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'is_active' => 'boolean'
        ]);
        
        $period->update($validated);
        
        return redirect()->back()->with('success', 'Período actualizado exitosamente');
    }

    /**
     * Eliminar período
     */
    public function destroy(Course $course, CoursePeriod $period)
    {
        $this->authorize('update', $course);
        
        $period->delete();
        
        return redirect()->back()->with('success', 'Período eliminado exitosamente');
    }
}