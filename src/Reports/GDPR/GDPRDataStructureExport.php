<?php
/*******************************************************************************
*
*  filename    : GDPRListExport.php
*  last change : 2018-07-23 Philippe Logel
*  description : Creates a csv for a GDPR List

******************************************************************************/
require '../../Include/Config.php';
require '../../Include/Functions.php';

use EcclesiaCRM\PersonCustomMasterQuery;
use EcclesiaCRM\FamilyCustomMasterQuery;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\ListOptionQuery;
use EcclesiaCRM\GdprInfoQuery;
use EcclesiaCRM\PastoralCareTypeQuery;
use EcclesiaCRM\PropertyQuery;
use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\utils\RedirectUtils;
use EcclesiaCRM\SessionUser;


if (!(SessionUser::getUser()->isGdrpDpoEnabled())) {
  RedirectUtils::Redirect('v2/dashboard');
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

$delimiter = SessionUser::getUser()->CSVExportDelemiter();
$charset   = SessionUser::getUser()->CSVExportCharset();

header('Pragma: no-cache');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Content-Description: File Transfer');
header('Content-Type: text/csv;charset='.$charset);
header('Content-Disposition: attachment; filename=GDPRList-'.date(SystemConfig::getValue("sDateFilenameFormat")).'.csv');
header('Content-Transfer-Encoding: binary');

$out = fopen('php://output', 'w');

//add BOM to fix UTF-8 in Excel 2016 but not under, so the problem is solved with the charset variable
if ($charset == "UTF-8") {
    fputs($out, $bom =(chr(0xEF) . chr(0xBB) . chr(0xBF)));
}

fputcsv($out, [InputUtils::translate_special_charset(_("Informations"),$charset),
  InputUtils::translate_special_charset(_("For"),$charset),
  InputUtils::translate_special_charset(_("Type"),$charset),
  InputUtils::translate_special_charset(_("Comment"),$charset)], $delimiter);

foreach ($personInfos as $personInfo) {
          $dataType = ListOptionQuery::Create()
            ->filterByOptionId($personInfo->getTypeId())
            ->findOneById(4);

   fputcsv($out, [
            InputUtils::translate_special_charset($personInfo->getName(),$charset),
            InputUtils::translate_special_charset(_("Person"),$charset),
            InputUtils::translate_special_charset(_($dataType->getOptionName()),$charset),
            InputUtils::translate_special_charset($personInfo->getComment(),$charset)
          ], $delimiter);
}

foreach ($personCustMasts as $personCustMast) {
          $dataType = ListOptionQuery::Create()
            ->filterByOptionId($personCustMast->getTypeId())
            ->findOneById(4);

   fputcsv($out, [
            InputUtils::translate_special_charset($personCustMast->getCustomName(),$charset),
            InputUtils::translate_special_charset(_("Custom Person"),$charset),
            InputUtils::translate_special_charset(_($dataType->getOptionName()),$charset),
            InputUtils::translate_special_charset($personCustMast->getCustomComment(),$charset)
          ], $delimiter);
}

foreach ($personProperties as $personProperty) {
          $dataType = ListOptionQuery::Create()
            ->filterByOptionId($personProperty->getProPrtId())
            ->findOneById(4);

   fputcsv($out, [
            InputUtils::translate_special_charset($personProperty->getProName()." (".$personProperty->getProDescription().")",$charset),
            InputUtils::translate_special_charset(_("Person Property"),$charset),
            InputUtils::translate_special_charset(_($dataType->getOptionName()),$charset),
            InputUtils::translate_special_charset($personProperty->getProComment(),$charset)
          ], $delimiter);
}

foreach ($familyInfos as $familyInfo) {
          $dataType = ListOptionQuery::Create()
            ->filterByOptionId($familyInfo->getTypeId())
            ->findOneById(4);

   fputcsv($out, [
            InputUtils::translate_special_charset(_($familyInfo->getName()),$charset),
            InputUtils::translate_special_charset(_("Family"),$charset),
            InputUtils::translate_special_charset(_($dataType->getOptionName()),$charset),
            InputUtils::translate_special_charset($familyInfo->getComment(),$charset)
          ], $delimiter);
}

foreach ($familyCustMasts as $familyCustMast) {
          $dataType = ListOptionQuery::Create()
            ->filterByOptionId($familyCustMast->getTypeId())
            ->findOneById(4);

   fputcsv($out, [
            InputUtils::translate_special_charset($familyCustMast->getCustomName(),$charset),
            InputUtils::translate_special_charset(_("Custom Family"),$charset),
            InputUtils::translate_special_charset(_($dataType->getOptionName()),$charset),
            InputUtils::translate_special_charset($familyCustMast->getCustomComment(),$charset)
          ], $delimiter);
}

foreach ($familyProperties as $familyProperty) {
          $dataType = ListOptionQuery::Create()
            ->filterByOptionId($familyProperty->getProPrtId())
            ->findOneById(4);

   fputcsv($out, [
            InputUtils::translate_special_charset($familyProperty->getProName()." (".$familyProperty->getProDescription().")",$charset),
            InputUtils::translate_special_charset(_("Family Property"),$charset),
            InputUtils::translate_special_charset(_($dataType->getOptionName()),$charset),
            InputUtils::translate_special_charset($familyProperty->getProComment(),$charset)
          ], $delimiter);
}

foreach ($pastoralCareTypes as $pastoralCareType) {
   fputcsv($out, [
            InputUtils::translate_special_charset($pastoralCareType->getTitle(),$charset),
            InputUtils::translate_special_charset(_("Pastoral Care"),$charset),
            InputUtils::translate_special_charset(_("Text Field (100 char)"),$charset),
            InputUtils::translate_special_charset($pastoralCareType->getComment(),$charset)
          ], $delimiter);
}


fclose($out);
