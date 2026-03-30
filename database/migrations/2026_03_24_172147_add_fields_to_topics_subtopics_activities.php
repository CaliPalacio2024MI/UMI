<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Verificar y agregar columnas a TOPICS
        Schema::table('topics', function (Blueprint $table) {
            if (!Schema::hasColumn('topics', 'show_title')) {
                $table->boolean('show_title')->default(true)->after('description');
            }
            if (!Schema::hasColumn('topics', 'show_turtle')) {
                $table->boolean('show_turtle')->default(false)->after('show_title');
            }
            if (!Schema::hasColumn('topics', 'turtle_voice')) {
                $table->integer('turtle_voice')->nullable()->after('show_turtle');
            }
            if (!Schema::hasColumn('topics', 'order')) {
                $table->integer('order')->default(0)->after('turtle_voice');
            }
        });

        // Verificar y agregar columnas a SUBTOPICS
        Schema::table('subtopics', function (Blueprint $table) {
            if (!Schema::hasColumn('subtopics', 'show_title')) {
                $table->boolean('show_title')->default(true)->after('description');
            }
            if (!Schema::hasColumn('subtopics', 'show_turtle')) {
                $table->boolean('show_turtle')->default(false)->after('show_title');
            }
            if (!Schema::hasColumn('subtopics', 'turtle_voice')) {
                $table->integer('turtle_voice')->nullable()->after('show_turtle');
            }
            if (!Schema::hasColumn('subtopics', 'order')) {
                $table->integer('order')->default(0)->after('turtle_voice');
            }
        });

        // Verificar y agregar columnas a ACTIVITIES
        Schema::table('activities', function (Blueprint $table) {
            if (!Schema::hasColumn('activities', 'show_title')) {
                $table->boolean('show_title')->default(true)->after('title');
            }
            if (!Schema::hasColumn('activities', 'order')) {
                $table->integer('order')->default(0)->after('show_title');
            }
        });
    }

    public function down()
    {
        Schema::table('topics', function (Blueprint $table) {
            $table->dropColumn(['show_title', 'show_turtle', 'turtle_voice', 'order']);
        });

        Schema::table('subtopics', function (Blueprint $table) {
            $table->dropColumn(['show_title', 'show_turtle', 'turtle_voice', 'order']);
        });

        Schema::table('activities', function (Blueprint $table) {
            $table->dropColumn(['show_title', 'order']);
        });
    }
};
