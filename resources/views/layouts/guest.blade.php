<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'Larkin HRMS') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            :root { color-scheme: light; }
            body { margin: 0; font-family: Figtree, sans-serif; background: #f5f7fa; color: #182433; }
            .login-shell { min-height: 100vh; display: grid; grid-template-columns: minmax(0, 1.05fr) minmax(420px, .95fr); }
            .brand-panel { position: relative; display: flex; flex-direction: column; justify-content: space-between; overflow: hidden; padding: clamp(36px, 6vw, 84px); color: white; background: linear-gradient(180deg, #111827 0%, #0f172a 100%); }
            .brand-panel:before, .brand-panel:after { content: ''; position: absolute; border: 1px solid rgba(255,255,255,.12); border-radius: 50%; pointer-events: none; }
            .brand-panel:before { width: 520px; height: 520px; right: -210px; top: 12%; }
            .brand-panel:after { width: 360px; height: 360px; right: -130px; top: 25%; }
            .brand-logo { position: relative; z-index: 1; width: min(260px, 68%); height: auto; object-fit: contain; object-position: left center; }
            .brand-copy { position: relative; z-index: 1; max-width: 520px; margin: auto 0; padding: 70px 0; }
            .brand-kicker { margin: 0 0 18px; color: #b9e3f2; font-size: 12px; font-weight: 700; letter-spacing: .18em; text-transform: uppercase; }
            .brand-copy h1 { margin: 0; max-width: 490px; font-size: clamp(38px, 5vw, 60px); line-height: 1.06; letter-spacing: -.04em; font-weight: 600; }
            .brand-copy p { max-width: 430px; margin: 22px 0 0; color: rgba(255,255,255,.78); font-size: 16px; line-height: 1.7; }
            .brand-footer { position: relative; z-index: 1; color: rgba(255,255,255,.64); font-size: 12px; }
            .form-panel { display: flex; align-items: center; justify-content: center; padding: 44px 28px; }
            .login-card { width: 100%; max-width: 410px; }
            .mobile-logo { display: none; width: 170px; height: auto; margin: 0 auto 36px; }
            @media (max-width: 760px) { .login-shell { display: block; min-height: 100vh; } .brand-panel { display: none; } .form-panel { min-height: 100vh; padding: 36px 24px; } .mobile-logo { display: block; } }
        </style>
    </head>
    <body>
        <main class="login-shell">
            <section class="brand-panel" aria-label="Larkin">
                <img class="brand-logo" src="{{ asset('images/larkin-nobg1.png') }}" alt="Larkin">
                <div class="brand-copy">
                    <p class="brand-kicker">People. Progress. Possibility.</p>
                    <h1>Your workday, made simpler.</h1>
                    <p>Sign in to access your Larkin HR portal and the tools you need to do your best work.</p>
                </div>
                <div class="brand-footer">&copy; {{ date('Y') }} Larkin. All rights reserved.</div>
            </section>
            <section class="form-panel">
                <div class="login-card">
                    <img class="mobile-logo" src="{{ asset('images/larkin-nobg1.png') }}" alt="Larkin">
                    {{ $slot }}
                </div>
            </section>
        </main>
    </body>
</html>
