<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

/**
 * php artisan mail:test you@example.com [--no-verify]
 *
 * Sends one plain test mail using the app's real mail settings and prints the
 * REAL error if it fails. (The meeting-notice / attendance emails swallow
 * errors and only write a WARNING to laravel.log, so on the site itself a
 * broken mail setup looks like "mail just doesn't go".)
 */
class MailTest extends Command
{
    protected $signature = 'mail:test {to : recipient address} {--no-verify : skip SSL certificate name check (diagnostic only)}';
    protected $description = 'Send a test email and show the real SMTP error, if any';

    public function handle(): int
    {
        $to = $this->argument('to');
        $mailer = config('mail.default');
        $cfg = config("mail.mailers.{$mailer}", []);

        $this->line('Mailer      : ' . $mailer);
        $this->line('Host / Port : ' . ($cfg['host'] ?? '-') . ' / ' . ($cfg['port'] ?? '-'));
        $this->line('Encryption  : ' . ($cfg['encryption'] ?? '(none)') . '   scheme: ' . ($cfg['scheme'] ?? '(none)'));
        $this->line('Username    : ' . ($cfg['username'] ?? '-'));
        $this->line('Password    : ' . (empty($cfg['password']) ? 'EMPTY' : 'set (' . strlen((string) $cfg['password']) . ' chars)'));
        $this->line('From        : ' . config('mail.from.address') . ' <' . config('mail.from.name') . '>');
        $this->line('Queue       : ' . config('queue.default'));
        $this->newLine();

        if (in_array($mailer, ['log', 'array'], true)) {
            $this->error("MAIL_MAILER is '{$mailer}': nothing is really sent (log = written to storage/logs/laravel.log). Set MAIL_MAILER=smtp in .env and run php artisan config:clear.");
            return self::FAILURE;
        }

        if ($mailer === 'smtp' && ! empty($cfg['host']) && ! empty($cfg['port'])) {
            $this->probe($cfg['host'], (int) $cfg['port']);
        }

        if ($this->option('no-verify')) {
            config(["mail.mailers.{$mailer}.verify_peer" => false]);
            app('mail.manager')->purge($mailer);
            $this->warn('Certificate verification DISABLED for this test.');
        }

        try {
            Mail::raw('SMTP test from ESDO Procurement at ' . now()->toDateTimeString(), function ($m) use ($to) {
                $m->to($to)->subject('ESDO Procurement - SMTP test');
            });
            $this->info("Accepted by the mail server for {$to}. Check the inbox AND spam folder.");
            if ($this->option('no-verify')) {
                $this->warn('It only worked with --no-verify: the certificate name does not match MAIL_HOST. Use the exact host name printed on the certificate (cPanel > Email Accounts > Connect Devices) or ask hosting to fix the certificate.');
            }
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error(get_class($e));
            $this->error($e->getMessage());
            $prev = $e->getPrevious();
            while ($prev) {
                $this->line('  caused by: ' . get_class($prev) . ': ' . $prev->getMessage());
                $prev = $prev->getPrevious();
            }
            return self::FAILURE;
        }
    }

    /** Plain connectivity + TLS check so a blocked port / bad certificate is obvious. */
    private function probe(string $host, int $port): void
    {
        $scheme = $port === 465 ? 'ssl' : 'tcp';
        $errno = 0;
        $errstr = '';
        $ctx = stream_context_create(['ssl' => ['verify_peer' => true, 'verify_peer_name' => true]]);
        $fp = @stream_socket_client("{$scheme}://{$host}:{$port}", $errno, $errstr, 10, STREAM_CLIENT_CONNECT, $ctx);

        if ($fp) {
            fclose($fp);
            $this->info("Connection to {$host}:{$port} OK" . ($scheme === 'ssl' ? ' (certificate verified)' : ''));
        } else {
            $this->error("Cannot connect to {$host}:{$port} - [{$errno}] {$errstr}");
            $this->line('  "certificate"/"peer name" in the message = MAIL_HOST does not match the SSL certificate.');
            $this->line('  "timed out" / "refused" = the port is blocked or the host is wrong.');
        }
        $this->newLine();
    }
}
