<?php

namespace App\Http\Controllers;

use App\Models\Cursos\Course;
use FontLib\Table\Type\name;
use Illuminate\Http\Request;

class TopicManagerController extends Controller
{
    public function index()
    {
        $courses = Course::orderBy('title')->get();
        return view('topics.manager', compact('courses'));
    }
}
