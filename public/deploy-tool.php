<?php

declare(strict_types=1);

session_start();

const DEPLOY_TOKEN_HASH = '7b32248c2a04d1eabc0f84c42a76ab2d0b547b7dc05dd1c357dd94feb777e2b2';
const SESSION_LIFETIME = 900;

header('X-Robots-Tag: noindex, nofollow', true);
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header("Content-Security-Policy: default-src 'self'; style-src 'unsafe-inline'; form-action 'self'; frame-ancestors 'none'");

$root = dirname(__DIR__);
$message = null;
$output = null;
$error = null;

function authenticated(): bool
{
    return isset($_SESSION['deploy_authenticated_at'])
        && (time() - (int) $_SESSION['deploy_authenticated_at']) < SESSION_LIFETIME;
}

function csrfToken(): string
{
    if (! isset($_SESSION['deploy_csrf'])) {
        $_SESSION['deploy_csrf'] = bin2hex(random_bytes(24));
    }

    return $_SESSION['deploy_csrf'];
}

function validCsrf(): bool
{
    return isset($_POST['_token'], $_SESSION['deploy_csrf'])
        && hash_equals($_SESSION['deploy_csrf'], (string) $_POST['_token']);
}

function tailLog(string $path, int $maxBytes = 180000, int $maxLines = 250): string
{
    if (! is_file($path)) {
        return "ยังไม่พบไฟล์ {$path}";
    }

    $size = filesize($path) ?: 0;
    $handle = fopen($path, 'rb');
    if ($handle === false) {
        return "ไม่สามารถเปิดอ่าน {$path}";
    }
    if ($size > $maxBytes) {
        fseek($handle, -$maxBytes, SEEK_END);
        fgets($handle);
    }
    $contents = stream_get_contents($handle) ?: '';
    fclose($handle);
    $lines = preg_split('/\R/', $contents) ?: [];

    return implode(PHP_EOL, array_slice($lines, -$maxLines));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'login') {
    $token = (string) ($_POST['deploy_token'] ?? '');
    if (hash_equals(DEPLOY_TOKEN_HASH, hash('sha256', $token))) {
        session_regenerate_id(true);
        $_SESSION['deploy_authenticated_at'] = time();
        $_SESSION['deploy_csrf'] = bin2hex(random_bytes(24));
        header('Location: '.$_SERVER['PHP_SELF']);
        exit;
    }
    usleep(700000);
    $error = 'รหัสเครื่องมือไม่ถูกต้อง';
}

if (authenticated() && $_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') !== 'login') {
    if (! validCsrf()) {
        http_response_code(419);
        $error = 'คำขอหมดอายุ กรุณารีเฟรชหน้าแล้วลองใหม่';
    } elseif (($_POST['action'] ?? '') === 'run') {
        try {
            chdir($root);
            $autoload = $root.'/vendor/autoload.php';
            if (! is_file($autoload)) {
                throw new RuntimeException("ไม่พบ {$autoload} กรุณาวาง vendor ไว้ระดับเดียวกับ public");
            }

            require_once $autoload;
            $app = require $root.'/bootstrap/app.php';
            $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

            $commands = ['optimize:clear', 'config:cache', 'route:cache', 'view:cache'];
            $chunks = [];
            foreach ($commands as $command) {
                $exitCode = Illuminate\Support\Facades\Artisan::call($command);
                $chunks[] = '$ php artisan '.$command.PHP_EOL
                    .Illuminate\Support\Facades\Artisan::output()
                    .'Exit code: '.$exitCode;
                if ($exitCode !== 0) {
                    break;
                }
            }
            $output = implode(PHP_EOL.PHP_EOL, $chunks);
            $message = 'ดำเนินการคำสั่ง Deploy แล้ว กรุณาตรวจผลลัพธ์และ Log ด้านล่าง';
        } catch (Throwable $throwable) {
            $error = get_class($throwable).': '.$throwable->getMessage();
        }
    } elseif (($_POST['action'] ?? '') === 'logout') {
        session_destroy();
        header('Location: '.$_SERVER['PHP_SELF']);
        exit;
    } elseif (($_POST['action'] ?? '') === 'delete') {
        if (@unlink(__FILE__)) {
            echo '<!doctype html><meta charset="utf-8"><title>ลบแล้ว</title><p style="font:18px sans-serif;padding:30px">ลบไฟล์ Deploy Tool ออกจากเซิร์ฟเวอร์แล้ว</p>';
            exit;
        }
        $error = 'ลบไฟล์ไม่ได้ กรุณาลบ public/deploy-tool.php ผ่าน File Manager';
    }
}

