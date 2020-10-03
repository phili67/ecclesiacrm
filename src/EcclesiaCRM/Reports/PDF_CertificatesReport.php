<?php

namespace EcclesiaCRM\Reports;

use EcclesiaCRM\Utils\OutputUtils;

class PDF_CertificatesReport extends ChurchInfoReport
{
    // Constructor
    public function __construct()
    {
        parent::__construct('P', 'mm', $this->paperFormat);
        $this->leftX = 10;
        $this->SetFont('Times', '', 10);
        $this->SetMargins(15, 25);

        $this->SetAutoPageBreak(true, 25);
    }

    public function AddPage($orientation = '', $format = '')
    {
        global $fr_title, $fr_description, $curY;

        parent::AddPage($orientation, $format);

        $this->SetFont('Times', 'B', 16);
        $this->Write(8, OutputUtils::translate_text_fpdf($fr_title)."\n");
        $curY += 8;
        $this->Write(8, OutputUtils::translate_text_fpdf($fr_description)."\n\n");
        $curY += 8;
        $this->SetFont('Times', 'B', 36);
        $this->Write(8, OutputUtils::translate_text_fpdf(_('Certificate of Ownership'))."\n\n");
        $curY += 8;
        $this->SetFont('Times', '', 10);
    }
}
