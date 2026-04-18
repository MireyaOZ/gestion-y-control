<?php

use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmailRequestController;
use App\Http\Controllers\LinkController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SystemRecordController;
use App\Http\Controllers\SubtaskController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/correos', [EmailRequestController::class, 'index'])->name('emails.index');
    Route::get('/correos/reporte/{format}', [EmailRequestController::class, 'report'])->name('emails.report');
    Route::get('/correos/{emailRequest}/historial/reporte/{format}', [EmailRequestController::class, 'historyReport'])->name('emails.history.report');
    Route::post('/correos', [EmailRequestController::class, 'store'])->name('emails.store');
    Route::patch('/correos/{emailRequest}', [EmailRequestController::class, 'update'])->name('emails.update');
    Route::delete('/correos/{emailRequest}', [EmailRequestController::class, 'destroy'])->name('emails.destroy');
    Route::get('/sistemas', [SystemRecordController::class, 'index'])->name('systems.index');
    Route::get('/sistemas/reporte/{format}', [SystemRecordController::class, 'report'])->name('systems.report');
    Route::get('/sistemas/{system}/historial/reporte/{format}', [SystemRecordController::class, 'historyReport'])->name('systems.history.report');
    Route::post('/sistemas', [SystemRecordController::class, 'store'])->name('systems.store');
    Route::patch('/sistemas/{system}', [SystemRecordController::class, 'update'])->name('systems.update');
    Route::delete('/sistemas/{system}', [SystemRecordController::class, 'destroy'])->name('systems.destroy');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/search/users', [SearchController::class, 'users'])->name('search.users');

    Route::resource('tasks', TaskController::class);
    Route::patch('tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.status');
    Route::resource('subtasks', SubtaskController::class);
    Route::patch('subtasks/{subtask}/status', [SubtaskController::class, 'updateStatus'])->name('subtasks.status');

    Route::post('/comments/{type}/{id}', [CommentController::class, 'store'])->name('comments.store');
    Route::post('/links/{type}/{id}', [LinkController::class, 'store'])->name('links.store');
    Route::delete('/links/{link}', [LinkController::class, 'destroy'])->name('links.destroy');
    Route::post('/attachments/{type}/{id}', [AttachmentController::class, 'store'])->name('attachments.store');
    Route::delete('/attachments/{attachment}', [AttachmentController::class, 'destroy'])->name('attachments.destroy');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', UserController::class);
        Route::resource('roles', RoleController::class);
        Route::resource('permissions', PermissionController::class)->except(['show']);
    });
});

require __DIR__.'/auth.php';
