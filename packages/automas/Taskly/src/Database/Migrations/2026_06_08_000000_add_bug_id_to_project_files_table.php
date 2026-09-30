<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('project_files', function (Blueprint $table) {
            if (!Schema::hasColumn('project_files', 'bug_id')) {
                $table->unsignedBigInteger('project_id')->nullable()->change();
                $table->unsignedBigInteger('bug_id')->nullable()->after('project_id');
                $table->foreign('bug_id')->references('id')->on('project_bugs')->onDelete('cascade');
            }
        });
    }

    public function down()
    {
        Schema::table('project_files', function (Blueprint $table) {
            if (Schema::hasColumn('project_files', 'bug_id')) {
                $table->dropForeign(['bug_id']);
                $table->dropColumn('bug_id');
            }
        });
    }
};
