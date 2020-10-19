<?php
/*******************************************************************************
 *
 *  filename    : PrintView.php
 *  last change : 2003-01-29
 *
 *  http://www.ecclesiacrm.com/
 *  Copyright 2001-2003 Phillip Hullquist, Deane Barker, Chris Gebhardt
 *  update 2017 : Philippe Logel
 *
 ******************************************************************************/

// Include the function library
require 'Include/Config.php';
require 'Include/Functions.php';

use Propel\Runtime\Propel;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\Utils\OutputUtils;
use EcclesiaCRM\Utils\MiscUtils;
use EcclesiaCRM\Utils\RedirectUtils;
use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\PersonCustomMasterQuery;
use EcclesiaCRM\PersonCustomQuery;
use EcclesiaCRM\Person2group2roleP2g2rQuery;
use EcclesiaCRM\Map\Person2group2roleP2g2rTableMap;
use EcclesiaCRM\Map\Record2propertyR2pTableMap;
use EcclesiaCRM\Map\PropertyTableMap;
use EcclesiaCRM\Map\PropertyTypeTableMap;
use EcclesiaCRM\Map\GroupTableMap;
use EcclesiaCRM\Map\ListOptionTableMap;
use EcclesiaCRM\Map\PersonTableMap;
use EcclesiaCRM\Map\NoteTableMap;
use Propel\Runtime\ActiveQuery\Criteria;


use EcclesiaCRM\GroupPropMasterQuery;
use EcclesiaCRM\Record2propertyR2pQuery;
use EcclesiaCRM\NoteQuery;


// Get the person ID from the querystring
$iPersonID = InputUtils::LegacyFilterInput($_GET['PersonID'], 'int');

if ( !(SessionUser::getUser()->isEditRecordsEnabled() ||
    (SessionUser::getUser()->isEditSelfEnabled() && $iPersonID == SessionUser::getUser()->getPersonId()) ) ) {
  RedirectUtils::Redirect('v2/dashboard');
  exit;
}

$connection = Propel::getConnection();

// Get this person
$sSQL = 'SELECT a.*, family_fam.*, cls.lst_OptionName AS sClassName, fmr.lst_OptionName AS sFamRole, b.per_FirstName AS EnteredFirstName,
        b.Per_LastName AS EnteredLastName, c.per_FirstName AS EditedFirstName, c.per_LastName AS EditedLastName
      FROM person_per a
      LEFT JOIN family_fam ON a.per_fam_ID = family_fam.fam_ID
      LEFT JOIN list_lst cls ON a.per_cls_ID = cls.lst_OptionID AND cls.lst_ID = 1
      LEFT JOIN list_lst fmr ON a.per_fmr_ID = fmr.lst_OptionID AND fmr.lst_ID = 2
      LEFT JOIN person_per b ON a.per_EnteredBy = b.per_ID
      LEFT JOIN person_per c ON a.per_EditedBy = c.per_ID
      WHERE a.per_ID = '.$iPersonID;

$statement = $connection->prepare($sSQL);
$statement->execute();
$prpPerson = $statement->fetch(PDO::FETCH_BOTH);
extract($prpPerson);


// Save for later
$sWorkEmail = trim($per_WorkEmail);

// Get the list of custom person fields
$ormPersonCustomFields = PersonCustomMasterQuery::Create()
                     ->orderByCustomOrder()
                     ->find()
                     ->toArray();

$numCustomFields = count($ormPersonCustomFields);

// Get the custom field data for this person.
$rawQry =  PersonCustomQuery::create();
foreach ($ormPersonCustomFields as $customfield ) {
   $rawQry->withColumn($customfield['CustomField']);
}

if (!is_null($rawQry->findOneByPerId($iPersonID))) {
  $aCustomData = $rawQry->findOneByPerId($iPersonID)->toArray();
}

// Get the notes for this person
$ormNotes = NoteQuery::Create()
       ->addAlias('a', PersonTableMap::TABLE_NAME)
       ->addJoin(NoteTableMap::COL_NTE_ENTEREDBY,PersonTableMap::alias('a', PersonTableMap::COL_PER_ID),Criteria::LEFT_JOIN)
       ->addAlias('b', PersonTableMap::TABLE_NAME)
       ->addJoin(NoteTableMap::COL_NTE_EDITEDBY,PersonTableMap::alias('b', PersonTableMap::COL_PER_ID),Criteria::LEFT_JOIN)
         ->filterByPerId($iPersonID)
       ->_and()
         ->filterByPrivate(array(0,SessionUser::getUser()->getPersonId()))
       ->find();

