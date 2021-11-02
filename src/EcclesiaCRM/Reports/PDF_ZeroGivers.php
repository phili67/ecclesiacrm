<?php

namespace EcclesiaCRM\Reports;

use EcclesiaCRM\Reports\ChurchInfoReportTCPDF;
use EcclesiaCRM\Utils\OutputUtils;
use EcclesiaCRM\dto\SystemConfig;

class PDF_ZeroGivers extends ChurchInfoReportTCPDF
{
    protected $letterhead;
    protected $sDateStart;
    protected $sDateEnd;
    protected $remittance;

    // Constructor
    public function __construct($letterhead, $sDateStart, $sDateEnd, $remittance)
    {
        parent::__construct('P', 'mm', $this->paperFormat);

        $this->letterhead = $letterhead;
        $this->sDateStart = $sDateStart;
        $this->sDateEnd = $sDateEnd;
        $this->remittance = $remittance;

        $this->SetFont('Times', '', 10);
        $this->SetMargins(20, 20);

        $this->SetAutoPageBreak(false);
    }

    public function StartNewPage($fam_ID, $fam_Name, $fam_Address1, $fam_Address2, $fam_City, $fam_State, $fam_Zip, $fam_Country)
    {
        $curY = $this->StartLetterPage($fam_ID, $fam_Name, $fam_Address1, $fam_Address2, $fam_City, $fam_State, $fam_Zip, $fam_Country, $this->letterhead);
        $curY += 2 * SystemConfig::getValue('incrementY');
        if ($this->sDateStart == $this->sDateEnd) {
            $DateString = OutputUtils::FormatDate($this->sDateStart);
        } else {
            $DateString = OutputUtils::FormatDate($this->sDateStart).' - '.OutputUtils::FormatDate($this->sDateEnd);
        }

        $blurb = SystemConfig::getValue('sZeroGivers').' '.$DateString;//.' '.SystemConfig::getValue('sZeroGivers');
        $this->WriteAt(SystemConfig::getValue('leftX'), $curY, $blurb);
        $curY += 30 * SystemConfig::getValue('incrementY');

        return $curY;
    }

    public function FinishPage($curY, $fam_ID, $fam_Name, $fam_Address1, $fam_Address2, $fam_City, $fam_State, $fam_Zip, $fam_Country)
    {
        $curY += 2 * SystemConfig::getValue('incrementY');
        $blurb = SystemConfig::getValue('sZeroGivers2');
        $this->WriteAt(SystemConfig::getValue('leftX'), $curY, $blurb);
        $curY += 3 * SystemConfig::getValue('incrementY');
        $blurb = SystemConfig::getValue('sZeroGivers3');
        $this->WriteAt(SystemConfig::getValue('leftX'), $curY, $blurb);
        $curY += 3 * SystemConfig::getValue('incrementY');
        $this->WriteAt(SystemConfig::getValue('leftX'), $curY, SystemConfig::getValue('sConfirmSincerely').',');
        $curY += 4 * SystemConfig::getValue('incrementY');
        $this->WriteAt(SystemConfig::getValue('leftX'), $curY, SystemConfig::getValue('sTaxSigner'));
    }
}
