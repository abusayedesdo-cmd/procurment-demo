<?php

namespace App\Support;

/**
 * Taka amounts the way ESDO prints them: Bangladeshi grouping (Crore / Lac /
 * Thousand) and — unlike the old integer-only helper — the paisa part, so
 * 32,66,714.35 reads "... Seven Hundred Fourteen Taka and Thirty-Five Paisa Only".
 */
class AmountInWords
{
    private const ONES = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten',
        'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
    private const TENS = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

    /** "32,66,714.35" / "16,73,915" (no decimals when the amount is whole). */
    public static function figure(float $amount): string
    {
        $paisa = (int) round($amount * 100);
        $taka = intdiv($paisa, 100);
        $p = $paisa % 100;

        $s = (string) $taka;
        if (strlen($s) > 3) {
            $head = substr($s, 0, -3);
            $tail = substr($s, -3);
            $head = strrev(implode(',', str_split(strrev($head), 2)));
            $s = $head . ',' . $tail;
        }

        return $p ? $s . '.' . str_pad((string) $p, 2, '0', STR_PAD_LEFT) : $s;
    }

    /** "Sixteen Lac Seventy-Three Thousand Nine Hundred Fifteen Taka Only". */
    public static function words(float $amount): string
    {
        $paisa = (int) round($amount * 100);
        $taka = intdiv($paisa, 100);
        $p = $paisa % 100;

        if ($taka === 0 && $p === 0) {
            return 'Zero Taka Only';
        }

        $out = $taka > 0 ? self::integer($taka) . ' Taka' : '';
        if ($p > 0) {
            $out .= ($out !== '' ? ' and ' : '') . self::below100($p) . ' Paisa';
        }

        return $out . ' Only';
    }

    private static function integer(int $n): string
    {
        $crore = intdiv($n, 10000000); $n %= 10000000;
        $lac = intdiv($n, 100000); $n %= 100000;
        $thousand = intdiv($n, 1000); $n %= 1000;

        $parts = [];
        if ($crore) $parts[] = self::below1000($crore) . ' Crore';
        if ($lac) $parts[] = self::below1000($lac) . ' Lac';
        if ($thousand) $parts[] = self::below1000($thousand) . ' Thousand';
        if ($n) $parts[] = self::below1000($n);

        return implode(' ', $parts);
    }

    private static function below1000(int $x): string
    {
        if ($x < 100) {
            return self::below100($x);
        }

        return self::ONES[intdiv($x, 100)] . ' Hundred' . ($x % 100 ? ' ' . self::below100($x % 100) : '');
    }

    private static function below100(int $x): string
    {
        if ($x < 20) {
            return self::ONES[$x];
        }

        return self::TENS[intdiv($x, 10)] . ($x % 10 ? '-' . self::ONES[$x % 10] : '');
    }
}
