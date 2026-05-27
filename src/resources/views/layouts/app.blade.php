<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Attendance-Management-Test</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    @yield('css')
</head>

<body>
    <header class="header">
        <div class="header__inner">
            <div class="header__logo">
                <a href="{{ route('attendance.index') }}" class="header__logo-link">
                    <img src="{{ asset('storage/' . 'images/COACHTECHヘッダーロゴ.png') }}" alt="ヘッダーロゴ画像"
                        class="header__logo-img">
                </a>
            </div>
            @if (!Request::is('login') && !Request::is('register'))
                <nav class="header__nav">
                    @if (auth()->check() && auth()->user()->admin_status)
                        <div class="header__nav-item">
                            <a href="{{ route('admin.list') }}" class="header__nav-link">勤怠一覧</a>
                        </div>
                        <div class="header__nav-item">
                            <a href="{{ route('admin.staff.list') }}" class="header__nav-link">スタッフ一覧</a>
                        </div>
                        <div class="header__nav-item">
                            <a href="{{ route('attendance.request.list') }}" class="header__nav-link">申請一覧</a>
                        </div>
                        <form action="{{ route('logout') }}" method="post" class="header__nav-item">
                            @csrf
                            <button type="submit" class="header__nav-button">ログアウト</button>
                        </form>
                    @else
                        <div class="header__nav-item">
                            <a href="{{ route('attendance.index') }}" class="header__nav-link">勤怠</a>
                        </div>
                        <div class="header__nav-item">
                            <a href="{{ route('attendance.list') }}" class="header__nav-link">勤怠一覧</a>
                        </div>
                        <div class="header__nav-item">
                            <a href="{{ route('attendance.request.list') }}" class="header__nav-link">申請</a>
                        </div>
                        <form action="{{ route('logout') }}" method="post" class="header__nav-item">
                            @csrf
                            <button type="submit" class="header__nav-button">ログアウト</button>
                        </form>
                    @endif
                </nav>
            @endif
        </div>
    </header>
    <main>
        @yield('content')
    </main>
</body>

</html>
