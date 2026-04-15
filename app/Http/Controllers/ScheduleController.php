<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Schedule;

class ScheduleController extends Controller
{
    public function destroy(Schedule $schedule)
{
    // Elimina relaciones en tabla pivote automáticamente si usaste cascade
    $schedule->delete();

    return redirect()->back()->with('success', 'Horario eliminado correctamente');
}
}