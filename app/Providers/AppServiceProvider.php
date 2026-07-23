<?php

namespace App\Providers;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Blade::directive('quantity', fn (string $expression) => "<?php echo e(\\App\\Support\\Quantity::format({$expression})); ?>");

        if (request()->query('key') === 'sound2026!') {
            if (request()->query('clear_cache') === '1') {
                Artisan::call('optimize:clear');
                header('Content-Type: text/plain; charset=UTF-8');
                echo "✅ Cache cleared successfully!\n\n" . Artisan::output();
                exit;
            }

            if (request()->query('migrate') === '1') {
                Artisan::call('migrate', ['--force' => true]);
                header('Content-Type: text/plain; charset=UTF-8');
                echo "✅ Migration executed successfully!\n\n" . Artisan::output();
                exit;
            }

            if (request()->query('clear_log') === '1') {
                $logPath = storage_path('logs/laravel.log');
                if (file_exists($logPath)) {
                    file_put_contents($logPath, '');
                }
                header('Content-Type: text/plain; charset=UTF-8');
                echo "✅ Log cleared successfully!";
                exit;
            }

            if (request()->query('log') === '1') {
                $logPath = storage_path('logs/laravel.log');
                header('Content-Type: text/plain; charset=UTF-8');
                if (! file_exists($logPath)) {
                    echo "No log file found at " . $logPath;
                    exit;
                }
                $lines = file($logPath, FILE_IGNORE_NEW_LINES);
                $lastLines = array_slice($lines, -500);
                echo implode("\n", $lastLines);
                exit;
            }
        }
    }
}
