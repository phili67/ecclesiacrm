<?php
/*******************************************************************************
*
*  filename    : GDPRListExport.php
*  last change : 2018-07-23 Philippe Logel
*  description : Creates a csv for a GDPR List

******************************************************************************/
require 'Include/Config.php';
require 'Include/Functions.php';

use EcclesiaCRM\NoteQuery;
use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\Person;
use EcclesiaCRM\FamilyQuery;
use EcclesiaCRM\Map\PersonTableMap;
use EcclesiaCRM\Map\FamilyTableMap;
use EcclesiaCRM\Map\NoteTableMap;
use Propel\Runtime\ActiveQuery\Criteria;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Utils\InputUtils;

if (!($_SESSION['user']->isGdrpDpoEnabled())) {
  Redirect('Menu.php');
  exit;
}

$notes = NoteQuery::Create()
      ->filterByPerId(array('min' => 2))
      ->filterByEnteredBy(array('min' => 2))
      ->addJoin(NoteTableMap::COL_NTE_ENTEREDBY,PersonTableMap::COL_PER_ID,Criteria::LEFT_JOIN)
      ->addAsColumn('editedByTitle',PersonTableMap::COL_PER_TITLE)
      ->addAsColumn('editedByLastName',PersonTableMap::COL_PER_LASTNAME)
      ->addAsColumn('editedByMiddleName',PersonTableMap::COL_PER_MIDDLENAME)
      ->addAsColumn('editedByFirstName',PersonTableMap::COL_PER_FIRSTNAME)
      ->addAsColumn('Deactivated',PersonTableMap::COL_PER_DATEDEACTIVATED)
      ->find();

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


fputcsv($out, [InputUtils::translate_special_charset(gettext("Full Name"),$sCSVExportCharset),
  InputUtils::translate_special_charset(gettext("Title"),$sCSVExportCharset),
  InputUtils::translate_special_charset(gettext("Text"),$sCSVExportCharset),
  InputUtils::translate_special_charset(gettext("Type"),$sCSVExportCharset),
  InputUtils::translate_special_charset(gettext("Date Entered"),$sCSVExportCharset),
  InputUtils::translate_special_charset(gettext("Date Last Edited"),$sCSVExportCharset),
  InputUtils::translate_special_charset(gettext("Edited By Name"),$sCSVExportCharset),
  InputUtils::translate_special_charset(gettext("Deactivated"),$sCSVExportCharset) ], $delimiter);

// only the unday groups
                    
 foreach ($notes as $note) {
    $person = PersonQuery::Create()->findOneById($note->getPerId());
 
    fputcsv($out, [
            InputUtils::translate_special_charset($note->getPerson()->getFullName(),$sCSVExportCharset),
            InputUtils::translate_special_charset($note->getTitle(),$sCSVExportCharset),
            InputUtils::translate_special_charset($note->getText(),$sCSVExportCharset),
            InputUtils::translate_special_charset($note->getType(),$sCSVExportCharset),
            (!empty($note->getDateEntered()))?$note->getDateEntered()->format(SystemConfig::getValue('sDateFormatLong').' H:i'):"", 
            (!empty($note->getDateLastEdited()))?$note->getDateLastEdited()->format(SystemConfig::getValue('sDateFormatLong').' H:i'):"",
            InputUtils::translate_special_charset($note->getEditedByLastName()." ".$note->getEditedByFirstName(),
            $sCSVExportCharset).' '.InputUtils::translate_special_charset($Address2,$sCSVExportCharset).' '.InputUtils::translate_special_charset($city,$sCSVExportCharset).' '.InputUtils::translate_special_charset($state,$sCSVExportCharset).' '.$zip,
            (!is_null($note->getDeactivated()))?gettext("Yes"):gettext("No")
            ], $delimiter);
 
 
 }


fclose($out);
