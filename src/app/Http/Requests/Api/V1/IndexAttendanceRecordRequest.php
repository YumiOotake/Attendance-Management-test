<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class IndexAttendanceRecordRequest extends FormRequest
{
    /**
     * このリクエストを実行する権限があるか判定する
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * このリクエストに適用するバリデーションメッセージを取得する
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'user_id' => 'nullable|integer',
            'date' => 'nullable|date_format:Y-m-d',
            'month' => 'nullable|string|date_format:Y-m',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
        ];
    }
}
