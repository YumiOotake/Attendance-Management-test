<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceBreakResource extends JsonResource
{
    /**
     * 休憩レコードをJSON形式に変換する
     *
     * @param  \Illuminate\Http\Request $request HTTPリクエスト
     * @return array 休憩データの配列
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'break_in' => $this->break_start,
            'break_out' => $this->break_end,
        ];
    }
}
