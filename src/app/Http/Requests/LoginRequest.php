<?php

namespace App\Http\Requests;

use Laravel\Fortify\Http\Requests\LoginRequest as FortifyLoginRequest;

class LoginRequest extends FortifyLoginRequest
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
            'email' => 'required|email',
            'password' => 'required',
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
            'email.required' => 'メールアドレスを入力してください',
            'email.email' => 'メールアドレスはメール形式で入力してください',
            'password.required' => 'パスワードを入力してください',
        ];
    }
}
