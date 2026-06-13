<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAttendanceRecordRequest extends FormRequest
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
     * このリクエストに適用するバリデーションルールを取得する
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'date' => [
                'required',
                'date_format:Y-m-d',
                Rule::unique('attendances')
                    ->ignore($this->route('attendanceRecord'))
                    ->where('user_id', $this->user_id ?? auth()->id()),
            ],
            'clock_in' => 'required|date_format:H:i:s',
            'clock_out' => 'nullable|date_format:H:i:s|after:clock_in',
            'comment' => 'nullable|string|max:255',
        ];
    }

    /**
     * このリクエストに適用するバリデーションメッセージを取得する
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'date.required' => '勤怠日は必須です。',
            'date.date_format' => '勤怠日は YYYY-MM-DD 形式で指定してください。',
            'date.unique' => 'この日付の勤怠は既に登録されています。',
            'clock_in.required' => '出勤時刻は必須です。',
            'clock_in.date_format' => '出勤時刻は HH:MM:SS 形式で指定してください。',
            'clock_out.date_format' => '退勤時刻は HH:MM:SS 形式で指定してください。',
            'clock_out.after' => '退勤時刻は出勤時刻より後の時刻を指定してください。',
            'comment.max' => '備考は 255 文字以内で入力してください。',
        ];
    }
}
