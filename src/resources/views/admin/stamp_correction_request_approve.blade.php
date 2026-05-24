@extends('layouts.app')
@section('css')
    <link rel="stylesheet" href="{{ asset('css/request.css') }}">
@endsection
@section('content')
    <div class="request-form__wrapper">
        <div class="attendance__inner">
            <h1 class="attendance__title">勤怠詳細</h1>

            <dl class="request-form__detail">
                <div class="request-form__group">
                    <dt class="request-form__label">名前</dt>
                    <dd class="request-form__value">{{ $attendanceRequest->attendance->user->name }}</dd>
                </div>
                <div class="request-form__group">
                    <dt class="request-form__label">日付</dt>
                    <dd class="request-form__value">{{ $attendanceRequest->attendance->date->format('Y年') }}</dd>
                    <dd class="request-form__value">{{ $attendanceRequest->attendance->date->format('n月j日') }}</dd>
                </div>
                <div class="request-form__group">
                    <dt class="request-form__label">出勤・退勤</dt>
                    <dd class="request-form__value">{{ $attendanceRequest->formatted_clock_in }}</dd>
                    <span class="request-form__span">〜</span>
                    <dd class="request-form__value">{{ $attendanceRequest->formatted_clock_out }}</dd>
                </div>
                @foreach ($requestBreaks as $key => $requestBreak)
                    <div class="request-form__group">
                        <dt class="request-form__label">休憩{{ $key === 0 ? '' : $key + 1 }}</dt>
                        <dd class="request-form__value">{{ $requestBreak?->formatted_break_start }}</dd>
                        <span class="request-form__span">〜</span>
                        <dd class="request-form__value">{{ $requestBreak?->formatted_break_end }}</dd>
                    </div>
                @endforeach
                <div class="request-form__group">
                    <dt class="request-form__label">備考</dt>
                    <dd class="request-form__value">{{ $attendanceRequest->note }}</dd>
                </div>
            </dl>
            @if ($isPending)
                <form action="{{ route('admin.approve', $attendanceRequest) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="request-form__button">
                        <button class="request-form__submit">承認</button>
                    </div>
                </form>
            @else
                <div class="request-form__button">
                    <button class="request-form__disabled">承認済み</button>
                </div>
            @endif
        </div>
    </div>
@endsection
