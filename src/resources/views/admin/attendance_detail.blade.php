@extends('layouts.app')
@section('css')
    <link rel="stylesheet" href="{{ asset('css/detail.css') }}">
@endsection
@section('content')
    <div class="l-inner">
        <h1 class="attendance__title">勤怠詳細</h1>
        <div class="attendance__list">
            @if ($isPending)
                <dl class="request-form">
                    <div class="request-form__group">
                        <dt class="request-form__label">名前</dt>
                        <div class="request-form__field">
                            <dd class="request-form__value">{{ $attendanceRequest->attendance->user->name }}</dd>
                        </div>
                    </div>
                    <div class="request-form__group">
                        <dt class="request-form__label">日付</dt>
                        <div class="request-form__field request-form__field--time">
                            <dd class="request-form__value">{{ $attendanceRequest->attendance->date->format('Y年') }}</dd>
                            <dd class="request-form__value">{{ $attendanceRequest->attendance->date->format('n月j日') }}</dd>
                        </div>
                    </div>
                    <div class="request-form__group">
                        <dt class="request-form__label">出勤・退勤</dt>
                        <div class="request-form__field request-form__field--time">
                            <dd class="request-form__value">{{ $attendanceRequest->formatted_clock_in }}</dd>
                            <span class="request-form__span">〜</span>
                            <dd class="request-form__value">{{ $attendanceRequest->formatted_clock_out }}</dd>
                        </div>
                    </div>
                    @foreach ($requestBreaks as $key => $requestBreak)
                        <div class="request-form__group">
                            <dt class="request-form__label">休憩{{ $key === 0 ? '' : $key + 1 }}</dt>
                            <div class="request-form__field request-form__field--time">
                                <dd class="request-form__value">{{ $requestBreak?->formatted_break_start }}</dd>
                                <span class="request-form__span">〜</span>
                                <dd class="request-form__value">{{ $requestBreak?->formatted_break_end }}</dd>
                            </div>
                        </div>
                    @endforeach
                    <div class="request-form__group">
                        <dt class="request-form__label">備考</dt>
                        <div class="request-form__field">
                            <dd class="request-form__value">{{ $attendanceRequest->note }}</dd>
                        </div>
                    </div>
                </dl>
                <p class="request-form__message">*承認待ちのため修正はできません。</p>
            @else
                <form
                    action="{{ route('admin.request', $attendance->id ?? 0) }}?date={{ $attendanceDate }}&user_id={{ $attendanceUser->id }}&from={{ $from }}&month={{ $monthOffset }}"
                    method="POST" class="request-form" id="attendance-request-form">
                    @csrf
                    @method('PATCH')
                    <div class="request-form__group">
                        <label class="request-form__label">名前</label>
                        <div class="request-form__field request-form__field--text">
                            <p class="request-form__value">{{ $attendanceUser->name }}</p>
                        </div>
                    </div>
                    <div class="request-form__group">
                        <label class="request-form__label">日付</label>
                        <div class="request-form__field  request-form__field--text request-form__field--time">
                            <p class="request-form__value">{{ $attendance->date->format('Y年') }}</p>
                            <p class="request-form__value">{{ $attendance->date->format('n月j日') }}</p>
                        </div>
                    </div>
                    <div class="request-form__group">
                        <label for="requested_clock_in" class="request-form__label">出勤・退勤</label>
                        <div class="request-form__field request-form__field--time">
                            <div class="request-form__clock-in">
                                <input type="text" id="requested_clock_in" name="requested_clock_in"
                                    value="{{ old('requested_clock_in', $attendance->formatted_clock_in) }}"
                                    class="request-form__input">
                                <div class="request-form__error">
                                    @error('requested_clock_in')
                                        {{ $message }}
                                    @enderror
                                </div>
                            </div>
                            <span class="request-form__span">〜</span>
                            <div class="request-form__clock-out">
                                <input type="text" id="requested_clock_out" name="requested_clock_out"
                                    value="{{ old('requested_clock_out', $attendance->formatted_clock_out) }}"
                                    class="request-form__input">
                                <div class="request-form__error">
                                    @error('requested_clock_out')
                                        {{ $message }}
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    @foreach ($breaks as $key => $break)
                        <div class="request-form__group">
                            <label for="requested_break_start_{{ $key }}"
                                class="request-form__label">休憩{{ $key === 0 ? '' : $key + 1 }}</label>
                            <div class="request-form__field request-form__field--time">
                                <div class="request-form__break_start">
                                    <input type="text" id="requested_break_start_{{ $key }}"
                                        name="requested_break_start[]"
                                        value="{{ old('requested_break_start.' . $key, $break?->formatted_break_start) }}"
                                        class="request-form__input">
                                    <div class="request-form__error">
                                        @error("requested_break_start.$key")
                                            {{ $message }}
                                        @enderror
                                    </div>
                                </div>
                                <span class="request-form__span">〜</span>
                                <div class="request-form__break_end">
                                    <input type="text" name="requested_break_end[]"
                                        value="{{ old('requested_break_end.' . $key, $break?->formatted_break_end) }}"
                                        class="request-form__input">
                                    <div class="request-form__error">
                                        @error("requested_break_end.$key")
                                            {{ $message }}
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <div class="request-form__group">
                        <label for="requested_break_start"
                            class="request-form__label">休憩{{ count($breaks) === 1 ? '2' : count($breaks) + 1 }}</label>
                        <div class="request-form__field request-form__field--time">
                            <div class="request-form__break_start">
                                <input type="text" id="requested_break_start[]" name="requested_break_start[]"
                                    value="{{ old('requested_break_start.' . count($breaks)) }}"
                                    class="request-form__input">
                                <div class="request-form__error">
                                    @error('requested_break_start')
                                        {{ $message }}
                                    @enderror
                                </div>
                            </div>
                            <span class="request-form__span">〜</span>
                            <div class="request-form__break_end">
                                <input type="text" name="requested_break_end[]"
                                    value="{{ old('requested_break_end.' . count($breaks)) }}" class="request-form__input">
                                <div class="request-form__error">
                                    @error('requested_break_start')
                                        {{ $message }}
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="request-form__group">
                        <label for="note" class="request-form__label">備考</label>
                        <div class="request-form__field">
                            <div class="request-form__content">
                                <textarea class="request-form__input request-form__textarea" name="note" id="note" cols="30"
                                    rows="10">{{ old('note') }}</textarea>
                                <div class="request-form__error">
                                    @error('note')
                                        {{ $message }}
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                <div class="request-form__button">
                    <button class="request-form__submit" type="submit">修正</button>
                </div>
            @endif
        </div>
    </div>
@endsection
