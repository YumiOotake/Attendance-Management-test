@extends('layouts.app')
@section('css')
    <link rel="stylesheet" href="{{ asset('css/list.css') }}">
@endsection
@section('content')
    <div class="attendance__list">
        <div class="attendance__inner">
            <h1 class="attendance__title">勤怠一覧</h1>

            <div class="attendance__month-navigation">
                <a href="{{ route('attendance.list', ['month' => $monthOffset - 1]) }}" class="attendance__button--previous">
                    <img src="{{ asset('storage/' . 'images/arrow.png') }}" alt="矢印画像" class="attendance__arrow-image">
                    前月
                </a>
                <div class="attendance__date">
                    <img src="{{ asset('storage/' . 'images/calendar.png') }}" alt="カレンダー画像"
                        class="attendance__calendar-image">
                    <p class="attendance__current-date">{{ $targetMonth->format('Y/m') }}</p>
                </div>
                <a href="{{ route('attendance.list', ['month' => $monthOffset + 1]) }}" class="attendance__button--next">
                    翌月
                    <img src="{{ asset('storage/' . 'images/arrow.png') }}" alt="矢印画像"
                        class="attendance__arrow-image attendance__arrow-image--next">
                </a>
            </div>
            <table class="attendance__table">
                <thead class="attendance__thead">
                    <tr class="attendance__row">
                        <th class="attendance__header">日付</th>
                        <th class="attendance__header">出勤</th>
                        <th class="attendance__header">退勤</th>
                        <th class="attendance__header">休憩</th>
                        <th class="attendance__header">合計</th>
                        <th class="attendance__header">詳細</th>
                    </tr>
                </thead>
                <tbody class="attendance__tbody">
                    @forelse ($dateLists as $dateList)
                        @php
                            $attendance = $attendanceByDate[$dateList->format('Y-m-d')] ?? null;
                        @endphp
                        <tr class="attendance__row">
                            <td class="attendance__item">{{ $dateList->isoFormat('MM/DD(ddd)') }}</td>
                            <td class="attendance__item">
                                {{ $attendance->formatted_clock_in ?? '' }}</td>
                            <td class="attendance__item">
                                {{ $attendance->formatted_clock_out ?? '' }}</td>
                            <td class="attendance__item">
                                {{ $attendance->total_break_time ?? '' }}</td>
                            <td class="attendance__item">
                                {{ $attendance->total_work_time ?? '' }}</td>
                            <td class="attendance__item">
                                <a href="{{ route('attendance.show', $attendance->id ?? 0) }}?date={{ $dateList->format('Y-m-d') }}"
                                    class="attendance__detail">詳細</a>
                            </td>
                        </tr>
                    @empty
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
