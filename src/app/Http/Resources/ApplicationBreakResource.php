<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ApplicationBreakResource extends JsonResource
{
    /**
     * 休憩申請レコードをJSON形式に変換する
     *
     * @param  \Illuminate\Http\Request $request HTTPリクエスト
     * @return array 休憩申請データの配列
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'requested_break_start' => $this->requested_break_start,
            'requested_break_end' => $this->requested_break_end,
        ];
    }
}
