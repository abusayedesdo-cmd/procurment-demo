<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * php artisan server:check
 *
 * One-shot health check to run after every upload to a new server / PHP
 * version. NOTE: it reports on the PHP that runs THIS command (CLI). The
 * website may use a different PHP version in cPanel, so also open the
 * temporary web check described in SERVER-CHECKLIST.md.
 */
class ServerCheck extends Command
{
    protected $signature = 'server:check';
    protected $description = 'Check PHP version, extensions, folder permissions and .env for this server';

    private int $failures = 0;
    private int $warnings = 0;

    public function handle(): int
    {
        $this->line('PHP ' . PHP_VERSION . '  (' . PHP_SAPI . ')');
        $this->line('php.ini: ' . (php_ini_loaded_file() ?: 'none'));
        $this->newLine();

        $this->section('PHP version');
        version_compare(PHP_VERSION, '8.1.0', '>=')
            ? $this->ok('PHP >= 8.1')
            : $this->fail('PHP 8.1+ required, running ' . PHP_VERSION);
        version_compare(PHP_VERSION, '8.2.0', '>=')
            ? $this->ok('PHP >= 8.2 (matches local development)')
            : $this->warn2('Local development uses PHP 8.2; some vendor packages may require it');

        $this->section('Required extensions');
        $required = [
            'zip' => 'Word (.docx) and Excel (.xlsx) files are zip archives',
            'gd' => 'images in documents',
            'mbstring' => 'Bengali / UTF-8 text',
            'xml' => 'PhpWord / PhpSpreadsheet',
            'dom' => 'PhpWord / PhpSpreadsheet',
            'xmlwriter' => 'PhpWord / PhpSpreadsheet',
            'libxml' => 'PhpWord / PhpSpreadsheet',
            'fileinfo' => 'file uploads',
            'iconv' => 'text conversion',
            'json' => 'core',
            'openssl' => 'encryption / mail',
            'pdo_mysql' => 'database',
            'ctype' => 'Laravel',
            'tokenizer' => 'Laravel',
            'curl' => 'HTTP / mail',
        ];
        foreach ($required as $ext => $why) {
            extension_loaded($ext)
                ? $this->ok($ext)
                : $this->fail("{$ext} MISSING - {$why}");
        }
        class_exists(\ZipArchive::class)
            ? $this->ok('ZipArchive class available')
            : $this->warn2('ZipArchive class missing: Word works via the PclZip fallback, but Excel exports will fail until the zip extension is enabled');

        $this->section('Writable folders');
        foreach ([
            'storage',
            'storage/logs',
            'storage/app',
            'storage/app/tmp',
            'storage/framework/cache',
            'storage/framework/sessions',
            'storage/framework/views',
            'bootstrap/cache',
            'public/uploads',
            'public/uploads/pr-attachments',
            'public/uploads/signatures',
        ] as $rel) {
            $path = base_path($rel);
            if (! is_dir($path)) {
                @mkdir($path, 0775, true);
            }
            is_dir($path) && is_writable($path)
                ? $this->ok($rel)
                : $this->fail("{$rel} is not writable (chmod -R 775)");
        }

        $this->section('Files');
        foreach (['public/img/esdo-logo.png' => 'logo printed on documents'] as $rel => $why) {
            file_exists(base_path($rel)) ? $this->ok($rel) : $this->warn2("{$rel} missing - {$why}");
        }

        $this->section('.env / config');
        config('app.key') ? $this->ok('APP_KEY set') : $this->fail('APP_KEY empty - run php artisan key:generate');
        config('app.env') === 'production' ? $this->ok('APP_ENV=production') : $this->warn2('APP_ENV is "' . config('app.env') . '"');
        config('app.debug') === false ? $this->ok('APP_DEBUG=false') : $this->fail('APP_DEBUG is true - never leave it on in production');
        str_starts_with((string) config('app.url'), 'https://')
            ? $this->ok('APP_URL uses https')
            : $this->warn2('APP_URL is ' . config('app.url'));
        file_exists(base_path('.env')) && (fileperms(base_path('.env')) & 0004)
            ? $this->warn2('.env is world-readable - chmod 600 .env')
            : $this->ok('.env permissions');

        $this->section('Database');
        try {
            DB::connection()->getPdo();
            $this->ok('Connected to ' . DB::connection()->getDatabaseName());
            $count = DB::table('migrations')->count();
            $this->ok("migrations table has {$count} rows");
        } catch (\Throwable $e) {
            $this->fail('Database connection failed: ' . $e->getMessage());
        }

        $this->newLine();
        if ($this->failures > 0) {
            $this->error("{$this->failures} problem(s), {$this->warnings} warning(s). Fix the FAIL lines above.");
            return self::FAILURE;
        }
        $this->info("All required checks passed ({$this->warnings} warning(s)).");
        return self::SUCCESS;
    }

    private function section(string $title): void
    {
        $this->newLine();
        $this->line("<options=bold>== {$title} ==</>");
    }

    private function ok(string $msg): void
    {
        $this->line("  <fg=green>OK  </> {$msg}");
    }

    private function warn2(string $msg): void
    {
        $this->warnings++;
        $this->line("  <fg=yellow>WARN</> {$msg}");
    }

    private function fail(string $msg): void
    {
        $this->failures++;
        $this->line("  <fg=red>FAIL</> {$msg}");
    }
}
