<?php

require '../Include/Config.php';
require '../Include/Functions.php';

use EcclesiaCRM\Service\SundaySchoolService;
use EcclesiaCRM\Service\PersonService;
use EcclesiaCRM\GroupQuery;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\SessionUser;


$personService = new PersonService();

$sundaySchoolService = new SundaySchoolService();
$groups = GroupQuery::create()->filterByActive(true)->filterByIncludeInEmailExport(true)->find();

$delimiter = SessionUser::getUser()->CSVExportDelemiter();
$charset   = SessionUser::getUser()->CSVExportCharset();

$colNames = [];
array_push($colNames, 'CRM ID');
array_push($colNames, InputUtils::translate_special_charset(_('First Name'),$charset));
array_push($colNames, InputUtils::translate_special_charset(_('Last Name'),$charset));
array_push($colNames, InputUtils::translate_special_charset(_('Email'),$charset));
foreach ($groups as $group) {
    array_push($colNames, $group->getName());
}

$sundaySchoolsParents = [];
foreach ($groups as $group) {
    if ($group->isSundaySchool()) {
        $sundaySchoolParents = [];
        $kids = $sundaySchoolService->getKidsFullDetails($group->getId());
        $parentIds = [];
        foreach ($kids as $kid) {
            if ($kid['dadId'] != '') {
                array_push($parentIds, $kid['dadId']);
            }
            if ($kid['momId'] != '') {
                array_push($parentIds, $kid['momId']);
            }
        }
        $sundaySchoolsParents[$group->getId()] = $parentIds;
    }
}

header('Pragma: no-cache');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Content-Description: File Transfer');
header('Content-Type: text/csv;charset='.$charset);
header('Content-Disposition: attachment; filename=EmailExport-'.date(SystemConfig::getValue("sDateFilenameFormat")).'.csv');
header('Content-Transfer-Encoding: binary');

$out = fopen('php://output', 'w');

//add BOM to fix UTF-8 in Excel 2016 but not under, so the problem is solved with the charset variable
if ($charset == "UTF-8") {
   fputs($out, $bom =(chr(0xEF) . chr(0xBB) . chr(0xBF)));
}

fputcsv($out, $colNames, $delimiter);
foreach ($personService->getPeopleEmailsAndGroups() as $person) {
    $row = [];
    array_push($row, $person['id']);
    array_push($row, InputUtils::translate_special_charset($person['firstName'],$charset));
    array_push($row, InputUtils::translate_special_charset($person['lastName'],$charset));
    array_push($row, $person['email']);
    foreach ($groups as $group) {
        $groupRole = $person[$group->getName()];
        if ($groupRole == '' && $group->isSundaySchool()) {
            if (in_array($person['id'], $sundaySchoolsParents[$group->getId()])) {
                $groupRole = InputUtils::translate_special_charset(_("Parent"),$charset);
            }
        }
        array_push($row, $groupRole);
    }
    fputcsv($out, $row, $delimiter);
}
fclose($out);
