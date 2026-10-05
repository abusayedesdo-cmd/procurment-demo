<?php

namespace App\Services\DocxTemplates\Support;

use ZipArchive;

/**
 * Puts the ESDO letterhead pad (ESDO_PAD_Formatin.docx) on an already-written
 * .docx: the pad's header (logo, organisation name, side tagline), its footer
 * (Dhaka / Head Office addresses, registration numbers) and its page size and
 * margins. The pad parts live in resources/pad/ — to change the pad, replace
 * those files (header.xml, footer.xml, their .rels and logo.emf) with the ones
 * from the new pad .docx.
 *
 * Works on the finished file (PHPWord cannot import a header from another
 * document), so call it right after the writer has saved the .docx.
 */
class EsdoPadApplier
{
    // Page size and margins taken from the pad (A4; twips).
    private const SECTION = '<w:sectPr>'
        . '<w:headerReference w:type="default" r:id="rIdEsdoPadHdr"/>'
        . '<w:footerReference w:type="default" r:id="rIdEsdoPadFtr"/>'
        . '<w:pgSz w:w="11909" w:h="16834" w:code="9"/>'
        . '<w:pgMar w:top="990" w:right="1008" w:bottom="720" w:left="2016" w:header="1007" w:footer="578" w:gutter="0"/>'
        . '<w:cols w:space="720"/>'
        . '</w:sectPr>';

    public static function apply(string $docxPath): bool
    {
        $dir = resource_path('pad');
        $parts = [
            'header' => $dir . '/header.xml',
            'headerRels' => $dir . '/header.xml.rels',
            'footer' => $dir . '/footer.xml',
            'footerRels' => $dir . '/footer.xml.rels',
            'logo' => $dir . '/logo.emf',
        ];
        foreach ($parts as $file) {
            if (! is_file($file)) {
                return false; // pad files missing — leave the document as it is
            }
        }

        $zip = new ZipArchive();
        if ($zip->open($docxPath) !== true) {
            return false;
        }

        $document = $zip->getFromName('word/document.xml');
        $relsXml = $zip->getFromName('word/_rels/document.xml.rels');
        $types = $zip->getFromName('[Content_Types].xml');
        if ($document === false || $relsXml === false || $types === false) {
            $zip->close();

            return false;
        }

        // 1) pad parts (own file names, so they never clash with anything PHPWord wrote)
        $zip->addFromString('word/esdoPadHeader.xml', file_get_contents($parts['header']));
        $zip->addFromString(
            'word/_rels/esdoPadHeader.xml.rels',
            str_replace('media/image1.emf', 'media/esdoPadLogo.emf', file_get_contents($parts['headerRels']))
        );
        $zip->addFromString('word/esdoPadFooter.xml', file_get_contents($parts['footer']));
        $zip->addFromString('word/_rels/esdoPadFooter.xml.rels', file_get_contents($parts['footerRels']));
        $zip->addFromString('word/media/esdoPadLogo.emf', file_get_contents($parts['logo']));

        // 2) content types
        if (! preg_match('/Extension="emf"/i', $types)) {
            $types = str_replace('</Types>', '<Default Extension="emf" ContentType="image/x-emf"/></Types>', $types);
        }
        $types = str_replace(
            '</Types>',
            '<Override PartName="/word/esdoPadHeader.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.header+xml"/>'
            . '<Override PartName="/word/esdoPadFooter.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.footer+xml"/>'
            . '</Types>',
            $types
        );
        $zip->addFromString('[Content_Types].xml', $types);

        // 3) relationships from the document to the pad header / footer
        $relsXml = str_replace(
            '</Relationships>',
            '<Relationship Id="rIdEsdoPadHdr" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/header" Target="esdoPadHeader.xml"/>'
            . '<Relationship Id="rIdEsdoPadFtr" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/footer" Target="esdoPadFooter.xml"/>'
            . '</Relationships>',
            $relsXml
        );
        $zip->addFromString('word/_rels/document.xml.rels', $relsXml);

        // 4) the section: header/footer references + the pad's page size and margins
        $start = strrpos($document, '<w:sectPr');
        if ($start === false) {
            $document = str_replace('</w:body>', self::SECTION . '</w:body>', $document);
        } else {
            $end = strpos($document, '</w:sectPr>', $start);
            if ($end === false) {
                $zip->close();

                return false;
            }
            $document = substr($document, 0, $start) . self::SECTION . substr($document, $end + strlen('</w:sectPr>'));
        }
        $zip->addFromString('word/document.xml', $document);

        return $zip->close();
    }
}
