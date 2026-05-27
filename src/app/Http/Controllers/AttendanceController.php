<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function showAttendancePage()
    {
        return view('attendance.index');
    }
}
