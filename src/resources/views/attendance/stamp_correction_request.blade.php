@extends('layouts.app')
@section('css')
    <link rel="stylesheet" href="{{ asset('css/list.css') }}">
@endsection
@section('content')
    <div class="attendance__list">
        <div class="attendance__inner">
            <h1 class="attendance__title">申請一覧</h1>

            <div class="attendance__navigation">
                <a href="{{ route('attendance.request.list', ['tab' => 'pending']) }}"
                    class="attendance__tab {{ request('tab') === 'pending' ? 'attendance__tab--active' : '' }}">承認待ち</a>
                <a href="{{ route('attendance.request.list', ['tab' => 'approved']) }}"
                    class="attendance__tab {{ request('tab') === 'approved' ? 'attendance__tab--active' : '' }}">承認済み</a>
            </div>
            <table class="attendance__table">
                <thead class="attendance__thead">
                    <tr class="attendance__row">
                        <th class="attendance__header">状態</th>
                        <th class="attendance__header">名前</th>
                        <th class="attendance__header">対象日時</th>
                        <th class="attendance__header">申請理由</th>
                        <th class="attendance__header">申請日時</th>
                        <th class="attendance__header">詳細</th>
                    </tr>
                </thead>
                <tbody class="attendance__tbody">
                    @forelse ($attendanceRequests as $attendanceRequest)
                        <tr class="attendance__row">
                            <td class="attendance__item">{{ $attendanceRequest->status_label }}</td>
                            <td class="attendance__item">{{ $attendanceRequest->attendance->user->name }}</td>
                            <td class="attendance__item">{{ $attendanceRequest->attendance->date->format('Y/m/d') }}</td>
                            <td class="attendance__item">{{ $attendanceRequest->note }}</td>
                            <td class="attendance__item">{{ $attendanceRequest->created_at->format('Y/m/d') }}</td>
                            <td class="attendance__item">
                                <a href="{{ route('attendance.show', $attendanceRequest->attendance) }}"
                                    class="attendance__detail">詳細</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="attendance__empty">
                                申請記録がありません。
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
