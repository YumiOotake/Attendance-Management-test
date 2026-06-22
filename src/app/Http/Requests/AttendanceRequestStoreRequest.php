<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class AttendanceRequestStoreRequest extends FormRequest
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
            'requested_clock_in' => 'date_format:H:i|before:requested_clock_out',
            'requested_clock_out' => 'date_format:H:i|after:requested_clock_in',
            'requested_break_start.*' => 'nullable|date_format:H:i',
            'requested_break_end.*' => 'nullable|date_format:H:i',
            'note' => 'required|string|max:255',
        ];
    }

    /**
     * このリクエストに適用するバリデーションルールとメッセージを取得する
     *
     * @param Validator $validator バリデーターインスタンス
     * @return void
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $breakStarts = $this->requested_break_start ?? [];
            $breakEnds = $this->requested_break_end ?? [];

            foreach ($breakStarts as $key => $start) {
                $end = $breakEnds[$key] ?? null;
                $clockIn = $this->requested_clock_in;
                $clockOut = $this->requested_clock_out;

                if (!$start && $end) {
                    $validator->errors()->add('requested_break_start.' . $key, '休憩開始時刻を入力してください');
                }
                if ($start && !$end) {
                    $validator->errors()->add('requested_break_end.' . $key, '休憩終了時刻を入力してください');
                }
                if ($start && $end) {
                    if ($start >= $end) {
                        $validator->errors()->add('requested_break_start.' . $key, '休憩時間が不適切な値です');
                    }
                    if ($clockIn && $start < $clockIn) {
                        $validator->errors()->add('requested_break_start.' . $key, '休憩時間が不適切な値です');
                    }
                    if ($clockOut && $start > $clockOut) {
                        $validator->errors()->add('requested_break_start.' . $key, '休憩時間が不適切な値です');
                    }
                    if ($clockOut && $end > $clockOut) {
                        $validator->errors()->add('requested_break_end.' . $key, '休憩時間もしくは退勤時間が不適切な値です');
                    }
                }
            }
        });
    }

    /**
     * このリクエストに適用するバリデーションメッセージを取得する
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'requested_clock_in.date_format' => '時刻は「09:00」の形式で入力してください',
            'requested_clock_in.before' => '出勤時間もしくは退勤時間が不適切な値です',
            'requested_clock_out.date_format' => '時刻は「09:00」の形式で入力してください',
            'requested_clock_out.after' => '出勤時間もしくは退勤時間が不適切な値です',
            'requested_break_start.*.date_format' => '時刻は「09:00」の形式で入力してください',
            'requested_break_end.*.date_format' => '時刻は「09:00」の形式で入力してください',
            'note.required' => '備考を記入してください',
            'note.string' => '備考は文字列で記入してください',
            'note.max' => '備考は255文字以内で記入してください',
        ];
    }
}
