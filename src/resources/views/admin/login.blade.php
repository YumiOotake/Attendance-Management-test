@extends('layouts.app')
@section('css')
    <link rel="stylesheet" href="{{ asset('css/auth/form.css') }}">
@endsection
@section('content')
    <div class="form__content">
        <div class="form__heading">
            <h1 class="form__heading-title">管理者ログイン</h1>
        </div>
        <form action="{{ route('login') }}" method="POST" class="form">
            @csrf
            <input type="hidden" name="login_type" value="admin">
            <div class="form__group">
                <div class="form__group-title">
                    <label for="email" class="form__label">メールアドレス</label>
                </div>
                <div class="form__group-content">
                    <input type="text" id="email" name="email" value="{{ old('email') }}" class="form__input">
                </div>
                <div class="form__error">
                    @error('email')
                        {{ $message }}
                    @enderror
                </div>
            </div>
            <div class="form__group">
                <div class="form__group-title">
                    <label for="password" class="form__label">パスワード</label>
                </div>
                <div class="form__group-content">
                    <input type="password" id="password" name="password" class="form__input">
                </div>
                <div class="form__error">
                    @error('password')
                        {{ $message }}
                    @enderror
                </div>
            </div>
            <div class="form__button">
                <button class="form__submit" type="submit">管理者ログインする</button>
            </div>
        </form>
    </div>
@endsection
