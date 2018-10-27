<?php
/*******************************************************************************
*
*  filename    : GDPRListExport.php
*  last change : 2018-07-23 Philippe Logel
*  description : Creates a csv for a GDPR List

******************************************************************************/
require 'Include/Config.php';
require 'Include/Functions.php';

use EcclesiaCRM\PersonCustomMasterQuery;
use EcclesiaCRM\FamilyCustomMasterQuery;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\ListOptionQuery;
use EcclesiaCRM\GdprInfoQuery;
use EcclesiaCRM\PastoralCareTypeQuery;
use EcclesiaCRM\PropertyQuery;
use EcclesiaCRM\Utils\InputUtils;

if (!($_SESSION['user']->isGdrpDpoEnabled())) {
  Redirect('Menu.php');
  exit;
}


// for persons
$personCustMasts = PersonCustomMasterQuery::Create()
      ->orderByCustomName()
      ->find();
      
$personInfos = GdprInfoQuery::Create()->filterByAbout('Person')->find();

$personProperties = PropertyQuery::Create()->filterByProClass('p')->find();

// for families
$familyCustMasts = FamilyCustomMasterQuery::Create()
      ->orderByCustomName()
      ->find();

$familyInfos = GdprInfoQuery::Create()->filterByAbout('Family')->find();

$familyProperties = PropertyQuery::Create()->filterByProClass('f')->find();

// for pastoral care
$pastoralCareTypes = PastoralCareTypeQuery::Create()->find();

$res = [];

header('Pragma: no-cache');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Content-Description: File Transfer');
header('Content-Type: text/csv;charset='.$sCSVExportCharset);
header('Content-Disposition: attachment; filename=GDPRList-'.date(SystemConfig::getValue("sDateFilenameFormat")).'.csv');
header('Content-Transfer-Encoding: binary');

$delimiter = $sCSVExportDelemiter;

$out = fopen('php://output', 'w');

//add BOM to fix UTF-8 in Excel 2016 but not under, so the problem is solved with the sCSVExportCharset variable
if ($sCSVExportCharset == "UTF-8") {
    fputs($out, $bom =(chr(0xEF) . chr(0xBB) . chr(0xBF)));
}

fputcsv($out, [InputUtils::translate_special_charset(gettext("Informations"),$sCSVExportCharset),
  InputUtils::translate_special_charset(gettext("For"),$sCSVExportCharset),
  InputUtils::translate_special_charset(gettext("Type"),$sCSVExportCharset),
  InputUtils::translate_special_charset(gettext("Comment"),$sCSVExportCharset)], $delimiter);

foreach ($personInfos as $personInfo) {
          $dataType = ListOptionQuery::Create()
            ->filterByOptionId($personInfo->getTypeId())
            ->findOneById(4);

   fputcsv($out, [
            InputUtils::translate_special_charset($personInfo->getName(),$sCSVExportCharset),
            InputUtils::translate_special_charset(gettext("Person"),$sCSVExportCharset),
            InputUtils::translate_special_charset(gettext($dataType->getOptionName()),$sCSVExportCharset),
            InputUtils::translate_special_charset($personInfo->getComment(),$sCSVExportCharset)
          ], $delimiter);
}

foreach ($personCustMasts as $personCustMast) { 
          $dataType = ListOptionQuery::Create()
            ->filterByOptionId($personCustMast->getTypeId())
            ->findOneById(4);

   fputcsv($out, [
            InputUtils::translate_special_charset($personCustMast->getCustomName(),$sCSVExportCharset),
            InputUtils::translate_special_charset(gettext("Custom Person"),$sCSVExportCharset),
            InputUtils::translate_special_charset(gettext($dataType->getOptionName()),$sCSVExportCharset),
            InputUtils::translate_special_charset($personCustMast->getCustomComment(),$sCSVExportCharset)
          ], $delimiter);
}

foreach ($personProperties as $personProperty) { 
          $dataType = ListOptionQuery::Create()
            ->filterByOptionId($personProperty->getProPrtId())
            ->findOneById(4);

   fputcsv($out, [
            InputUtils::translate_special_charset($personProperty->getProName()." (".$personProperty->getProDescription().")",$sCSVExportCharset),
            InputUtils::translate_special_charset(gettext("Person Property"),$sCSVExportCharset),
            InputUtils::translate_special_charset(gettext($dataType->getOptionName()),$sCSVExportCharset),
            InputUtils::translate_special_charset($personProperty->getProComment(),$sCSVExportCharset)
          ], $delimiter);
}

foreach ($familyInfos as $familyInfo) {
          $dataType = ListOptionQuery::Create()
            ->filterByOptionId($familyInfo->getTypeId())
            ->findOneById(4);

   fputcsv($out, [
            InputUtils::translate_special_charset(gettext($familyInfo->getName()),$sCSVExportCharset),
            InputUtils::translate_special_charset(gettext("Family"),$sCSVExportCharset),
            InputUtils::translate_special_charset(gettext($dataType->getOptionName()),$sCSVExportCharset),
            InputUtils::translate_special_charset($familyInfo->getComment(),$sCSVExportCharset)
          ], $delimiter);
}

foreach ($familyCustMasts as $familyCustMast) { 
          $dataType = ListOptionQuery::Create()
            ->filterByOptionId($familyCustMast->getTypeId())
            ->findOneById(4);

   fputcsv($out, [
            InputUtils::translate_special_charset($familyCustMast->getCustomName(),$sCSVExportCharset),
            InputUtils::translate_special_charset(gettext("Custom Family"),$sCSVExportCharset),
            InputUtils::translate_special_charset(gettext($dataType->getOptionName()),$sCSVExportCharset),
            InputUtils::translate_special_charset($familyCustMast->getCustomComment(),$sCSVExportCharset)
          ], $delimiter);
}

foreach ($familyProperties as $familyProperty) { 
          $dataType = ListOptionQuery::Create()
            ->filterByOptionId($familyProperty->getProPrtId())
            ->findOneById(4);

   fputcsv($out, [
            InputUtils::translate_special_charset($familyProperty->getProName()." (".$familyProperty->getProDescription().")",$sCSVExportCharset),
            InputUtils::translate_special_charset(gettext("Family Property"),$sCSVExportCharset),
            InputUtils::translate_special_charset(gettext($dataType->getOptionName()),$sCSVExportCharset),
            InputUtils::translate_special_charset($familyProperty->getProComment(),$sCSVExportCharset)
          ], $delimiter);
}

foreach ($pastoralCareTypes as $pastoralCareType) { 
   fputcsv($out, [
            InputUtils::translate_special_charset($pastoralCareType->getTitle(),$sCSVExportCharset),
            InputUtils::translate_special_charset(gettext("Pastoral Care"),$sCSVExportCharset),
            InputUtils::translate_special_charset(gettext("Text Field (100 char)"),$sCSVExportCharset),
            InputUtils::translate_special_charset($pastoralCareType->getComment(),$sCSVExportCharset)
          ], $delimiter);
}


fclose($out);