// Get the Groups this Person is assigned to
$ormAssignedGroups = Person2group2roleP2g2rQuery::Create()
       ->addJoin(Person2group2roleP2g2rTableMap::COL_P2G2R_GRP_ID,GroupTableMap::COL_GRP_ID,Criteria::LEFT_JOIN)
       ->addMultipleJoin(array(array(Person2group2roleP2g2rTableMap::COL_P2G2R_RLE_ID,ListOptionTableMap::COL_LST_OPTIONID),array(GroupTableMap::COL_GRP_ROLELISTID,ListOptionTableMap::COL_LST_ID)),Criteria::LEFT_JOIN)
       ->add(ListOptionTableMap::COL_LST_OPTIONNAME, null, Criteria::ISNOTNULL)
       ->Where(Person2group2roleP2g2rTableMap::COL_P2G2R_PER_ID.' = '.$iPersonID.' ORDER BY grp_Name')
       ->addAsColumn('roleName',ListOptionTableMap::COL_LST_OPTIONNAME)
       ->addAsColumn('groupName',GroupTableMap::COL_GRP_NAME)
       ->addAsColumn('groupID',GroupTableMap::COL_GRP_ID)
       ->addAsColumn('hasSpecialProps',GroupTableMap::COL_GRP_HASSPECIALPROPS)
       ->find();


// Get the Properties assigned to this Person
$ormAssignedProperties = Record2propertyR2pQuery::Create()
                            ->addJoin(Record2propertyR2pTableMap::COL_R2P_PRO_ID,PropertyTableMap::COL_PRO_ID,Criteria::LEFT_JOIN)
                            ->addJoin(PropertyTableMap::COL_PRO_PRT_ID,PropertyTypeTableMap::COL_PRT_ID,Criteria::LEFT_JOIN)
                            ->addAsColumn('ProName',PropertyTableMap::COL_PRO_NAME)
                            ->addAsColumn('ProId',PropertyTableMap::COL_PRO_ID)
                            ->addAsColumn('ProPrtId',PropertyTableMap::COL_PRO_PRT_ID)
                            ->addAsColumn('ProPrompt',PropertyTableMap::COL_PRO_PROMPT)
                            ->addAsColumn('ProName',PropertyTableMap::COL_PRO_NAME)
                            ->addAsColumn('ProTypeName',PropertyTypeTableMap::COL_PRT_NAME)
                            ->where(PropertyTableMap::COL_PRO_CLASS."='p'")
                            ->addAscendingOrderByColumn('ProName')
                            ->addAscendingOrderByColumn('ProTypeName')
                            ->findByR2pRecordId($iPersonID);

// Get Field Security List Matrix
/*$ormSecurityGrp = ListOptionQuery::Create()
              ->orderByOptionSequence()
              ->findById(5);*/


// Format the BirthDate
$dBirthDate = OutputUtils::FormatBirthDate($per_BirthYear, $per_BirthMonth, $per_BirthDay, '-', $per_Flags);
//if ($per_BirthMonth > 0 && $per_BirthDay > 0)
//{
//  $dBirthDate = $per_BirthMonth . "/" . $per_BirthDay;
//  if (is_numeric($per_BirthYear))
//  {
//    $dBirthDate .= "/" . $per_BirthYear;
//  }
//}
//elseif (is_numeric($per_BirthYear))
//{
//  $dBirthDate = $per_BirthYear;
//}
//else
//{
//  $dBirthDate = "";
//}

// Assign the values locally, after selecting whether to display the family or person information

MiscUtils::SelectWhichAddress($sAddress1, $sAddress2, $per_Address1, $per_Address2, $fam_Address1, $fam_Address2, false);
$sCity = MiscUtils::SelectWhichInfo($per_City, $fam_City, false);
$sState = MiscUtils::SelectWhichInfo($per_State, $fam_State, false);
$sZip = MiscUtils::SelectWhichInfo($per_Zip, $fam_Zip, false);
$sCountry = MiscUtils::SelectWhichInfo($per_Country, $fam_Country, false);

$sHomePhone = MiscUtils::SelectWhichInfo(MiscUtils::ExpandPhoneNumber($per_HomePhone, $sCountry, $dummy),
  MiscUtils::ExpandPhoneNumber($fam_HomePhone, $fam_Country, $dummy), false);
