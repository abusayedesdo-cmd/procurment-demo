<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use PhpOffice\PhpWord\Settings as PhpWordSettings;

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
        $this->configureDocumentGeneration();
    }

    /**
     * Shared-hosting safety net for Word/PDF generation.
     *
     * - PhpWord normally builds .docx files with PHP's ZipArchive class. If the
     *   server's PHP has no `zip` extension, fall back to PhpWord's bundled
     *   PclZip (slower, but needs no extension). Once `zip` is enabled on the
     *   server this branch is simply skipped, so nothing has to be removed later.
     * - Temp files go to storage/app/tmp (always writable for the app) instead
     *   of the system temp dir, which some cPanel hosts restrict.
     */
    protected function configureDocumentGeneration(): void
    {
        if (! class_exists(PhpWordSettings::class)) {
            return;
        }

        if (! class_exists(\ZipArchive::class)) {
            PhpWordSettings::setZipClass(PhpWordSettings::PCLZIP);
        }

        $tmp = storage_path('app/tmp');
        if (! is_dir($tmp)) {
            @mkdir($tmp, 0775, true);
        }
        if (is_dir($tmp) && is_writable($tmp)) {
            PhpWordSettings::setTempDir($tmp);
        }
    }
}
