<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AttendanceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware('auth')->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'showAttendancePage'])->name('attendance.index');
    Route::patch('/attendance/clock-in', [AttendanceController::class, 'clockIn'])->name('attendance.clock-in');
    Route::patch('/attendance/{attendance}/clock-out', [AttendanceController::class, 'clockOut'])->name('attendance.clock-out');
    Route::patch('/attendance/{attendance}/break-start', [AttendanceController::class, 'breakStart'])->name('attendance.break-start');
    Route::patch('/attendance/{attendance}/break-end', [AttendanceController::class, 'breakEnd'])->name('attendance.break-end');
    Route::get('/attendance/list', [AttendanceController::class, 'list'])->name('attendance.list');
    Route::get('/attendance/detail/{id}', [AttendanceController::class, 'show'])->name('attendance.show');
    Route::get('/stamp_correction_request/list', [AttendanceController::class, 'requestList'])->name('attendance.request.list');//2ルート分
});

Route::get('admin/login', [AdminController::class, 'showLoginForm'])->name('admin.login');

Route::middleware(['auth', 'admin'])->group(function () {

    Route::get('/admin/attendance/list', [AdminController::class, 'attendanceList'])->name('admin.list');
    Route::get('/admin/attendance/{id}', [AdminController::class, 'attendanceDetail'])->name('admin.detail');
    Route::get('/admin/staff/list', [AdminController::class, 'staffList'])->name('admin.staff.list');
    Route::get('/admin/attendance/staff/{id}', [AdminController::class, 'staffAttendanceList'])->name('admin.attendance.staff');
    Route::get('/stamp_correction_request/approve/{attendance_correct_request_id}', [AdminController::class, 'requestApprove'])->name('admin.request.approve');
});