$sWorkPhone = MiscUtils::SelectWhichInfo(MiscUtils::ExpandPhoneNumber($per_WorkPhone, $sCountry, $dummy),
  MiscUtils::ExpandPhoneNumber($fam_WorkPhone, $fam_Country, $dummy), false);
$sCellPhone = MiscUtils::SelectWhichInfo(MiscUtils::ExpandPhoneNumber($per_CellPhone, $sCountry, $dummy),
  MiscUtils::ExpandPhoneNumber($fam_CellPhone, $fam_Country, $dummy), false);

$sUnformattedEmail = MiscUtils::SelectWhichInfo($per_Email, $fam_Email, false);


$iFamilyID = $fam_ID;

if ($fam_ID) {
    //Get the family members for this family
    $sSQLFamilyMembers = 'SELECT per_ID, per_DateDeactivated, per_Title, per_FirstName, per_LastName, per_Suffix, per_Gender,
    per_BirthMonth, per_BirthDay, per_BirthYear, per_Flags, cls.lst_OptionName AS sClassName,
    fmr.lst_OptionName AS sFamRole
    FROM person_per
    LEFT JOIN list_lst cls ON per_cls_ID = cls.lst_OptionID AND cls.lst_ID = 1
    LEFT JOIN list_lst fmr ON per_fmr_ID = fmr.lst_OptionID AND fmr.lst_ID = 2
    WHERE per_fam_ID = '.$iFamilyID.' ORDER BY fmr.lst_OptionSequence';
}

// Set the page title and include HTML header
$sPageTitle = _('Printable View');
$iTableSpacerWidth = 10;
require 'Include/Header-Short.php';
?>

<table width="400">
  <tr>
    <td>
        <p class="ShadedBox">

        <?php

        $personSheet = PersonQuery::create()->findPk($per_ID);

        if ($personSheet->getDateDeactivated() != null) {
          RedirectUtils::Redirect('members/404.php?type=Person');
        }


        if ($personSheet) {
            $imgName = str_replace(SystemURLs::getDocumentRoot(), "", $personSheet->getPhoto()->getPhotoURI());
        ?>
          <table>
            <tr>
               <td  style="padding:5px;">
                 <img src=<?= $imgName ?> width=110/>
               </td>
               <td>
                 <b><font size="4"><?= $personSheet->getFullName() ?></font></b><br>
               </td>
            </tr>
          </table>
        <?php
        } else {
        ?>
            <b><font size="4"><?= $personSheet->getFullName() ?></font></b><br>
        <?php
        }

        // Print the name and address header
        ?>
        <font size="3">
        <?php
        if ($sAddress1 != '') {
            echo $sAddress1.'<br>';
        }
        if ($sAddress2 != '') {
            echo $sAddress2.'<br>';
        }
        if ($sCity != '') {
            echo $sCity.', ';
        }
        if ($sState != '') {
            echo $sState;
        }

        // bevand10 2012-04-28 Replace space with &nbsp; in zip/postcodes, to ensure they do not wrap on output.
        if ($sZip != '') {
            echo ' '.str_replace(' ', '&nbsp;', trim($sZip));
        }

        if ($sCountry != '') {
        ?>
            <br><?= $sCountry ?>
        <?php
        }
        ?>
        </font>
      </p>
    </td>
  </tr>
</table>
<BR/>

<table border="0" width="100%" cellspacing="0" cellpadding="0">
<tr>
  <td width="33%" valign="top" align="left">
    <table cellspacing="1" cellpadding="4">
    <tr>
      <td class="LabelColumn"><?= _('Home Phone') ?>:</td>
      <td width="<?= $iTableSpacerWidth ?>"></td>
      <td class="TextColumn"><?= $sHomePhone ?>&nbsp;</td>
    </tr>
    <tr>
      <td class="LabelColumn"><?= _('Work Phone') ?>:</td>
      <td width="<?= $iTableSpacerWidth ?>"></td>
      <td class="TextColumn"><?= $sWorkPhone ?>&nbsp;</td>
    </tr>
    <tr>
      <td class="LabelColumn"><?= _('Mobile Phone') ?>:</td>
      <td width="<?= $iTableSpacerWidth ?>"></td>
      <td class="TextColumn"><?= $sCellPhone ?>&nbsp;</td>
    </tr>
