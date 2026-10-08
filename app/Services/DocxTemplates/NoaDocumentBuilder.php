<?php

namespace App\Services\DocxTemplates;

use App\Services\DocxTemplates\Support\BuildsEsdoDocx;
use App\Support\AmountInWords;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;

/**
 * Notification of Award (NoA) — ESDO's standard award letter: award statement,
 * the 3 actions the vendor must take within 7 days (written acceptance,
 * performance security by pay order / draft, contract on a Tk 300 stamp), the
 * "you may proceed only after" paragraph, the cancellation note and the
 * Convener / Member Secretary sign-off.
 *
 * Expects (see ContractDocumentController::awardData()):
 *   date, ref, vendorName, vendorAddress, tenderNo, tenderDate, workTitle,
 *   project, location, category, amount, securityAmount, securityPct,
 *   warrantyEnds, committeeName, letterhead (bool — false when the Word pad
 *   already supplies the letterhead).
 */
class NoaDocumentBuilder
{
    use BuildsEsdoDocx;

    public function build(array $d): PhpWord
    {
        $phpWord = $this->newPhpWord();
        $section = $this->addSection($phpWord);
        $just = ['alignment' => Jc::BOTH, 'spaceAfter' => 120];

        if (! empty($d['letterhead'])) {
            // Borderless on purpose (a table would be drawn as a box in the PDF).
            $logo = public_path('img/esdo-logo.png');
            if (file_exists($logo)) {
                $section->addImage($logo, ['width' => 55, 'height' => 55, 'alignment' => Jc::CENTER]);
            }
            $section->addText('Eco-Social Development Organization (ESDO)', array_merge($this->b(), ['size' => 14]), $this->c());
            $section->addText('Collegepara (Gobindanagar), Thakurgaon, Rangpur, Bangladesh', ['size' => 9, 'color' => '555555'], $this->c());
            $section->addTextBreak(1);
        }

        $section->addText('Notification of Award (NoA)', array_merge($this->b(), ['size' => 14, 'underline' => 'single']), $this->c());
        $section->addTextBreak(1);

        $section->addText('Date: ' . ($d['date'] ? $d['date']->format('d.m.Y') : '____________'));
        if (! empty($d['ref'])) {
            $section->addText('Ref: ' . $d['ref']);
        }
        $section->addTextBreak(1);

        $section->addText('To,');
        $section->addText($d['vendorName'] ?: '[Vendor name]', $this->b());
        if (! empty($d['vendorAddress'])) {
            $section->addText(rtrim((string) $d['vendorAddress'], " .,") . '.');
        }
        $section->addTextBreak(1);

        $work = $d['workTitle'] ?: 'the awarded work';
        $isWork = ($d['category'] ?? 'Work') === 'Work';
        $amountTxt = $d['amount'] !== null
            ? 'BDT ' . AmountInWords::figure($d['amount']) . ' (' . AmountInWords::words($d['amount']) . ')'
            : 'BDT ____________';

        $what = $work
            . (! empty($d['location']) ? ' at ' . $d['location'] : '')
            . (! empty($d['project']) ? ' for ' . $d['project'] . ' project' : '');
        $tenderDate = ! empty($d['tenderDate']) ? ' dated on ' . $d['tenderDate']->format('d/m/Y') : '';

        $section->addText(
            'This is to notify you that, you are awarded for tender no. ' . ($d['tenderNo'] ?: '____________') . $tenderDate
            . ' for the ' . $what . '. Your awarded amount ' . $amountTxt . '. The contract price may vary according to the variance of quantity during '
            . ($isWork ? 'execution of works' : 'supply') . '.',
            [], $just
        );

        $section->addText('Receiving this letter, you are thus requested to take actions as per below:', [], $just);

        $secAmt = $d['securityAmount'] ?? null;
        $items = ['Accept in writing the Notification of Award (NoA) within Seven (7) days.'];

        if ($secAmt) {
            $pct = $d['securityPct'] !== null ? rtrim(rtrim(number_format((float) $d['securityPct'], 2, '.', ''), '0'), '.') . '% of the scheduled tender amount of ' : 'an amount of ';
            $warranty = ! empty($d['warrantyEnds'])
                ? 'after the warranty period ending on ' . $d['warrantyEnds']->format('d/m/Y')
                : 'after the warranty period';
            $items[] = 'Within seven (7) days of issuance of this letter, a performance security of ' . $pct
                . 'BDT ' . AmountInWords::figure((float) $secAmt) . ' (' . str_replace(' Taka Only', ' Only', AmountInWords::words((float) $secAmt))
                . ') should be deposited through Pay Order / Bank Draft etc. in favor of Eco-Social Development Organization (ESDO). '
                . 'The Performance Security will be returned to you upon successful completion of work and evaluation ' . $warranty . '.';
            $items[] = 'After depositing the pay order / bank draft etc., you will sign a contract agreement on a non-judicial stamp of 300 taka within seven (7) days of issuance of this Notification of Award (NoA).';
        } else {
            $items[] = 'You will sign a contract agreement on a non-judicial stamp of 300 taka within seven (7) days of issuance of this Notification of Award (NoA).';
        }
        $this->addNumberedList($section, $items);

        $section->addTextBreak(1);
        $section->addText(
            'You may proceed with ' . $what . ' only upon completion of the above tasks. You may also please note that this Notification of Award shall constitute the formation of this contract which shall become binding upon you.',
            [], $just
        );
        $section->addText(
            'NB: If you fail to ' . ($secAmt ? 'submit the pay order / bank draft etc. and ' : '') . 'sign the agreement within the specified seven (7) days, this offer letter will be deemed cancelled.',
            $this->b(), $just
        );
        $section->addText('We attach the draft Contract and all other documents for your perusal and signature.', [], $just);

        $section->addTextBreak(2);
        $section->addText('Signed');
        $section->addTextBreak(1);
        $section->addText('Convener / Member Secretary', $this->b());
        $section->addText(($d['committeeName'] ?: 'Central Procurement Committee') . '.');
        $section->addText('Eco-Social Development Organization (ESDO)');

        return $phpWord;
    }
}
