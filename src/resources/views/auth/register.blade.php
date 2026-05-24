@extends('layouts.app')
@section('css')
    <link rel="stylesheet" href="{{ asset('css/auth/form.css') }}">
@endsection
@section('content')
    <div class="form__content">
        <div class="form__heading">
            <h1 class="form__heading-title">会員登録</h1>
        </div>
        <form action="{{ route('register') }}" method="POST" class="form">
            @csrf
            <div class="form__group">
                <div class="form__group-title">
                    <label for="name" class="form__label">名前</label>
                </div>
                <div class="form__group-content">
                    <input type="text" id="name" name="name" value="{{ old('name') }}" class="form__input">
                </div>
                <div class="form__error">
                    @error('name')
                        {{ $message }}
                    @enderror
                </div>
            </div>
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
            <div class="form__group">
                <div class="form__group-title">
                    <label for="confirm-password" class="form__label">パスワード確認</label>
                </div>
                <div class="form__group-content">
                    <input type="password" name="password_confirmation" id="confirm-password" class="form__input">
                </div>
                <div class="form__error">
                    @error('password_confirmation')
                        {{ $message }}
                    @enderror
                </div>
            </div>
            <div class="form__button">
                <button class="form__submit" type="submit">登録する</button>
            </div>
        </form>
        <div class="form__footer">
            <a href="{{ route('login') }}" class="form__footer-link">ログインはこちら</a>
        </div>
    </div>
@endsection
