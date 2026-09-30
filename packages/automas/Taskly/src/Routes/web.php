<?php

use Illuminate\Support\Facades\Route;
use Automas\Taskly\Http\Controllers\DashboardController;
use Automas\Taskly\Http\Controllers\ProjectController;
use Automas\Taskly\Http\Controllers\ProjectTaskController;
use Automas\Taskly\Http\Controllers\TaskStageController;
use Automas\Taskly\Http\Controllers\BugStageController;
use Automas\Taskly\Http\Controllers\ProjectBugController;
use Automas\Taskly\Http\Controllers\ProjectPaymentController;
use Automas\Taskly\Http\Controllers\ProjectReportController;

// API Routes for other packages
Route::middleware(['web', 'auth', 'verified', 'PlanModuleCheck:Taskly'])->prefix('api/taskly')->name('api.taskly.')->group(function () {
    Route::get('/projects', [ProjectController::class, 'apiIndex'])->name('projects.index');
    Route::get('/projects/{project}/tasks', [ProjectTaskController::class, 'apiTasks'])->name('projects.tasks');
});

Route::middleware(['web', 'auth', 'verified', 'PlanModuleCheck:Taskly'])->group(function () {
    Route::get('/project/dashboard', [DashboardController::class, 'index'])->name('project.dashboard.index');
    Route::get('/projects', [ProjectController::class, 'index'])->name('project.index');

    // Task routes - must come before generic project routes
    Route::get('/projects/tasks', [ProjectTaskController::class, 'index'])->name('project.tasks.index');
    Route::post('/projects/tasks/store', [ProjectTaskController::class, 'store'])->name('project.tasks.store');
    Route::get('/projects/tasks/{task}', [ProjectTaskController::class, 'show'])->name('project.tasks.show');
    Route::get('/projects/tasks/kanban/{project}', [ProjectTaskController::class, 'kanban'])->name('project.tasks.kanban');
    Route::get('/projects/tasks/calendar/{project}', [ProjectTaskController::class, 'calendar'])->name('project.tasks.calendar');
    Route::patch('/projects/tasks/{task}/move', [ProjectTaskController::class, 'move'])->name('project.tasks.move');
    Route::put('/projects/tasks/{task}', [ProjectTaskController::class, 'update'])->name('project.tasks.update');
    Route::delete('/projects/tasks/{task}', [ProjectTaskController::class, 'destroy'])->name('project.tasks.destroy');
    Route::get('/projects/{project}/tasks/api', [ProjectTaskController::class, 'getTasks'])->name('project.tasks.api');

    // Project report routes
    Route::get('/project/report', [ProjectReportController::class, 'index'])->name('project.report.index');
    Route::get('/project/report/{id}', [ProjectReportController::class, 'show'])->name('project.report.show');
    // Task comments and subtasks
    Route::get('/projects/tasks/{task}/comments', [ProjectTaskController::class, 'getComments'])->name('project.tasks.comments.index');
    Route::post('/projects/tasks/{task}/comments', [ProjectTaskController::class, 'storeComment'])->name('project.tasks.comments.store');
    Route::delete('/projects/tasks/comments/{comment}', [ProjectTaskController::class, 'destroyComment'])->name('project.tasks.comments.destroy');
    Route::get('/projects/tasks/{task}/subtasks', [ProjectTaskController::class, 'getSubtasks'])->name('project.tasks.subtasks.index');
    Route::post('/projects/tasks/{task}/subtasks', [ProjectTaskController::class, 'storeSubtask'])->name('project.tasks.subtasks.store');
    Route::patch('/projects/tasks/subtasks/{subtask}/toggle', [ProjectTaskController::class, 'toggleSubtask'])->name('project.tasks.subtasks.toggle');

    // Task files
    Route::post('/projects/tasks/{task}/files', [ProjectTaskController::class, 'storeFile'])->name('project.tasks.files.store');
    Route::delete('/projects/tasks/files/{file}', [ProjectTaskController::class, 'deleteFile'])->name('project.tasks.files.delete');

    // Bug routes - must come before generic project routes
    Route::get('/projects/bugs', [ProjectBugController::class, 'index'])->name('project.bugs.index');
    Route::get('/projects/bugs/kanban/{project}', [ProjectBugController::class, 'kanban'])->name('project.bugs.kanban');
    Route::post('/projects/bugs', [ProjectBugController::class, 'store'])->name('project.bugs.store');
    Route::get('/projects/bugs/{bug}', [ProjectBugController::class, 'show'])->name('project.bugs.show');
    Route::put('/projects/bugs/{bug}', [ProjectBugController::class, 'update'])->name('project.bugs.update');
    Route::delete('/projects/bugs/{bug}', [ProjectBugController::class, 'destroy'])->name('project.bugs.destroy');
    Route::patch('/projects/bugs/{bug}/move', [ProjectBugController::class, 'move'])->name('project.bugs.move');
    Route::get('/projects/{project}/bugs/api', [ProjectBugController::class, 'getBugs'])->name('project.bugs.api');

    // Bug comments
    Route::get('/projects/bugs/{bug}/comments', [ProjectBugController::class, 'getComments'])->name('project.bugs.comments.index');
    Route::post('/projects/bugs/{bug}/comments', [ProjectBugController::class, 'storeComment'])->name('project.bugs.comments.store');
    Route::delete('/projects/bugs/comments/{comment}', [ProjectBugController::class, 'destroyComment'])->name('project.bugs.comments.destroy');

    // Bug files
    Route::post('/projects/bugs/{bug}/files', [ProjectBugController::class, 'storeFile'])->name('project.bugs.files.store');
    Route::delete('/projects/bugs/files/{file}', [ProjectBugController::class, 'deleteFile'])->name('project.bugs.files.delete');

    // Project files
    Route::post('/projects/{project}/files', [ProjectController::class, 'storeFiles'])->name('project.files.store');
    Route::delete('/projects/files/{file}', [ProjectController::class, 'deleteFile'])->name('project.files.delete');

    // Project routes - must come after task and bug routes
    Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('project.show');
    Route::get('/projects/{project}/gantt', [ProjectController::class, 'gantt'])->name('project.gantt');
    Route::get('/projects/{project}/edit', [ProjectController::class, 'edit'])->name('project.edit');
    Route::post('/projects/{project}/invite', [ProjectController::class, 'invite'])->name('project.invite');
    Route::delete('/projects/{project}/delete-member', [ProjectController::class, 'deleteMember'])->name('project.delete-member');
    Route::post('/projects/{project}/invite-client', [ProjectController::class, 'inviteClient'])->name('project.invite-client');
    Route::delete('/projects/{project}/delete-client', [ProjectController::class, 'deleteClient'])->name('project.delete-client');
    Route::post('/projects/{project}/milestones', [ProjectController::class, 'storeMilestone'])->name('project.milestones.store');
    Route::put('/projects/{project}/milestones', [ProjectController::class, 'updateMilestone'])->name('project.milestones.update');
    Route::delete('/projects/{project}/milestones', [ProjectController::class, 'deleteMilestone'])->name('project.milestones.delete');
    Route::post('/projects', [ProjectController::class, 'store'])->name('project.store');
    Route::put('/projects/{project}', [ProjectController::class, 'update'])->name('project.update');
    Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])->name('project.destroy');
    Route::post('/projects/{project}/duplicate', [ProjectController::class, 'duplicate'])->name('project.duplicate');

    Route::get('/project/task-stages/index', [TaskStageController::class, 'index'])->name('project.task-stages.index');
    Route::post('/project/task-stages/store', [TaskStageController::class, 'store'])->name('project.task-stages.store');
    Route::put('/project/task-stages/reorder', [TaskStageController::class, 'reorder'])->name('project.task-stages.reorder');
    Route::put('/project/task-stages/{taskStage}', [TaskStageController::class, 'update'])->name('project.task-stages.update');
    Route::delete('/project/task-stages/{taskStage}', [TaskStageController::class, 'destroy'])->name('project.task-stages.destroy');

    Route::get('/project/bug-stages/index', [BugStageController::class, 'index'])->name('project.bug-stages.index');
    Route::post('/project/bug-stages/store', [BugStageController::class, 'store'])->name('project.bug-stages.store');
    Route::put('/project/bug-stages/reorder', [BugStageController::class, 'reorder'])->name('project.bug-stages.reorder');
    Route::put('/project/bug-stages/{bugStage}', [BugStageController::class, 'update'])->name('project.bug-stages.update');
    Route::delete('/project/bug-stages/{bugStage}', [BugStageController::class, 'destroy'])->name('project.bug-stages.destroy');

    // Project Payment routes
    Route::prefix('project-payments')->name('project-payments.')->group(function () {
        Route::get('/get-project-milestones', [ProjectPaymentController::class, 'getProjectMilestones'])->name('get-milestones');
        Route::get('/', [ProjectPaymentController::class, 'index'])->name('index');
        Route::get('/create', [ProjectPaymentController::class, 'create'])->name('create');
        Route::post('/', [ProjectPaymentController::class, 'store'])->name('store');
        Route::get('/{projectPayment}', [ProjectPaymentController::class, 'show'])->name('show');
        Route::get('/{projectPayment}/edit', [ProjectPaymentController::class, 'edit'])->name('edit');
        Route::put('/{projectPayment}', [ProjectPaymentController::class, 'update'])->name('update');
        Route::delete('/{projectPayment}', [ProjectPaymentController::class, 'destroy'])->name('destroy');
        Route::post('/{projectPayment}/post', [ProjectPaymentController::class, 'post'])->name('post');
        Route::get('/{projectPayment}/print', [ProjectPaymentController::class, 'print'])->name('print');
    });
});