<?php
  $numColumn1Fields = ceil((float)$numCustomFields / 3.0);
  $numColumn2Fields = $numColumn1Fields;
  $numColumn3Fields = $numCustomFields - $numColumn1Fields*2;

  for ($i = 0 ; $i < $numColumn1Fields ; $i++) {
    if (OutputUtils::securityFilter($ormPersonCustomFields[$i]['CustomFieldSec'])) {
        $currentData = trim($aCustomData[$ormPersonCustomFields[$i]['CustomField']]);

        if ($currentData != '') {
          if ($ormPersonCustomFields[$i]['TypeId'] == 11) {
            $custom_Special = $sPhoneCountry;
          } else {
            $custom_Special = $ormPersonCustomFields[$i]['CustomSpecial'];
          }
?>
      <tr>
        <td class="LabelColumn"><?= $ormPersonCustomFields[$i]['CustomName'] ?></td>
        <td width="<?= $iTableSpacerWidth ?>"></td>
        <td class="TextColumn"><?= OutputUtils::displayCustomField($ormPersonCustomFields[$i]['TypeId'], $currentData, $custom_Special,false)?></td>
      </tr>
<?php
        } else {
?>
      <tr>
        <td class="LabelColumn"><?= $ormPersonCustomFields[$i]['CustomName'] ?></td>
        <td width="<?= $iTableSpacerWidth ?>"></td>
        <td class="TextColumn"></td>
      </tr>
    <?php
        }
    }
  }
?>
    </table>
  </td>

  <td width="33%" valign="top" align="left">
    <table cellspacing="1" cellpadding="4">
    <tr>
      <td class="LabelColumn"><?= _('Gender') ?>:</td>
      <td width="<?= $iTableSpacerWidth ?>"></td>
      <td class="TextColumn">
        <?php
          switch (strtolower($per_Gender)) {case 1:echo _('Male');break; case 2: echo _('Female');break;}
        ?>
      </td>
    </tr>
    <tr>
      <td class="LabelColumn"><?= _('Birth Date') ?>:</td>
      <td width="<?= $iTableSpacerWidth ?>"></td>
      <td class="TextColumn"><?= $dBirthDate ?>&nbsp;</td>
    </tr>
    <tr>
      <td class="LabelColumn"><?= _('Family') ?>:</td>
      <td width="<?= $iTableSpacerWidth ?>"></td>
      <td class="TextColumn">
      <?php
        if ($fam_Name != '') {
          echo $fam_Name;
        } else {
          echo _('Unassigned');
        }
      ?>
      &nbsp;</td>
    </tr>
    <tr>
      <td class="LabelColumn"><?= _('Family Role') ?>:</td>
      <td width="<?= $iTableSpacerWidth ?>"></td>
      <td class="TextColumnWithBottomBorder">
      <?php
        if ($sFamRole != '') {
          echo $sFamRole;
        } else {
          echo _('Unassigned');
        }
      ?>&nbsp;
      </td>
    </tr>
<?php
  for ($i = $numColumn1Fields ; $i < $numColumn1Fields+$numColumn2Fields ; $i++) {
    if (OutputUtils::securityFilter($ormPersonCustomFields[$i]['CustomFieldSec'])) {
        $currentData = trim($aCustomData[$ormPersonCustomFields[$i]['CustomField']]);

        if ($currentData != '') {
          if ($ormPersonCustomFields[$i]['TypeId'] == 11) {
            $custom_Special = $sPhoneCountry;
          } else {
            $custom_Special = $ormPersonCustomFields[$i]['CustomSpecial'];
          }
?>
      <tr>
        <td class="LabelColumn"><?= $ormPersonCustomFields[$i]['CustomName'] ?></td>
        <td width="<?= $iTableSpacerWidth ?>"></td>
        <td class="TextColumn"><?= OutputUtils::displayCustomField($ormPersonCustomFields[$i]['TypeId'], $currentData, $custom_Special,false)?></td>
      </tr>
<?php
        } else {
?>
      <tr>
        <td class="LabelColumn"><?= $ormPersonCustomFields[$i]['CustomName'] ?></td>
        <td width="<?= $iTableSpacerWidth ?>"></td>
        <td class="TextColumn"></td>
      </tr>
    <?php
        }
    }
  }
