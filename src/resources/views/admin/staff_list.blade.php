@extends('layouts.app')
@section('css')
    <link rel="stylesheet" href="{{ asset('css/list.css') }}">
@endsection
@section('content')
    <div class="l-inner">
        <h1 class="attendance__title">スタッフ一覧</h1>
        <div class="attendance__list">
            <table class="attendance__table">
                <thead class="attendance__thead">
                    <tr class="attendance__row">
                        <th class="attendance__header">名前</th>
                        <th class="attendance__header">メールアドレス</th>
                        <th class="attendance__header">月次勤怠</th>
                    </tr>
                </thead>
                <tbody class="attendance__tbody">
                    @forelse ($users as $user)
                        <tr class="attendance__row">
                            <td class="attendance__item">{{ $user->name }}</td>
                            <td class="attendance__item">{{ $user->email }}</td>
                            <td class="attendance__item">
                                <a href="{{ route('admin.attendance.staff', $user) }}" class="attendance__detail">詳細</a>
                            </td>
                        </tr>
                    @empty
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
