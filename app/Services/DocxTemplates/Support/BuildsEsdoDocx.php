<?php

namespace App\Services\DocxTemplates\Support;

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\SimpleType\JcTable;
use PhpOffice\PhpWord\Element\Section;

/**
 * Common building blocks so every document builder in
 * App\Services\DocxTemplates produces a consistent look (same fonts,
 * letterhead, table borders, footer disclaimer) without repeating the
 * PHPWord boilerplate in each class.
 */
trait BuildsEsdoDocx
{
    protected function newPhpWord(): PhpWord
    {
        // CRITICAL: PHPWord does NOT escape text for XML by default — any
        // '<', '>' or '&' a user typed into a free-text field (item spec,
        // delivery location, subject, remarks, vendor name, etc.) gets
        // written into word/document.xml raw, which corrupts the file and
        // is exactly what produces Word's "illegal name character" /
        // "problems with the contents" error on open. This must be enabled
        // before any content is added.
        \PhpOffice\PhpWord\Settings::setOutputEscapingEnabled(true);

        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Calibri');
        $phpWord->setDefaultFontSize(10.5);
        $phpWord->getSettings()->setThemeFontLang(new \PhpOffice\PhpWord\Style\Language('en-US'));

        return $phpWord;
    }

    protected function addSection(PhpWord $phpWord, array $extra = []): Section
    {
        return $phpWord->addSection(array_merge([
            'marginLeft' => 850, 'marginRight' => 850, 'marginTop' => 850, 'marginBottom' => 850,
        ], $extra));
    }

    protected function b(): array
    {
        return ['bold' => true];
    }

    protected function c(): array
    {
        return ['alignment' => Jc::CENTER];
    }

    protected function bc(): array
    {
        return ['bold' => true, 'alignment' => Jc::CENTER];
    }

    /** ESDO letterhead: logo (if present) + org name, centered, with an optional address/subtitle line. */
    protected function addLetterhead(Section $section, string $orgLine = 'Eco-Social Development Organization (ESDO)', ?string $subtitle = null): void
    {
        $logo = public_path('img/esdo-logo.png');

        $table = $section->addTable(['borderSize' => 0, 'cellMargin' => 0, 'width' => 100 * 50, 'unit' => 'pct']);
        $table->addRow();
        $logoCell = $table->addCell(1500, ['valign' => 'center']);
        if (file_exists($logo)) {
            $logoCell->addImage($logo, ['width' => 55, 'height' => 55, 'alignment' => Jc::CENTER]);
        } else {
            $logoCell->addText('');
        }
        $textCell = $table->addCell(7500, ['valign' => 'center']);
        $textCell->addText($orgLine, array_merge($this->b(), ['size' => 14]), $this->c());
        if ($subtitle) {
            $textCell->addText($subtitle, ['size' => 9, 'color' => '555555'], $this->c());
        }
        $section->addTextBreak(1);
    }

    /** The standard footer disclaimer used on every generated document. */
    protected function addFooterDisclaimer(Section $section): void
    {
        $section->addTextBreak(1);
        $section->addText(
            '(This is a system-generated document; signature is not required. The document is ready only after verification by the concerned official.)',
            ['italic' => true, 'size' => 8.5, 'color' => '444444'],
            $this->c()
        );
    }

    /** Default border settings for a data table (mirrors the .bordered CSS class). */
    protected function borderedTableStyle(): array
    {
        return [
            'borderSize' => 6,
            'borderColor' => '333333',
            'cellMargin' => 80,
        ];
    }

    protected function headerCellStyle(): array
    {
        return ['bgColor' => 'EEEEEE'];
    }

    protected function fmtDate($value, string $format = 'd F, Y'): string
    {
        return $value ? $value->format($format) : '';
    }

    protected function fmtMoney($value): string
    {
        return number_format((float) $value, 2);
    }

    /** Writes an ordered numbered paragraph list (Terms & Conditions style). */
    protected function addNumberedList(Section $section, array $lines): void
    {
        foreach ($lines as $i => $line) {
            $section->addText(($i + 1) . '. ' . $line, [], ['spaceAfter' => 60]);
        }
    }
}
