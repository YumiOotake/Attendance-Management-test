<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ApplicationResource extends JsonResource
{
    /**
     * 勤怠申請レコードをJSON形式に変換する
     *
     * @param  \Illuminate\Http\Request $request HTTPリクエスト
     * @return array 勤怠申請データの配列
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'requested_clock_in' => $this->requested_clock_in,
            'requested_clock_out' => $this->requested_clock_out,
            'note' => $this->note,
            'status' => $this->status_label,
            'breaks' => ApplicationBreakResource::collection($this->whenLoaded('requestBreakTimes')),
        ];
    }
}