?>
    </table>
  </td>
  <td width="33%" valign="top" align="left">
    <table cellspacing="1" cellpadding="4">
      <tr>
        <td class="LabelColumn"><?= _('Email') ?>:</td>
        <td width="<?= $iTableSpacerWidth ?>"></td>
        <td class="TextColumnWithBottomBorder"><?= $sUnformattedEmail ?>&nbsp;</td>
      </tr>
      <tr>
        <td class="LabelColumn"><?= _('Work / Other Email') ?>:</td>
        <td width="<?= $iTableSpacerWidth ?>"></td>
        <td class="TextColumnWithBottomBorder"><?= $sWorkEmail ?>&nbsp;</td>
      </tr>
      <tr>
        <td class="LabelColumn"><?= _('Membership Date') ?>:</td>
        <td width="<?= $iTableSpacerWidth ?>"></td>
        <td class="TextColumn"><?= OutputUtils::FormatDate($per_MembershipDate, false) ?>&nbsp;</td>
      </tr>
      <tr>
        <td class="LabelColumn"><?= _('Classification') ?>:</td>
        <td width="<?= $iTableSpacerWidth ?>"></td>
        <td class="TextColumnWithBottomBorder"><?= $sClassName ?>&nbsp;</td>
      </tr>
<?php
  for ($i = $numColumn1Fields+$numColumn2Fields ; $i < $numColumn1Fields+$numColumn2Fields+$numColumn3Fields ; $i++) {
    if (OutputUtils::securityFilter($ormPersonCustomFields[$i]['CustomFieldSec'])) {
        $currentData = trim($aCustomData[$ormPersonCustomFields[$i]['CustomField']]);

        if ($currentData != '') {
          if ($ormPersonCustomFields[$i]['TypeId'] == 11) {
            $custom_Special = $sPhoneCountry;
          } else {
            $custom_Special = $ormPersonCustomFields[$i]['CustomSpecial'];
          }
?>
      <tr>
        <td class="LabelColumn"><?= $ormPersonCustomFields[$i]['CustomName'] ?></td>
        <td width="<?= $iTableSpacerWidth ?>"></td>
        <td class="TextColumn"><?= OutputUtils::displayCustomField($ormPersonCustomFields[$i]['TypeId'], $currentData, $custom_Special,false)?></td>
      </tr>
<?php
        } else {
?>
      <tr>
        <td class="LabelColumn"><?= $ormPersonCustomFields[$i]['CustomName'] ?></td>
        <td width="<?= $iTableSpacerWidth ?>"></td>
        <td class="TextColumn"></td>
      </tr>
    <?php
        }
    }
  }
?>
      </table>
    </td>
</tr>
</table>
<br>

<?php
  if ($fam_ID) {
?>

<b><?= _('Family Members') ?>:</b>
<table cellpadding=5 cellspacing=0 width="100%">
  <tr class="TableHeader">
    <td><?= _('Name') ?></td>
    <td><?= _('Gender') ?></td>
    <td><?= _('Role') ?></td>
    <td><?= _('Age') ?></td>
  </tr>
<?php
    $sRowClass = 'RowColorA';

    // Loop through all the family members
    $statement = $connection->prepare($sSQLFamilyMembers);
    $statement->execute();

    while ($aRow = $statement->fetch(PDO::FETCH_BOTH)) {
        $per_BirthYear = '';
        $agr_Description = '';

        extract($aRow);

        if ($per_DateDeactivated != null)// GDRP, when a person is completely deactivated
          continue;

        // Alternate the row style
        $sRowClass = MiscUtils::AlternateRowStyle($sRowClass)

        // Display the family member
    ?>
    <tr class="<?= $sRowClass ?>">
      <td>
        <?= $per_FirstName.' '.$per_LastName ?>
        <br>
      </td>
      <td>
        <?php switch ($per_Gender) {case 1: echo _('Male'); break; case 2: echo _('Female'); break; default: echo ''; } ?>&nbsp;
      </td>
      <td>
        <?= $sFamRole ?>&nbsp;
      </td>
      <td data-birth-date="<?= $per_Flags == 1 ? '' : date_create($per_BirthYear.'-'.$per_BirthMonth.'-'.$per_BirthDay)->format('Y-m-d') ?>">
      </td>
    </tr>
  <?php
            }
  ?>
  </table>
<?php
  }
?>
<BR>
<b><?= _('Assigned Groups') ?>:</b>

<?php

//Initialize row shading
$sRowClass = 'RowColorA';

$sAssignedGroups = ',';

