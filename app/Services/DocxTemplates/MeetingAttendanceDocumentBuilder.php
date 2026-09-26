<?php

namespace App\Services\DocxTemplates;

use App\Services\CommitteeDocumentText as Txt;
use App\Services\DocxTemplates\Support\BuildsEsdoDocx;
use PhpOffice\PhpWord\PhpWord;

/**
 * Builds the Committee Meeting Attendance sheet. Mirrors
 * resources/views/documents/meeting-attendance.blade.php.
 *
 * Expects: ['meeting', 'case', 'committeeLocation', 'convener'].
 */
class MeetingAttendanceDocumentBuilder
{
    use BuildsEsdoDocx;

    public function build(array $data): PhpWord
    {
        $meeting = $data['meeting'];
        $case = $data['case'];
        $committeeLocation = $data['committeeLocation'];

        $phpWord = $this->newPhpWord();
        $section = $this->addSection($phpWord);

        $this->addLetterhead($section, 'Eco-Social Development Organization (ESDO)');
        $section->addText(
            'House # 748, Baitul Aman Housing Society, Road # 8, Adabor, Dhaka-1207',
            ['bold' => true, 'color' => '1F4E9C', 'size' => 10], $this->c()
        );
        $section->addText('Gobindanagar (Collegepara), Thakurgaon-5100', ['bold' => true, 'color' => '1F4E9C', 'size' => 10], $this->c());
        $section->addTextBreak(1);

        $meta = $section->addTable(['borderSize' => 0, 'cellMargin' => 0]);
        $meta->addRow();
        $left = $meta->addCell(6300);
        $leftRun = $left->addTextRun();
        $leftRun->addText('Attendance Number: ', $this->b());
        $leftRun->addText((string) $meeting->attendance_number, ['italic' => true]);
        $right = $meta->addCell(2700);
        $rightRun = $right->addTextRun(['alignment' => 'end']);
        $rightRun->addText('Attendance Date: ', $this->b());
        $rightRun->addText($meeting->meeting_date->format('d F, Y'), ['italic' => true]);

        $section->addTextBreak(1);
        $section->addText(
            'Attendance of Procurement Committee Meeting to ' . Txt::agendaLine($case),
            array_merge($this->b(), ['size' => 11.5]),
            $this->c()
        );

        $section->addText('Meeting Summary:', array_merge($this->b(), ['size' => 10.5, 'underline' => 'single']));
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

        $section->addText('Meeting Agenda:', array_merge($this->b(), ['size' => 10.5, 'underline' => 'single']));
        $this->addNumberedList($section, Txt::agendaItems($case, $meeting->agenda));

        $section->addText('Attendance of Procurement Committee Meeting:', array_merge($this->b(), ['size' => 10.5, 'underline' => 'single']));
        $table = $section->addTable($this->borderedTableStyle());
        $table->addRow();
        foreach ([['Sl. No.', 800], ['Name', 3000], ['Designation', 2400], ['Signature', 2000], ['Remarks', 1800]] as [$h, $w]) {
            $table->addCell($w, $this->headerCellStyle())->addText($h, $this->b());
        }

        $attendees = $meeting->attendees;
        if ($attendees->isEmpty()) {
            for ($i = 0; $i < 4; $i++) {
                $table->addRow();
                $table->addCell(800)->addText(str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT), [], $this->c());
                $table->addCell(3000)->addText('');
                $table->addCell(2400)->addText('');
                $table->addCell(2000)->addText('');
                $table->addCell(1800)->addText('');
            }
        } else {
            foreach ($attendees as $i => $attendee) {
                $table->addRow();
                $table->addCell(800)->addText(str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT), [], $this->c());
                $table->addCell(3000)->addText($attendee->name);
                $table->addCell(2400)->addText($attendee->designation);
                $table->addCell(2000)->addText('');
                $table->addCell(1800)->addText((string) $attendee->remarks);
            }
        }

        $section->addTextBreak(1);
        $section->addText('Approved');
        $section->addText('Convener,');
        $section->addText("Central Procurement Committee, {$committeeLocation}.");

        return $phpWord;
    }
}
