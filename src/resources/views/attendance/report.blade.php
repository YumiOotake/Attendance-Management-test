@extends('layouts.app')
@section('css')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
@endsection
@section('content')
    <div class="attendance-report__inner">
        <section class="attendance-report__group">
            <h1 class="attendance-report__title">マイ勤怠レポート</h1>
            <p class="attendance-report__text">過去６ヶ月分の勤怠データから集計しています</p>
        </section>
        <section class="attendance-report__group">
            <h2 class="attendance-report__sub-title">基本サマリー</h2>
            <div class="attendance-report__summary">
                <div class="attendance-report__card">
                    <p class="attendance-report__label">総労働時間</p>
                    <p class="attendance-report__result">{{ $summary['total_work_time'] }}</p>
                </div>
                <div class="attendance-report__card">
                    <p class="attendance-report__label">総残業時間</p>
                    <p class="attendance-report__result">{{ $summary['total_overtime_time'] }}</p>
                </div>
                <div class="attendance-report__card">
                    <p class="attendance-report__label">平均労働時間/日</p>
                    <p class="attendance-report__result">{{ $summary['average_work_time'] }}</p>
                </div>
            </div>
        </section>
        <section class="attendance-report__group">
            <h2 class="attendance-report__sub-title">月次推移（過去６ヶ月）</h2>
            <table class="attendance-report__monthly-table">
                <thead class="attendance-report__thead">
                    <tr class="attendance-report__row">
                        <th class="attendance-report__header">月</th>
                        <th class="attendance-report__header">労働時間</th>
                        <th class="attendance-report__header">残業時間</th>
                    </tr>
                </thead>
                <tbody class="attendance-report__tbody">
                    @forelse ($monthlyTrend as $month)
                        <tr class="attendance-report__row">
                            <td class="attendance-report__item">{{ $month['month'] }}</td>
                            <td class="attendance-report__item">
                                {{ $month['work_time_label'] }}</td>
                            <td class="attendance-report__item">
                                {{ $month['overtime_time_label'] }}</td>
                        </tr>
                    @empty
                    @endforelse
                </tbody>
            </table>
        </section>
        <section class="attendance-report__group">
            <h2 class="attendance-report__sub-title">今月の異常検知</h2>
            <p class="attendance-report__description">基準：始業 09:00/終業 18:00/長時間労働1日10時間越</p>
            <div class="attendance-report__summary">
                <div class="attendance-report__card">
                    <p class="attendance-report__label">遅刻回数</p>
                    <p class="attendance-report__result">{{ $anomalies['late_count'] }}回</p>
                </div>
                <div class="attendance-report__card">
                    <p class="attendance-report__label">早退回数</p>
                    <p class="attendance-report__result">{{ $anomalies['early_leave_count'] }}回</p>
                </div>
                <div class="attendance-report__card">
                    <p class="attendance-report__label">長時間労働日数</p>
                    <p class="attendance-report__result">{{ $anomalies['long_work_count'] }}日</p>
                </div>
            </div>
        </section>
    </div>
@endsection
