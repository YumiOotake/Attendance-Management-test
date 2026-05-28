@extends('layouts.app')
@section('css')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
@endsection
@section('content')
    <div class="attendance">
        <div class="attendance__inner">
            <div class="attendance__status">
                <p class="attendance__badge">
                    @switch($attendance->status ?? 'none')
                        @case('working')
                            出勤中
                        @break

                        @case('break')
                            休憩中
                        @break

                        @case('done')
                            退勤済
                        @break

                        @default
                            勤務外
                    @endswitch
                </p>
            </div>
            <div class="attendance__clock">
                <p class="attendance__date">{{ $now->isoFormat('YYYY年M月D日(ddd)') }}</p>
                <p class="attendance__time">{{ $now->format('H:i') }}</p>
            </div>
            <div class="attendance__actions">

                @switch($attendance->status ?? 'none')
                    @case('working')
                        <form action="{{ route('attendance.clock-out', $attendance) }}" method="post" class="attendance__form">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="attendance__button">退勤</button>
                        </form>
                        <form action="{{ route('attendance.break-start', $attendance) }}" method="post" class="attendance__form">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="attendance__button attendance__button--break">休憩入</button>
                        </form>
                    @break

                    @case('break')
                        <form action="{{ route('attendance.break-end', $attendance) }}" method="post" class="attendance__form">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="attendance__button attendance__button--break">休憩戻</button>
                        </form>
                    @break

                    @case('done')
                        <p class="attendance__message">お疲れ様でした。</p>
                    @break

                    @default
                        <form action="{{ route('attendance.clock-in', $attendance) }}" method="post" class="attendance__form">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="attendance__button">出勤</button>
                        </form>
                @endswitch
            </div>
        </div>
    </div>
@endsection
