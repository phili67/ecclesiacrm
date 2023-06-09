<?php

namespace EcclesiaCRM\Reports;

class PDF_FRReport extends ChurchInfoReportTCPDF
{
    public $leftX = 10;
    public $orientation = 10;
    public $format = 10;
    public $fr_title = 'Voila';
    public $fr_description = 'coucou';
    public $curY = 0;

    public function __construct($fundTitle, $fundDescription)
    {
        parent::__construct('P', 'mm', $this->paperFormat);
        $this->leftX = 10;
        $this->SetFont('Times', '', 10);
        $this->SetMargins(10, 20);

        $this->fr_title = $fundTitle;
        $this->fr_description = $fundDescription;

        $this->AddCustomPage();
        $this->SetAutoPageBreak(true, 25);
    }

    public function AddCustomPage()
    {
        parent::AddPage();

        $this->SetFont('Times', 'B', 16);
        $this->Write(8, $this->fr_title."\n");
        $this->curY += 8;
        $this->Write(8, $this->fr_description."\n\n");
        $this->curY += 8;
        $this->SetFont('Times', '', 12);
    }
}

class PDF_FRCatalogReport extends PDF_FRReport
{
}

class PDF_CertificatesReport extends PDF_FRReport
{
    public function AddCustomPage()
    {
        parent::AddPage();

        $this->SetFont('Times', 'B', 16);
        $this->Write(8, $this->fr_title."\n");
        $this->curY += 8;
        $this->Write(8, $this->fr_description."\n\n");
        $this->curY += 8;
        $this->SetFont('Times', 'B', 36);
        $this->Write(8, _('Certificate of Ownership')."\n\n");
        $this->curY += 8;
        $this->SetFont('Times', '', 10);
    }
}

class PDF_FRBidSheetsReport extends PDF_FRReport
{
}
