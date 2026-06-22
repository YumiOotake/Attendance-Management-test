<?php

namespace App\Policies;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AttendanceRecordPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * 勤怠詳細の閲覧権限を確認する
     *
     * @param  User  $user 認証ユーザー
     * @param  Attendance  $attendance 勤怠レコード
     * @return bool
     */
    public function view(User $user, Attendance $attendance): bool
    {
        return $user->id === $attendance->user_id;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * 勤怠の編集権限を確認する
     *
     * @param  User  $user 認証ユーザー
     * @param  Attendance  $attendance 勤怠レコード
     * @return bool
     */
    public function update(User $user, Attendance $attendance): bool
    {
        return $user->id === $attendance->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Attendance  $attendance
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Attendance $attendance): bool
    {
        return $user->id === $attendance->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Attendance  $attendance
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Attendance $attendance): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Attendance  $attendance
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, Attendance $attendance): bool
    {
        return false;
    }

    public function before(User $user, string $ability): bool|null
    {
        if ($user->admin_status) {
            return true;
        }
        return null;
    }
}
