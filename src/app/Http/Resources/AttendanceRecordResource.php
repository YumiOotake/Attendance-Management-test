<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceRecordResource extends JsonResource
{
    /**
     * 勤怠レコードをJSON形式に変換する
     *
     * @param  \Illuminate\Http\Request  $request HTTPリクエスト
     * @return array 勤怠データの配列
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'user_id' =>  $this->when(
                $request->routeIs('attendance-records.index'),
                $this->user?->id
            ),
            'user_name' => $this->when(
                $request->routeIs('attendance-records.index'),
                $this->user?->name
            ),
            'user' => $this->when(
                $request->routeIs('attendance-records.show'),
                new UserResource($this->whenLoaded('user'))
            ),
            'date' => $this->date?->format('Y-m-d'),
            'clock_in' => $this->clock_in ?? null,
            'clock_out' => $this->clock_out ?? null,
            'total_time' => $this->total_time,
            'total_break_time' => $this->total_break_time,
            'breaks' => $this->when(
                $request->routeIs('attendance-records.show'),
                AttendanceBreakResource::collection($this->whenLoaded('breakTimes'))
            ),
            'applications' => $this->when(
                $request->routeIs('attendance-records.show'),
                ApplicationResource::collection($this->whenLoaded('attendanceRequests'))
            ),
            'comment' => $this->comment,
        ];
    }
}
