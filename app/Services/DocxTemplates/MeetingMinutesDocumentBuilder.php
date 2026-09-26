<?php

namespace App\Services\DocxTemplates;

use App\Services\CommitteeDocumentText as Txt;
use App\Services\DocxTemplates\Support\BuildsEsdoDocx;
use PhpOffice\PhpWord\PhpWord;

/**
 * Builds the Meeting Rezulation/Minutes document. Mirrors
 * resources/views/documents/meeting-minutes.blade.php.
 *
 * Expects: ['meeting', 'case', 'convener', 'memberSecretaryName', 'committeeLocation'].
 */
class MeetingMinutesDocumentBuilder
{
    use BuildsEsdoDocx;

    public function build(array $data): PhpWord
    {
        $meeting = $data['meeting'];
        $case = $data['case'];
        $convener = $data['convener'];
        $memberSecretaryName = $data['memberSecretaryName'];
        $committeeLocation = $data['committeeLocation'];

        $phpWord = $this->newPhpWord();
        $section = $this->addSection($phpWord);

        $this->addLetterhead($section, 'Eco-Social Development Organization (ESDO)');
        $section->addText(
            'House # 748, Baitul Aman Housing Society, Road # 8, Adabor, Dhaka-1207',
            ['bold' => true, 'color' => '1F4E9C', 'size' => 10.5], $this->c()
        );
        $section->addText('Gobindanagar (Collegepara), Thakurgaon-5100', ['bold' => true, 'color' => '1F4E9C', 'size' => 10.5], $this->c());
        $section->addTextBreak(1);

        $numRun = $section->addTextRun();
        $numRun->addText('Rezulation/Minutes Number: ', $this->b());
        $numRun->addText((string) $meeting->rezulation_no, ['italic' => true]);

        $meta = $section->addTable(['borderSize' => 0, 'cellMargin' => 0]);
        $meta->addRow();
        $locCell = $meta->addCell(4500);
        $locRun = $locCell->addTextRun();
        $locRun->addText('Meeting Location: ', $this->b());
        $locRun->addText($meeting->location ?: 'N/A', ['italic' => true]);
        $whenCell = $meta->addCell(4500);
        $whenRun = $whenCell->addTextRun();
        $whenRun->addText('Meeting Date & Time: ', $this->b());
        $whenRun->addText($meeting->meeting_date->format('d F, Y') . ($meeting->meeting_time ? ', ' . $meeting->meeting_time : ''), ['italic' => true]);

        $section->addText(
            'The meeting was led by ' . ($convener->name ?? '[Convener Name]') . ', Convener of the Central Procurement '
            . "Committee, {$committeeLocation}. At the beginning, he welcomed all the members and thanked them for joining. "
            . 'After that, he started the meeting officially.'
        );

        $section->addText('Attendance of Procurement Committee Meeting:', array_merge($this->b(), ['size' => 11.5, 'underline' => 'single']));
        $table = $section->addTable($this->borderedTableStyle());
        $table->addRow();
        foreach ([['Sl. No.', 800], ['Name', 3000], ['Designation', 2400], ['Signature', 2000], ['Remarks', 1800]] as [$h, $w]) {
            $table->addCell($w, $this->headerCellStyle())->addText($h, $this->b());
        }
        $attendees = $meeting->attendees;
        if ($attendees->isEmpty()) {
            for ($i = 0; $i < 3; $i++) {
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

        $section->addText('Meeting Agenda:', array_merge($this->b(), ['size' => 11.5, 'underline' => 'single']));
        $agendaLines = array_merge(
            ['Reading and approval of the minutes of the previous meeting.'],
            Txt::agendaItems($case, $meeting->agenda)
        );
        $this->addNumberedList($section, $agendaLines);

        $section->addText("Decisions of Today's Meeting:", array_merge($this->b(), ['size' => 11.5, 'underline' => 'single']));

        $section->addText('1. Reading and approval of the minutes of the previous meeting:', $this->b());
        $section->addText(
            "The Member Secretary of the Central Procurement Committee, {$committeeLocation}, {$memberSecretaryName}, read out "
            . 'the rezulation/minutes of the previous meeting. After review, the convener approved the rezulation/minutes '
            . 'without any amendment, addition, or deletion.'
        );

        $verb = Txt::verb($case);
        $subCategory = Txt::subCategoryName($case);
        $category = Txt::categoryName($case);
        $section->addText("2. Regarding the {$verb} {$subCategory} for the {$category}:", $this->b());
        $decisionText = $meeting->decisions ?: (
            'A requisition was submitted to the Central Procurement Committee, ' . $committeeLocation . ' for the '
            . $verb . ' ' . $subCategory . ' for the ' . $category . ' under the ' . (Txt::projectName($case) ?? 'N/A') . ' Project.'
        );
        $section->addText($decisionText);

        if ($meeting->meeting_type === 'first') {
            $section->addText('After discussion, all committee members agreed, and the Committee confirmed the following tender schedule:');

            $scheduleLines = [
                'Tender/RFQ/Sole Sourcing/Framework Agreement Vendor Publication Date: ' . (optional($meeting->publish_date)->format('d F, Y') ?: 'N/A'),
            ];
            if ($meeting->schedule_override_reason) {
                $scheduleLines[] = 'Special Note: ' . $meeting->schedule_override_reason;
            }
            $scheduleLines[] = 'Tender Submission Deadline: ' . (optional($meeting->closing_date)->format('d F, Y') ?: 'N/A');
            $scheduleLines[] = 'Tender Opening Time: ' . (optional($meeting->opening_date)->format('d F, Y') ?: 'N/A');

            foreach ($scheduleLines as $line) {
                $section->addText('o  ' . $line);
            }

            $section->addText(
                'The Committee has agreed that the procurement process will be carried out by selecting the qualified '
                . 'bidder after comparing at least three eligible bids after proper technical and financial evaluation as '
                . 'per the procurement policy of ESDO. The Committee will conduct interviews with the initially selected '
                . 'vendors as required to verify their qualifications, technical expertise and relevant experience.'
            );
        } else {
            $section->addText(
                'After reviewing the Comparative Statement of Bids, the Committee discussed the technical and financial '
                . 'evaluation of the bidders and agreed on the recommended vendor for award, subject to final approval.'
            );
        }

        $section->addText('3. Miscellaneous:', $this->b());
        $section->addText(
            "As there were no further issues for discussion, the Central Procurement Committee, {$committeeLocation} "
            . 'thanked all members for their active participation and declared the meeting adjourned.'
        );

        $section->addTextBreak(1);
        $section->addText('Approved');
        $section->addText('Convener,');
        $section->addText("Central Procurement Committee, {$committeeLocation}.");

        return $phpWord;
    }
}
