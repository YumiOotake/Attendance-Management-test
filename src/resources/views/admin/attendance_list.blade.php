@extends('layouts.app')
@section('css')
    <link rel="stylesheet" href="{{ asset('css/list.css') }}">
@endsection
@section('content')
    <div class="l-inner">
        <h1 class="attendance__title">{{ $targetDate->format('Y/n/j') }}の勤怠</h1>
        <div class="attendance__list">
            <div class="attendance__month-navigation">
                <a href="{{ route('admin.list', ['date' => $dateOffset - 1]) }}" class="attendance__button--previous">
                    <img src="{{ asset('storage/' . 'images/arrow.png') }}" alt="矢印画像" class="attendance__arrow-image">
                    前日
                </a>
                <div class="attendance__date">
                    <img src="{{ asset('storage/' . 'images/calendar.png') }}" alt="カレンダー画像"
                        class="attendance__calendar-image">
                    <p class="attendance__current-date">{{ $targetDate->format('Y/m/d') }}</p>
                </div>
                <a href="{{ route('admin.list', ['date' => $dateOffset + 1]) }}" class="attendance__button--next">
                    翌日
                    <img src="{{ asset('storage/' . 'images/arrow.png') }}" alt="矢印画像"
                        class="attendance__arrow-image attendance__arrow-image--next">
                </a>
            </div>
            <table class="attendance__table">
                <thead class="attendance__thead">
                    <tr class="attendance__row">
                        <th class="attendance__header attendance__header-width">名前</th>
                        <th class="attendance__header">出勤</th>
                        <th class="attendance__header">退勤</th>
                        <th class="attendance__header">休憩</th>
                        <th class="attendance__header">合計</th>
                        <th class="attendance__header">詳細</th>
                    </tr>
                </thead>
                <tbody class="attendance__tbody">
                    @forelse ($attendances as $attendance)
                        <tr class="attendance__row">
                            <td class="attendance__item">{{ $attendance->user->name }}</td>
                            <td class="attendance__item">{{ $attendance->formatted_clock_in }}</td>
                            <td class="attendance__item">{{ $attendance->formatted_clock_out }}</td>
                            <td class="attendance__item">{{ $attendance->total_break_time }}</td>
                            <td class="attendance__item">{{ $attendance->total_work_time }}</td>
                            <td class="attendance__item">
                                <a href="{{ route('admin.detail', $attendance) }}" class="attendance__detail">詳細</a>
                            </td>
                        </tr>
                    @empty
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
