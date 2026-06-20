<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\IndexAttendanceRecordRequest;
use App\Http\Requests\Api\V1\StoreAttendanceRecordRequest;
use App\Http\Requests\Api\V1\UpdateAttendanceRecordRequest;
use App\Http\Resources\AttendanceRecordResource;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\Attendance;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

class AttendanceRecordController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->only(['store', 'update', 'destroy']);
    }

    /**
     * 勤怠一覧を取得する
     *
     * @param Request $request
     * @return AnonymousResourceCollection 勤怠コレクション
     */
    public function index(IndexAttendanceRecordRequest $request): AnonymousResourceCollection
    {
        $userId = $request->user_id;
        $date = $request->date;
        $month = $request->month;
        $page = $request->page ?? 1;
        $perPage = $request->per_page ?? 20;

        $attendanceRecord_records = Attendance::with('user', 'breakTimes')
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->when($date, fn($q) => $q->where('date', $date))
            ->when($month, function ($q) use ($month) {
                $targetMonth = Carbon::createFromFormat('Y-m', $month);
                $startOfMonth = $targetMonth->copy()->startOfMonth();
                $endOfMonth = $targetMonth->copy()->endOfMonth();
                $q->whereBetween('date', [$startOfMonth, $endOfMonth]);
            })
            ->latest('date')
            ->paginate($perPage, ['*'], 'page', $page);

        return AttendanceRecordResource::collection($attendanceRecord_records);
    }

    /**
     * 勤怠を新規登録する
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreAttendanceRecordRequest $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validated();

        $attendanceRecord = $request->user()->attendances()->create($validated);
        $attendanceRecord->load(['user', 'breakTimes']);

        return (new AttendanceRecordResource($attendanceRecord))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * 勤怠詳細を取得する
     *
     * @param  int Attendance $attendanceRecord
     * @return AttendanceRecordResource
     */
    public function show(Attendance $attendanceRecord): AttendanceRecordResource
    {
        $attendanceRecord->load([
            'user',
            'breakTimes',
            'attendanceRequests.requestBreakTimes'
        ]);

        return new AttendanceRecordResource($attendanceRecord);
    }

    /**
     * 勤怠を更新する
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int Attendance $attendanceRecord
     * @return AttendanceRecordResource
     */
    public function update(UpdateAttendanceRecordRequest $request, Attendance $attendanceRecord): AttendanceRecordResource
    {
        $validated = $request->validated();

        $this->authorize('update', $attendanceRecord);
        $attendanceRecord->update($validated);
        $attendanceRecord->load(['user', 'breakTimes']);

        return new AttendanceRecordResource($attendanceRecord);
    }

    /**
     * 勤怠を削除する
     *
     * @param  int  Attendance $attendanceRecord
     * @return \Illuminate\Http\Response
     */
    public function destroy(Attendance $attendanceRecord): \Illuminate\Http\Response
    {
        $this->authorize('delete', $attendanceRecord);

        $attendanceRecord->delete();

        return response('', 204);
    }
}
