<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#fffaf0">
    <title>Activity Planner Already Open | KidLink</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 24px; background: #fffaf0; color: #182438; font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
        .panel { width: min(100%, 520px); padding: 42px 34px; background: #fff; border: 1px solid #f1e7cc; border-radius: 24px; box-shadow: 0 18px 55px rgba(24,36,56,.08); text-align: center; }
        .symbol { width: 76px; height: 76px; margin: 0 auto 22px; display: grid; place-items: center; border-radius: 22px; background: #fff3d6; color: #b7791f; }
        h1 { margin: 0 0 12px; font-size: clamp(25px, 5vw, 32px); line-height: 1.2; }
        h2 { margin: 0 0 14px; font-size: 17px; font-weight: 650; }
        p { margin: 0 auto 24px; max-width: 410px; color: #657080; line-height: 1.7; font-size: 14px; }
        .status { display: inline-flex; align-items: center; gap: 7px; margin-bottom: 26px; padding: 7px 12px; border-radius: 999px; background: #fff3d6; color: #9a6415; font-size: 12px; font-weight: 700; }
        .actions { display: flex; flex-wrap: wrap; justify-content: center; gap: 10px; }
        a { display: inline-flex; align-items: center; justify-content: center; min-height: 44px; padding: 0 20px; border-radius: 11px; background: #ff8555; color: #fff; text-decoration: none; font-size: 14px; font-weight: 700; }
        a.secondary { background: #fff; border: 1px solid #e8dfcc; color: #344054; }
        .brand { margin-top: 30px; color: #98a0aa; font-size: 11px; letter-spacing: .12em; font-weight: 700; text-transform: uppercase; }
        @media (max-width: 420px) { .panel { padding: 32px 22px; } .actions a { width: 100%; } }
    </style>
</head>
<body>
    <main class="panel" role="alert" aria-live="assertive">
        <div class="symbol" aria-hidden="true">
            <svg width="38" height="38" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                <rect x="2.5" y="4" width="13" height="15" rx="2"></rect>
                <path d="M7 16h4M8 7h2"></path>
                <rect x="17" y="8" width="4.5" height="11" rx="1.2"></rect>
                <path d="M18.5 16.5h1.5"></path>
            </svg>
        </div>
        <h1>Activity Planner Already Open</h1>
        <h2>Another device is using this page</h2>
        <p>Your KidLink account already has the Activity Planner open on another device. To prevent conflicting changes, only one device can use the planner at a time.</p>
        <div class="status"><span aria-hidden="true">●</span> Concurrent access detected</div>
        <div class="actions">
            <a href="{{ $retryUrl ?? route('teacher.activities.index') }}">Try again</a>
            <a class="secondary" href="{{ route('teacher.dashboard') }}">Back to dashboard</a>
        </div>
        <div class="brand">KidLink · Account session protection</div>
    </main>
</body>
</html>
