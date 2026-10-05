<?php

namespace App\Services\DocxTemplates;

use App\Services\CommitteeDocumentText as Txt;
use App\Services\DocxTemplates\Support\BuildsEsdoDocx;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;

/**
 * Builds the Committee Meeting Notice. Mirrors resources/views/documents/meeting-notice.blade.php.
 *
 * Expects: ['meeting', 'case', 'convener', 'committeeLocation', 'memberDesignation'].
 */
class MeetingNoticeDocumentBuilder
{
    use BuildsEsdoDocx;

    public function build(array $data): PhpWord
    {
        $meeting = $data['meeting'];
        $case = $data['case'];
        $committeeLocation = $data['committeeLocation'];
        $memberDesignation = $data['memberDesignation'];

        $phpWord = $this->newPhpWord();
        $section = $this->addSection($phpWord);

        // The ESDO pad (header with logo/name, footer with addresses) is added to the file after it is written.

        return $this->buildInner(
            $phpWord, $section, $meeting, $case, $committeeLocation, $memberDesignation,
            (string) ($data['signerName'] ?? ''), (string) ($data['signerDesignation'] ?? '')
        );
    }

    private function buildInner(PhpWord $phpWord, $section, $meeting, $case, string $committeeLocation, string $memberDesignation, string $signerName = '', string $signerDesignation = ''): PhpWord
    {
        // Replace the placeholder meta table above with a proper mixed-run version.
        $metaRun = $section->addTable(['borderSize' => 0, 'cellMargin' => 0]);
        $metaRun->addRow();
        $left = $metaRun->addCell(4440);
        $leftRun = $left->addTextRun();
        $leftRun->addText('Notice Number: ', $this->b());
        $leftRun->addText((string) $meeting->notice_number, ['italic' => true]);
        $right = $metaRun->addCell(4440);
        $rightRun = $right->addTextRun(['alignment' => Jc::END]);
        $rightRun->addText('Notice Date: ', $this->b());
        $rightRun->addText($this->fmtDate($meeting->notice_date), ['italic' => true]);

        $section->addTextBreak(1);
        $section->addText(
            "Notice for Procurement Committee Meeting to " . Txt::agendaLine($case),
            array_merge($this->b(), ['size' => 12]),
            $this->c()
        );
        $section->addTextBreak(1);

        $section->addText("Dear Hon'ble {$memberDesignation} of Procurement Committee,");
        $section->addText("Greetings from Central Procurement Committee {$committeeLocation}!");

        $note = "Central Procurement Committee {$committeeLocation} has requested you to attend the " . Txt::agendaLine($case)
            . ' for below mentioned Purchase Requisition (PR):';
        if ($case->purchaseRequisition?->attachment_path) {
            $note .= ' (Details PR as PDF Linked)';
        }
        $section->addText($note);

        $section->addText('Summary of Purchase Requisition (PR):', array_merge($this->b(), ['size' => 11.5, 'underline' => 'single']));
        $sumTable = $section->addTable(['borderSize' => 0, 'cellMargin' => 0]);
        foreach ([
            ['Name of Project/Program/Department:', Txt::projectName($case) ?? 'N/A'],
            ['Location of Name of Project/Program/Department:', Txt::projectLocation($case) ?? 'N/A'],
            ['Subject of Purchase Requisition (PR):', Txt::subCategoryName($case) . ' for the ' . Txt::categoryName($case)],
            ['Total Amount of Purchase Requisition (PR):', number_format(Txt::totalAmount($case), 2) . ' Tk'],
        ] as [$label, $value]) {
            $sumTable->addRow();
            $sumTable->addCell(4500)->addText($label);
            $sumTable->addCell(4380)->addText($value, ['italic' => true]);
        }

        $when = $meeting->meeting_date->format('d F, Y') . ($meeting->meeting_time ? ', ' . $meeting->meeting_time : '');
        $whenRun = $section->addTextRun();
        $whenRun->addText('Meeting Date & Time: ', $this->b());
        $whenRun->addText($when, ['italic' => true]);

        $section->addText('Meeting Agenda:', array_merge($this->b(), ['size' => 11.5, 'underline' => 'single']));
        $this->addNumberedList($section, Txt::agendaItems($case, $meeting->agenda));

        $section->addTextBreak(1);
        $section->addText('With Thanks');
        $section->addTextBreak(1);
        if ($signerName !== '') {
            $section->addText('(' . $signerName . ')', $this->b());
        }
        if ($signerDesignation !== '') {
            $section->addText($signerDesignation . ',');
        }
        $section->addText("Central Procurement Committee, {$committeeLocation}.");

        return $phpWord;
    }
}
