<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Cursos\Activities;
use App\Models\Cursos\Topics;

return new class extends Migration
{
    public function up()
    {
        // Migrar actividades independientes a topics
        $independentActivities = Activities::whereNull('topic_id')
            ->whereNull('subtopic_id')
            ->where('is_final_exam', false)
            ->get();

        foreach ($independentActivities as $activity) {
            // Crear un Topic por cada actividad independiente
            Topics::create([
                'course_id' => $activity->course_id,
                'title' => $activity->title,
                'description' => "Actividad: {$activity->type}",
                'file_path' => $activity->file_path,
                'show_title' => $activity->show_title ?? true,
                'show_turtle' => false,
                'turtle_voice' => null,
                'order' => $activity->order,

                // Campos nuevos de actividad
                'is_activity' => true,
                'activity_type' => $activity->type,
                'activity_content' => json_encode($activity->content),
            ]);

            // Opcional: Eliminar la actividad antigua
            // $activity->delete();
        }

        echo "✅ Migradas " . $independentActivities->count() . " actividades independientes a Topics\n";
    }

    public function down()
    {
        // Revertir: Mover topics con is_activity=true de vuelta a activities
        $activityTopics = Topics::where('is_activity', true)->get();

        foreach ($activityTopics as $topic) {
            Activities::create([
                'course_id' => $topic->course_id,
                'topic_id' => null,
                'subtopic_id' => null,
                'title' => $topic->title,
                'type' => $topic->activity_type,
                'content' => json_decode($topic->activity_content, true),
                'file_path' => $topic->file_path,
                'show_title' => $topic->show_title,
                'order' => $topic->order,
                'is_final_exam' => false,
            ]);

            $topic->delete();
        }
    }
};
