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

        $this->addLetterhead($section, 'Eco-Social Development Organization (ESDO)');
        $section->addText(
            'House # 748, Baitul Aman Housing Society, Road # 8, Adabor, Dhaka-1207',
            ['bold' => true, 'color' => '1F4E9C', 'size' => 10.5], $this->c()
        );
        $section->addText('Gobindanagar (Collegepara), Thakurgaon-5100', ['bold' => true, 'color' => '1F4E9C', 'size' => 10.5], $this->c());
        $section->addTextBreak(1);

        return $this->buildInner($phpWord, $section, $meeting, $case, $committeeLocation, $memberDesignation);
    }

    private function buildInner(PhpWord $phpWord, $section, $meeting, $case, string $committeeLocation, string $memberDesignation): PhpWord
    {
        // Replace the placeholder meta table above with a proper mixed-run version.
        $metaRun = $section->addTable(['borderSize' => 0, 'cellMargin' => 0]);
        $metaRun->addRow();
        $left = $metaRun->addCell(4500);
        $leftRun = $left->addTextRun();
        $leftRun->addText('Notice Number: ', $this->b());
        $leftRun->addText((string) $meeting->notice_number, ['italic' => true]);
        $right = $metaRun->addCell(4500);
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
            $sumTable->addCell(4600)->addText($label);
            $sumTable->addCell(4400)->addText($value, ['italic' => true]);
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
        $section->addText('Convener,');
        $section->addText("Central Procurement Committee, {$committeeLocation}.");

        $section->addTextBreak(2);
        $section->addText(
            'Dhaka Office: ESDO House: House # 748, Road No: 08, Baitul Aman Housing Society, Adabar, Dhaka-1207, Bangladesh, '
            . 'Phone No: +88-02-58154857, Contact No: 01713149259, Email: esdobangladesh@hotmail.com, Web: www.esdo.net.bd',
            array_merge($this->b(), ['size' => 8]), $this->c()
        );
        $section->addText(
            'Head Office: Collegepara, Thakurgaon-5100, Tel: 0561-52149, 0561-61614 Mobile: 0174-063360 Fax: 0561-61599, '
            . 'E-mail: esdobangladesh@hotmail.com, web: www.esdo.net.bd',
            array_merge($this->b(), ['size' => 8]), $this->c()
        );
        $section->addText('Registration No: DSS: Thakur-440/88, NGO Bureau-694/93 (Renewed 2018), MRA 0000204', ['size' => 8], $this->c());

        return $phpWord;
    }
}
