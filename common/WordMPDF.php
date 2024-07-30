<?php

namespace services\common;

use Mpdf\Mpdf;

class WordMPDF extends \PhpOffice\PhpWord\Writer\PDF\MPDF
{
    protected array $PDFOptions = [];

    public function setPDFOptions(array $PDFOptions)
    {
        $this->PDFOptions = $PDFOptions;
    }

    protected function createExternalWriterInstance(){
        return new Mpdf($this->PDFOptions, new \Mpdf\Container\SimpleContainer([
            'httpClient' => new CustomHttpClient(),
            'localContentLoader' => new CustomContentLoader(),
        ]));
    }

}