$checks = [
    'PHP 8.2 ขึ้นไป' => version_compare(PHP_VERSION, '8.2.0', '>='),
    'vendor/autoload.php' => is_file($root.'/vendor/autoload.php'),
    '.env' => is_file($root.'/.env'),
    'storage เขียนได้' => is_writable($root.'/storage'),
    'bootstrap/cache เขียนได้' => is_writable($root.'/bootstrap/cache'),
    'Frontend build' => is_file($root.'/public/build/manifest.json'),
];
$log = authenticated() ? tailLog($root.'/storage/logs/laravel.log') : '';
?>
<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Simple Stock — Deploy Tool</title>
    <style>
        *{box-sizing:border-box}body{margin:0;background:#f1f5f9;color:#0f172a;font-family:Tahoma,Arial,sans-serif}.wrap{max-width:1050px;margin:45px auto;padding:0 20px}.card{background:#fff;border:1px solid #dbe3ef;border-radius:18px;box-shadow:0 12px 35px #0f172a12;margin-bottom:20px;overflow:hidden}.head{padding:22px 26px;border-bottom:1px solid #e2e8f0}.body{padding:24px 26px}h1,h2{margin:0}p{line-height:1.65}.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(230px,1fr));gap:12px}.check{padding:14px;border-radius:12px;background:#f8fafc;border:1px solid #e2e8f0}.ok{color:#047857}.bad{color:#be123c}.alert{padding:14px 18px;border-radius:12px;margin-bottom:18px}.success{background:#ecfdf5;color:#065f46}.danger{background:#fff1f2;color:#9f1239}input{width:100%;padding:13px;border:1px solid #cbd5e1;border-radius:10px;font-size:16px}.buttons{display:flex;flex-wrap:wrap;gap:10px;margin-top:18px}button{border:0;border-radius:10px;padding:12px 18px;font-size:15px;font-weight:700;cursor:pointer}.primary{background:#2563eb;color:#fff}.secondary{background:#e2e8f0;color:#0f172a}.delete{background:#e11d48;color:#fff}pre{margin:0;max-height:460px;overflow:auto;padding:18px;background:#08111f;color:#dbeafe;border-radius:12px;font:13px/1.6 Consolas,monospace;white-space:pre-wrap;word-break:break-word}.muted{color:#64748b;font-size:14px}
    </style>
</head>
<body><main class="wrap">
    <section class="card"><div class="head"><h1>Simple Stock — Deploy Tool</h1><p class="muted">เครื่องมือชั่วคราวสำหรับ Plesk · PHP <?= htmlspecialchars(PHP_VERSION) ?></p></div><div class="body">
        <?php if ($message): ?><div class="alert success"><?= htmlspecialchars($message) ?></div><?php endif ?>
        <?php if ($error): ?><div class="alert danger"><?= htmlspecialchars($error) ?></div><?php endif ?>
        <?php if (! authenticated()): ?>
            <form method="post"><input type="hidden" name="action" value="login"><label><strong>รหัส Deploy Tool</strong><input type="password" name="deploy_token" required autofocus autocomplete="current-password"></label><div class="buttons"><button class="primary">เข้าสู่เครื่องมือ</button></div></form>
        <?php else: ?>
            <div class="grid"><?php foreach ($checks as $label => $passed): ?><div class="check <?= $passed ? 'ok' : 'bad' ?>"><strong><?= $passed ? '✓' : '✕' ?> <?= htmlspecialchars($label) ?></strong></div><?php endforeach ?></div>
            <p class="muted">ปุ่มด้านล่างรันตามลำดับ: optimize:clear → config:cache → route:cache → view:cache</p>
            <div class="buttons">
                <form method="post"><input type="hidden" name="_token" value="<?= htmlspecialchars(csrfToken()) ?>"><input type="hidden" name="action" value="run"><button class="primary">รันคำสั่ง Deploy</button></form>
                <form method="post"><input type="hidden" name="_token" value="<?= htmlspecialchars(csrfToken()) ?>"><input type="hidden" name="action" value="logout"><button class="secondary">ออกจากระบบ</button></form>
                <form method="post" onsubmit="return confirm('ยืนยันลบ Deploy Tool ออกจากเซิร์ฟเวอร์?')"><input type="hidden" name="_token" value="<?= htmlspecialchars(csrfToken()) ?>"><input type="hidden" name="action" value="delete"><button class="delete">ลบเครื่องมือนี้</button></form>
            </div>
        <?php endif ?>
    </div></section>
    <?php if (authenticated() && $output !== null): ?><section class="card"><div class="head"><h2>ผลลัพธ์คำสั่ง</h2></div><div class="body"><pre><?= htmlspecialchars($output) ?></pre></div></section><?php endif ?>
    <?php if (authenticated()): ?><section class="card"><div class="head"><h2>Laravel Log ล่าสุด</h2><p class="muted">แสดงสูงสุด 250 บรรทัดท้ายจาก storage/logs/laravel.log</p></div><div class="body"><pre><?= htmlspecialchars($log) ?></pre></div></section><?php endif ?>
</main></body></html>
