<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Dev Tools — WIP Stock</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { min-height: 100vh; background: #0f172a; color: #e2e8f0; font: 15px/1.5 'IBM Plex Sans Thai', system-ui, sans-serif; }
        .container { max-width: 720px; margin: 0 auto; padding: 40px 20px; }
        h1 { font-size: 28px; color: #38bdf8; margin-bottom: 6px; }
        .subtitle { color: #64748b; margin-bottom: 30px; }
        .result-box { background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 16px 20px; margin-bottom: 24px; white-space: pre-wrap; font-family: monospace; font-size: 14px; color: #a7f3d0; line-height: 1.6; }
        .grid { display: grid; gap: 12px; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); margin-bottom: 24px; }
        .btn { display: flex; align-items: center; justify-content: center; gap: 8px; border: 0; border-radius: 12px; padding: 14px 20px; font: inherit; font-weight: 700; font-size: 15px; cursor: pointer; transition: all .15s; }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgb(0 0 0 / .3); }
        .btn-blue { background: #2563eb; color: #fff; }
        .btn-green { background: #059669; color: #fff; }
        .btn-amber { background: #d97706; color: #fff; }
        .btn-red { background: #dc2626; color: #fff; }
        .custom-section { background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 20px; margin-top: 24px; }
        .custom-section h3 { color: #94a3b8; font-size: 13px; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 12px; }
        .input-row { display: flex; gap: 10px; }
        input[type="text"] { flex: 1; background: #0f172a; border: 1px solid #475569; border-radius: 8px; padding: 10px 14px; color: #e2e8f0; font: inherit; }
        input[type="text"]:focus { outline: none; border-color: #2563eb; }
        .warn { margin-top: 32px; padding: 14px 18px; border: 1px solid #92400e; border-radius: 12px; background: #451a03; color: #fbbf24; font-size: 13px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🛠 Dev Tools</h1>
        <p class="subtitle">WIP Stock — Artisan Commands via Web</p>

        @if(session('result'))
        <div class="result-box">{{ session('result') }}</div>
        @endif

        <div class="grid">
            <form method="post" action="/dev-tools/migrate?key={{ $key }}">@csrf
                <button class="btn btn-blue" style="width:100%">🗄 Migrate</button>
            </form>
            <form method="post" action="/dev-tools/optimize?key={{ $key }}">@csrf
                <button class="btn btn-green" style="width:100%">⚡ Optimize</button>
            </form>
            <form method="post" action="/dev-tools/seed?key={{ $key }}">@csrf
                <button class="btn btn-amber" style="width:100%">🌱 Seed</button>
            </form>
            <form method="post" action="/dev-tools/migrate-fresh?key={{ $key }}" onsubmit="return confirm('⚠️ จะลบข้อมูลทั้งหมด! ยืนยัน?')">@csrf
                <button class="btn btn-red" style="width:100%">💥 Migrate Fresh</button>
            </form>
        </div>

        <div class="custom-section">
            <h3>Custom Artisan Command</h3>
            <form method="post" action="/dev-tools/custom?key={{ $key }}">@csrf
                <div class="input-row">
                    <input type="text" name="command" placeholder="e.g. route:list, config:cache, queue:work" required>
                    <button class="btn btn-blue">Run</button>
                </div>
            </form>
        </div>

        <div class="warn">
            ⚠️ หน้านี้ใช้สำหรับ Developer เท่านั้น อย่าเผยแพร่ URL และ Key ให้ผู้อื่น
        </div>
    </div>
</body>
</html>
