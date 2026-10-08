<?php

namespace App\Services\DocxTemplates;

use App\Services\DocxTemplates\Support\BuildsEsdoDocx;
use App\Support\AmountInWords;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;

/**
 * Contract Agreement between ESDO (the Procuring Entity) and the awarded
 * vendor: parties, recital with the contract price, the 4 operative clauses
 * and the signature table (Procuring Entity + witness | Supplier + witness).
 * Printed as a "(Draft Contract Agreement)" while no agreement has been
 * recorded yet — that draft is what the NoA says is attached to it.
 *
 * Expects (see ContractDocumentController::awardData()): draft (bool), date,
 * agreementNo, vendorName, vendorAddress, workTitle, project, location,
 * category, amount, committeeName, convener (?['name','designation']),
 * secretary (?['name','designation']), officeAddress.
 */
class ContractAgreementDocumentBuilder
{
    use BuildsEsdoDocx;

    public function build(array $d): PhpWord
    {
        $phpWord = $this->newPhpWord();
        $section = $this->addSection($phpWord);
        $just = ['alignment' => Jc::BOTH, 'spaceAfter' => 120];

        $committee = $d['committeeName'] ?: 'Central Procurement Committee';
        $work = $d['workTitle'] ?: 'the awarded work';
        $category = $d['category'] ?? 'Work';
        [$verb, $done] = match ($category) {
            'Goods' => ['supply', 'supplied'],
            'Service' => ['provide', 'provided'],
            default => ['execute and complete', 'executed'],
        };
        $what = $work . (! empty($d['project']) ? ' under the ' . $d['project'] . ' project' : '')
            . (! empty($d['location']) ? ' at ' . $d['location'] : '');
        $price = $d['amount'] !== null
            ? 'Tk-' . AmountInWords::figure($d['amount']) . '/- (' . str_replace(' Taka Only', ' Only', AmountInWords::words($d['amount'])) . ')'
            : 'Tk-____________/-';

        if (! empty($d['draft'])) {
            $section->addText('(Draft Contract Agreement)', $this->b(), $this->c());
        } else {
            $section->addText('CONTRACT AGREEMENT', array_merge($this->b(), ['size' => 14]), $this->c());
            if (! empty($d['agreementNo'])) {
                $section->addText('Agreement No: ' . $d['agreementNo'], ['size' => 10], $this->c());
            }
        }
        $section->addTextBreak(1);

        $addr = rtrim((string) ($d['vendorAddress'] ?? ''), " .,");
        $date = $d['date'] ? $d['date']->format('d F Y') : '____ ____________ ______';
        $run = $section->addTextRun($just);
        $run->addText('THIS AGREEMENT', $this->b());
        $run->addText(' made on ');
        $run->addText($date, $this->b());
        $run->addText(' between the Convener, ' . $committee . ', ');
        $run->addText('Eco-Social Development Organization (ESDO)', $this->b());
        $run->addText(', (hereinafter called “the Procuring Entity”) of the one part and ');
        $run->addText($d['vendorName'] ?: '[Vendor name]', $this->b());
        $run->addText($addr !== '' ? ', ' . $addr . ', ' : ', ');
        $run->addText('(hereinafter called “the Supplier/Contractor”) of the other part:');

        $run = $section->addTextRun($just);
        $run->addText('WHEREAS', $this->b());
        $run->addText(' the Procuring Entity invited Tenders for ' . $what . ' and has accepted a Tender by the Vendor/Contractor for the ' . ($category === 'Work' ? 'execution' : ($category === 'Goods' ? 'supply' : 'provision')) . ' of those ' . $work . ' in the sum of ');
        $run->addText($price, $this->b());
        $run->addText(' hereinafter called “the Contract Price”. ');
        if ($category === 'Work') {
            $run->addText('ESDO may increase/decrease work quantity; unit rate remains fixed. ');
        }
        $run->addText('All works, goods & related services delivery under the contract shall at all times be open to examination, inspection, measurements, testing, commissioning, and supervision of the Procuring Entity or his/her authorized representative / ESDO project office representative.');

        $section->addText('NOW THIS AGREEMENT WITNESSETH AS FOLLOWS:', $this->b(), ['spaceBefore' => 120, 'spaceAfter' => 120]);

        $section->addText('1. In this Agreement words and expressions shall have the same meanings as are respectively assigned to them in the General Conditions of Contract hereafter referred to.', [], $just);
        $section->addText('2. The documents forming the Contract shall be interpreted in the following order of priority:', [], $just);
        foreach (['(a) the signed Contract Agreement', '(b) the Notification of Award', '(c) the completed Tender'] as $line) {
            $section->addText($line, [], ['indentation' => ['left' => 567], 'spaceAfter' => 40]);
        }
        $section->addText('3. In consideration of the payments to be made by the Procuring Entity to the Vendor/Contractor as hereinafter mentioned, the Vendor/Contractor hereby covenants with the Procuring Entity to ' . $verb . ' ' . $work . ' and to remedy any defects therein in conformity in all respects with the provisions of the Contract.', [], ['alignment' => Jc::BOTH, 'spaceBefore' => 120, 'spaceAfter' => 120]);
        $section->addText('4. The Procuring Entity hereby covenants to pay the Vendor/Contractor in consideration of ' . $work . ' being ' . $done . ' and the remedying of defects therein, the Contract Price or such other sum as may become payable under the provisions of the Contract at the times and in the manner prescribed by the Contract.', [], $just);

        $section->addText('IN WITNESS whereof the parties hereto have caused this Agreement to be executed in accordance with the laws of Bangladesh on the day, month and year first written above.', [], $just);
        $section->addTextBreak(1);

        // signature table — white borders: both Word and the PDF renderer draw a hairline for "no border", white never shows
        $t = $section->addTable(['borderSize' => 6, 'borderColor' => 'FFFFFF', 'cellMargin' => 40]);
        $t->addRow();
        $left = $t->addCell(4800);
        $right = $t->addCell(4800);

        $left->addText('For the Procuring Entity', $this->b());
        $left->addTextBreak(1);
        $left->addText('………………………');
        $left->addText($d['convener']['name'] ?? '[Convener name]', $this->b());
        $left->addText('Convener, ' . $committee . '.');
        $left->addText('ESDO, Dhaka Office.');
        $left->addTextBreak(1);
        $left->addText('Witness-', $this->b());
        $left->addText('Signature: ………………………');
        $left->addText('Name: ' . ($d['secretary']['name'] ?? '………………………'));
        $left->addText('Designation: ' . ($d['secretary']['designation'] ?? '………………………'));
        $left->addText('Member-Secretary, ' . $committee . '.');
        $left->addText('Address: ' . ($d['officeAddress'] ?? ''));

        $right->addText('The Supplier', $this->b());
        $right->addTextBreak(1);
        $right->addText('………………………');
        $right->addText($d['vendorName'] ?: '[Vendor name]', $this->b());
        if ($addr !== '') {
            $right->addText($addr . '.');
        }
        $right->addTextBreak(1);
        $right->addText('Witness-', $this->b());
        foreach (['Signature: ………………………', 'Name: ………………………', 'Designation: ………………………', 'Address: ………………………'] as $line) {
            $right->addText($line);
        }

        return $phpWord;
    }
}
