<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MCP Welcome</title>
    <style>
        body { font-family: system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, Noto Sans, Helvetica, Arial, "Apple Color Emoji", "Segoe UI Emoji"; margin: 0; background: #0f172a; color: #e2e8f0; }
        .container { max-width: 720px; margin: 10vh auto; padding: 24px; background: #111827; border-radius: 12px; border: 1px solid #1f2937; box-shadow: 0 10px 30px rgba(0,0,0,0.35); }
        h1 { margin: 0 0 8px; font-size: 28px; }
        p { margin: 0 0 16px; line-height: 1.6; color: #cbd5e1; }
        code { background: #0b1020; padding: 2px 6px; border-radius: 6px; color: #93c5fd; }
        .muted { color: #94a3b8; font-size: 14px; }
        a { color: #60a5fa; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
    @if (function_exists('app'))
        <meta name="csrf-token" content="{{ csrf_token() }}">
    @endif
</head>
<body>
    <div class="container">
        <h1>Laravel Boost MCP — Hello!</h1>
        <p>
            This page is created to verify MCP access to <code>resources/views/mcp_welcome.blade.php</code>.
        </p>
        <p class="muted">
            App: <strong>{{ config('app.name', 'Artual') }}</strong>
        </p>
        <p>
            To serve this page, add a route in <code>routes/web.php</code> like:
        </p>
        <pre style="white-space: pre-wrap; background:#0b1020; padding:12px; border-radius:8px; border:1px solid #1f2937; color:#e5e7eb;">
Route::get('/mcp-welcome', function () {
    return view('mcp_welcome');
});
        </pre>
        <p>
            Then open <a href="/mcp-welcome">/mcp-welcome</a> in your browser.
        </p>
    </div>
</body>
</html>











