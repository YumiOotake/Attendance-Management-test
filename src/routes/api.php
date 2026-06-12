<?php

use App\Http\Controllers\Api\V1\AttendanceRecordController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('v1')->group(function () {
    Route::apiResource('attendance-records', AttendanceRecordController::class)
        ->parameters([
            'attendance-records' => 'attendanceRecord',
        ])
        ->middleware([
            'store' => 'auth:sanctum',
            'update' => 'auth:sanctum',
            'destroy' => 'auth:sanctum',
        ]);
});

Route::post('/login', function (Request $request) {
    $user = User::where('email', $request->email)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    $token = $user->createToken('api-token')->plainTextToken;

    return response()->json(['token' => $token]);
});
