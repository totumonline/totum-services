<?php

namespace services\common;

use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class ExcelMpdf extends \PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf
{
    protected array $PDFOptions = [];
    protected $MPDF = null;

    protected function createExternalWriterInstance($config)
    {
        if ($this->PDFOptions['format'] ?? false) {
            $config['format'] = $this->PDFOptions['format'];
        }
        return $this->MPDF ?? $this->MPDF = new \Mpdf\Mpdf($config, new \Mpdf\Container\SimpleContainer([
                'httpClient' => new CustomHttpClient(),
                'localContentLoader' => new CustomContentLoader(),
            ]));
    }

    private function inchesToMm($inches)
    {
        return $inches * 25.4;
    }

    public function save($filename, int $flags = 0): void
    {
        $fileHandle = parent::prepareForSave($filename);

        //  Check for paper size and page orientation
        $worksheet = $this->spreadsheet->getSheet($this->getSheetIndex() ?? 0);
        $setup = $worksheet->getPageSetup();

        $orientation = $this->getOrientation() ?? $setup->getOrientation();
        $orientation = ($orientation === PageSetup::ORIENTATION_LANDSCAPE) ? 'L' : 'P';
        $printPaperSize = $this->getPaperSize() ?? $setup->getPaperSize();
        $paperSize = self::$paperSizes[$printPaperSize] ?? PageSetup::getPaperSizeDefault();


        //  Create PDF
        $config = ['tempDir' => $this->tempDir . '/mpdf'];
        $pdf = $this->createExternalWriterInstance($config);
        $ortmp = $orientation;
        $pdf->_setPageSize($paperSize, $ortmp);
        $pdf->DefOrientation = $orientation;
        $pdf->AddPageByArray([
            'orientation' => $orientation,
            'margin-left' => $this->inchesToMm($this->spreadsheet->getActiveSheet()->getPageMargins()->getLeft()),
            'margin-right' => $this->inchesToMm($this->spreadsheet->getActiveSheet()->getPageMargins()->getRight()),
            'margin-top' => $this->inchesToMm($this->spreadsheet->getActiveSheet()->getPageMargins()->getTop()),
            'margin-bottom' => $this->inchesToMm($this->spreadsheet->getActiveSheet()->getPageMargins()->getBottom()),
        ]);


        //  Document info
        $pdf->SetTitle($this->spreadsheet->getProperties()->getTitle());
        $pdf->SetAuthor($this->spreadsheet->getProperties()->getCreator());
        $pdf->SetSubject($this->spreadsheet->getProperties()->getSubject());
        $pdf->SetKeywords($this->spreadsheet->getProperties()->getKeywords());
        $pdf->SetCreator($this->spreadsheet->getProperties()->getCreator());



        $html = $this->generateHTMLAll($setup->getPrintArea());
        $pdf->WriteHTML($html);

        //  Write to file
        fwrite($fileHandle, $pdf->Output('', 'S'));

        parent::restoreStateAfterSave();
    }
}