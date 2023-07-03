<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('projects_id');
            $table->string('task_name');
            $table->date('start_at');
            $table->date('goal_at');
            $table->date('end_at');
            $table->integer('buffer_time');
            $table->string('buffer_reason');
            $table->enum('state', ['done', 'todo', 'wip', 'cancel']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
