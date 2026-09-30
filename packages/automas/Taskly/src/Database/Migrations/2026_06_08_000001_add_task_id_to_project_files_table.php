<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('project_files', function (Blueprint $table) {
            if (!Schema::hasColumn('project_files', 'task_id')) {
                $table->unsignedBigInteger('task_id')->nullable()->after('bug_id');
                $table->foreign('task_id')->references('id')->on('project_tasks')->onDelete('cascade');
            }
        });
    }

    public function down()
    {
        Schema::table('project_files', function (Blueprint $table) {
            if (Schema::hasColumn('project_files', 'task_id')) {
                $table->dropForeign(['task_id']);
                $table->dropColumn('task_id');
            }
        });
    }
};