//Was anything returned?
if ($ormAssignedGroups->count() == 0) {
?>
  <p align"center"><?= _('No group assignments.') ?></p>
<?php
} else {
?>
  <table width="100%" cellpadding="4" cellspacing="0">
    <tr class="TableHeader">
      <td width="15%"><b><?= _('Group Name') ?></b>
      <td><b><?= _('Role') ?></b></td>
    </tr>
<?php
    //Loop through the rows
    foreach ($ormAssignedGroups as $ormAssignedGroup) {
        //Alternate the row style
        $sRowClass = MiscUtils::AlternateRowStyle($sRowClass);

        // DISPLAY THE ROW
?>
    <tr class="<?= $sRowClass ?>">
      <td><?= $ormAssignedGroup->getGroupName() ?></td>
      <td><?= _($ormAssignedGroup->getRoleName()) ?></td>
    </tr>
<?php
        // If this group has associated special properties, display those with values and prop_PersonDisplay flag set.
        if ($ormAssignedGroup->getHasSpecialProps()) {
            $firstRow = true;
            // Get the special properties for this group
            $ormPropLists = GroupPropMasterQuery::Create()->filterByPersonDisplay('true')->orderByPropId()->findByGroupId($ormAssignedGroup->getGroupId());

            $sSQL = 'SELECT * FROM groupprop_'.$ormAssignedGroup->getGroupId().' WHERE per_ID = '.$iPersonID;

            $statement = $connection->prepare($sSQL);
            $statement->execute();
            $aPersonProps = $statement->fetch( PDO::FETCH_BOTH );

            foreach ($ormPropLists as $ormPropList) {
                $currentData = trim($aPersonProps[$ormPropList->getField()]);
                if (strlen($currentData) > 0) {
                    // only create the properties table if it's actually going to be used
                    if ($firstRow) {
          ?>
      <tr>
         <td colspan="2">
            <table width="50%">
               <tr><td width="15%"></td>
               <td>
                 <table width="90%" cellspacing="0">
                    <tr class="TinyTableHeader">
                      <td><?= _('Property')?></td>
                      <td><?= _("Value") ?></td>
                    </tr>
                <?php
                        $firstRow = false;
                    }
                    $sRowClass = MiscUtils::AlternateRowStyle($sRowClass);
                    if ($type_ID == 11) {
                        $prop_Special = $sCountry;
                    }
                ?>
                    <tr class="<?= $sRowClass ?>">
                       <td><?= $ormPropList->getName() ?></td>
                       <td><?= OutputUtils::displayCustomField($ormPropList->getTypeId(), $currentData, $ormPropList->getSpecial()) ?></td>
                    </tr>
                <?php
                }
            }
            if (!$firstRow) {
        ?>
                </table>
              </td>
            </tr>
          </table>
        </td>
      </tr>
    <?php
            }
        }

        $sAssignedGroups .= $grp_ID.',';
    }
  ?>
    </table>
<?php
}
?>
<BR>
<b><?= _('Assigned Properties') ?>:</b>

<?php

//Initialize row shading
$sRowClass = 'RowColorA';

$sAssignedProperties = ',';

//Was anything returned?
if ($ormAssignedProperties->count() == 0) {
?>
    <p align"center"><?= _('No property assignments.') ?></p>
<?php
} else {
?>
    <table width="100%" cellpadding="4" cellspacing="0">
      <tr class="TableHeader">
        <td width="25%" valign="top"><b><?= _('Name') ?></b>
        <td valign="top"><b><?=_('Value') ?></td>
      </tr>
<?php
    foreach ($ormAssignedProperties as $ormAssignedProperty) {
        //Alternate the row style
        $sRowClass = MiscUtils::AlternateRowStyle($sRowClass);

        //Display the row
?>
      <tr class="<?= $sRowClass ?>">
        <td valign="top"><?= _($ormAssignedProperty->getProName()) ?>&nbsp;</td>
        <td valign="top"><?= $ormAssignedProperty->getR2pValue() ?>&nbsp;</td>
      </tr>
<?php
        $sAssignedProperties .= $ormAssignedProperty->getR2pId().',';
    }
?>
  </table>
<br>
<?php
}

if (SessionUser::getUser()->isNotesEnabled()) {
    // Loop through all the notes
    foreach ($ormNotes as $note) {
?>
      <p class="ShadedBox")><?= $note->getText() ?></p>
<?php
    if (!is_null($note->getDateEntered())) {
?>
      <span class="SmallText"><?= _('Entered:').(OutputUtils::FormatDate($note->getDateEntered()->format('Y-m-d H:i:s'), true)) ?></span><br>
<?php
        }

        if (strlen($note->getDateLastEdited())) {
?>
      <span class="SmallText"><?= _('Last Edited').(OutputUtils::FormatDate($note->getDateLastEdited(), true)).' '._('by').' '.$EditedFirstName.' '.$EditedLastName ?></span><br>

<?php
        }
    }
}

require 'Include/Footer-Short.php';
