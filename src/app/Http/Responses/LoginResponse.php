<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        $user = auth()->user();

        if ($request->wantsJson()) {
            return response()->json(['two_factor' => false]);
        }

        if ($user->admin_status) {
            return redirect()->route('admin.list');
        }

        return redirect()->route('attendance.index');
    }
}
