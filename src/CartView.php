<?php
/*******************************************************************************
 *
 *  filename    : CartView.php
 *  website     : http://www.ecclesiacrm.com
 *
 *  Copyright 2001-2003 Phillip Hullquist, Deane Barker, Chris Gebhardt

 ******************************************************************************/

require 'Include/Config.php';
require 'Include/Functions.php';

use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\dto\Cart;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\ListOptionQuery;
use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\utils\LabelUtils;

// Set the page title and include HTML header
$sPageTitle = gettext('View Your Cart');
require 'Include/Header.php'; ?>
<?php

if (!$_SESSION['user']->isShowCartEnabled()) {
    Redirect('Menu.php');
    exit;
}

// Confirmation message that people where added to Event from Cart
if (!Cart::HasPeople()) {
    if (!array_key_exists('Message', $_GET)) {
        ?>
             <p class="text-center callout callout-warning"><?= gettext('You have no items in your cart.') ?> </p>
        <?php
    } else {
        switch ($_GET['Message']) {
                case 'aMessage': ?>
                    <p class="text-center callout callout-info"><?= $_GET['iCount'] . ' ' . ($_GET['iCount'] == 1 ? 'Record' : 'Records') . ' Emptied into Event ID:' . $_GET['iEID'] ?> </p>
                    <?php break;
            }
    }
    ?>
    <p align="center"><input type="button" name="Exit" class="btn btn-primary" value="<?= gettext('Back to Menu') ?>" onclick="javascript:document.location='Menu.php';"></p>
    </div>
<?php
} else {

    // Create array with Classification Information (lst_ID = 1)
    $ormClassifications = ListOptionQuery::Create()
              ->orderByOptionSequence()
              ->findById(1);
    
    unset($aClassificationName);
    $aClassificationName[0] = gettext('Unassigned');
    foreach ($ormClassifications as $ormClassification) {
      $aClassificationName[intval($ormClassification->getOptionId())] = $ormClassification->getOptionName();
    }
    
    // Create array with Family Role Information (lst_ID = 2)
    $ormClassifications = ListOptionQuery::Create()
              ->orderByOptionSequence()
              ->findById(2);
    
    unset($aFamilyRoleName);
    $aFamilyRoleName[0] = gettext('Unassigned');
    foreach ($ormClassifications as $ormClassification) {
      $aFamilyRoleName[intval($ormClassification->getOptionId())] = $ormClassification->getOptionName();
    }

    $ormCartItems = PersonQuery::Create()->leftJoinFamily()->orderByLastName()->Where('Person.Id IN ?',$_SESSION['aPeopleCart'])->find();

    $iNumPersons = Cart::CountPeople();
    $iNumFamilies = Cart::CountFamilies();

    /*if ($iNumPersons > 16) {
        ?>
        <form method="get" action="CartView.php#GenerateLabels">
        <input type="submit" class="btn" name="gotolabels"
        value="<?= gettext('Go To Labels') ?>">
        </form>
        <?php
    }*/ ?>

    <!-- BEGIN CART FUNCTIONS -->
    <?php
      if (Cart::CountPeople() > 0) {
    ?>
    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title"><?= gettext("Cart Functions") ?></h3>
        </div>
        <div class="box-body">
            <a href="#" id="emptyCart" class="btn btn-app emptyCart"><i class="fa fa-eraser"></i><?= gettext('Empty Cart') ?></a>
            <?php if ($_SESSION['user']->isManageGroupsEnabled()) {
            ?>
                <a id="emptyCartToGroup" class="btn btn-app"><i class="fa fa-object-ungroup"></i><?= gettext('Empty Cart to Group') ?></a>
            <?php
        }
        if ($_SESSION['user']->isAddRecordsEnabled()) {
            ?>
            <a href="<?= SystemURLs::getRootPath() ?>/CartToFamily.php" class="btn btn-app"><i
                        class="fa fa-users"></i><?= gettext('Empty Cart to Family') ?></a>
            <?php
        } ?>
            <a href="#" id="emptyCartToEvent" class="btn btn-app"><i
                class="fa fa-ticket"></i><?= gettext('Empty Cart to Event') ?></a>

        <?php
            if (SystemConfig::getValue('sMapProvider') == 'OpenStreetMap') {
        ?>
              <a href="<?= SystemURLs::getRootPath() ?>/MapUsingLeaflet.php?GroupID=0" class="btn btn-app"><i class="fa fa-map-marker"></i><?= gettext('Map Cart') ?></a>
        <?php
            } else if (SystemConfig::getValue('sMapProvider') == 'GoogleMaps'){
        ?>
              <a href="<?= SystemURLs::getRootPath() ?>/MapUsingGoogle.php?GroupID=0" class="btn btn-app"><i class="fa fa-map-marker"></i><?= gettext('Map Cart') ?></a>
        <?php
            } else if (SystemConfig::getValue('sMapProvider') == 'BingMaps') {
        ?>
              <a href="<?= SystemURLs::getRootPath() ?>/MapUsingBing.php?GroupID=0" class="btn btn-app"><i class="fa fa-map-marker"></i><?= gettext('Map Cart') ?></a>        
        <?php
            }
        ?>
        <?php if ($bExportCSV) {
            ?>
                <a href="<?= SystemURLs::getRootPath() ?>/CSVExport.php?Source=cart" class="btn btn-app bg-green"><i
                            class="fa fa-file-excel-o"></i><?= gettext('CSV Export') ?></a>
                <?php
        } ?>
            <a href="<?= SystemURLs::getRootPath() ?>/Reports/NameTags.php?labeltype=74536&labelfont=times&labelfontsize=36" class="btn btn-app bg-aqua"><i
                        class="fa fa-file-pdf-o"></i><?= gettext('Name Tags') ?></a>      
            <a class="btn btn-app bg-purple" href="<?= SystemURLs::getRootPath() ?>/CartToBadge.php" > <i class="fa fa-file-picture-o"></i> <span class="cartActionDescription"><?= gettext("Badges") ?></span></a>
        <?php
            if (Cart::CountPeople() != 0) {

                // Email Cart links
                // Note: This will email entire group, even if a specific role is currently selected.
                $sSQL = "SELECT per_Email, fam_Email
                        FROM person_per
                        LEFT JOIN person2group2role_p2g2r ON per_ID = p2g2r_per_ID
                        LEFT JOIN group_grp ON grp_ID = p2g2r_grp_ID
                        LEFT JOIN family_fam ON per_fam_ID = family_fam.fam_ID
                    WHERE per_ID NOT IN (SELECT per_ID FROM person_per INNER JOIN record2property_r2p ON r2p_record_ID = per_ID INNER JOIN property_pro ON r2p_pro_ID = pro_ID AND pro_Name = 'Do Not Email') AND per_ID IN (" . ConvertCartToString($_SESSION['aPeopleCart']) . ')';
                $rsEmailList = RunQuery($sSQL);
                $sEmailLink = '';
                while (list($per_Email, $fam_Email) = mysqli_fetch_row($rsEmailList)) {
                    $sEmail = SelectWhichInfo($per_Email, $fam_Email, false);
                    if ($sEmail) {
                        /* if ($sEmailLink) // Don't put delimiter before first email
                            $sEmailLink .= $sMailtoDelimiter; */
                        // Add email only if email address is not already in string
                        if (!stristr($sEmailLink, $sEmail)) {
                            $sEmailLink .= $sEmail .= $sMailtoDelimiter;
                        }
                    }
                }
                
                $sEmailLink = mb_substr($sEmailLink, 0, -1);
                
                if ($sEmailLink) {
                    // Add default email if default email has been set and is not already in string
                    if (SystemConfig::getValue('sToEmailAddress') != '' && !stristr($sEmailLink, SystemConfig::getValue('sToEmailAddress'))) {
                        $sEmailLink .= $sMailtoDelimiter . SystemConfig::getValue('sToEmailAddress');
                    }
                    
                    $sEmailLink = urlencode($sEmailLink);  // Mailto should comply with RFC 2368

                    if ($bEmailMailto) { // Does user have permission to email groups
                        // Display link
                    ?>
                        <a href="mailto:<?= $sEmailLink?>" class="btn btn-app"><i class='fa fa-send-o'></i><?= gettext('Email Cart') ?></a>
                        <a href="mailto:?bcc=<?= $sEmailLink ?>" class="btn btn-app"><i class="fa fa-send"></i><?= gettext('Email (BCC)') ?></a>
                    <?php
                    }
                }

                //Text Cart Link
                $sSQL = "SELECT per_CellPhone, fam_CellPhone 
                            FROM person_per LEFT 
                            JOIN family_fam ON person_per.per_fam_ID = family_fam.fam_ID 
                        WHERE per_ID NOT IN (SELECT per_ID FROM person_per INNER JOIN record2property_r2p ON r2p_record_ID = per_ID INNER JOIN property_pro ON r2p_pro_ID = pro_ID AND pro_Name = 'Do Not SMS') AND per_ID IN (" . ConvertCartToString($_SESSION['aPeopleCart']) . ')';
                $rsPhoneList = RunQuery($sSQL);
                $sPhoneLink = '';
                $sPhoneLinkSMS = '';
                $sCommaDelimiter = ', ';

                while (list($per_CellPhone, $fam_CellPhone) = mysqli_fetch_row($rsPhoneList)) {
                    $sPhone = SelectWhichInfo($per_CellPhone, $fam_CellPhone, false);
                    if ($sPhone) {
                        /* if ($sPhoneLink) // Don't put delimiter before first phone
                            $sPhoneLink .= $sCommaDelimiter;  */
                        // Add phone only if phone is not already in string
                        if (!stristr($sPhoneLink, $sPhone)) {
                            $sPhoneLink .= $sPhone.$sCommaDelimiter;
                            $sPhoneLinkSMS .= $sPhone.$sCommaDelimiter;
                        }
                    }
                }
                if ($sPhoneLink) {
                    if ($bEmailMailto) { // Does user have permission to email groups
                    ?>
                    &nbsp;
                    <div class="btn-group">
                      <a class="btn btn-app" href="javascript:void(0)" onclick="allPhonesCommaD()"><i class="fa fa-mobile-phone"></i> <?= gettext("Text Cart") ?></a>
                      <button type="button" class="btn btn-app dropdown-toggle" data-toggle="dropdown">
                        <span class="caret"></span>
                        <span class="sr-only">Toggle Dropdown</span>
                      </button>
                      <ul class="dropdown-menu" role="menu">
                             <li> <a href="javascript:void(0)" onclick="allPhonesCommaD()"><i class="fa fa-mobile-phone"></i> <?= gettext("Copy Paste the Texts") ?></a></li>
                             <script nonce="<?= SystemURLs::getCSPNonce() ?>">function allPhonesCommaD() {prompt("Press CTRL + C to copy all group members\' phone numbers", "<?= mb_substr($sPhoneLink, 0, -2) ?>")};</script>
                             <li> <a href="sms:<?= str_replace(' ', '',mb_substr($sPhoneLinkSMS, 0, -2)) ?>"><i class="fa fa-mobile-phone"></i><?= gettext("Text Cart") ?></li>
                          </ul>
                    </div>
                    <?php
                    }
                } ?>
                <a href="<?= SystemURLs::getRootPath() ?>/DirectoryReports.php?cartdir=Cart+Directory" class="btn btn-app"><i
                            class="fa fa-book"></i><?= gettext('Create Directory From Cart') ?></a>
                            
             <?php   if ($_SESSION['user']->isAddRecordsEnabled()) {
            ?>
                <a href="#" id="deleteCart" class="btn btn-app bg-red"><i
                            class="fa fa-trash"></i><?= gettext('Delete Persons From Cart and CRM') ?></a>
                <?php
            } ?>

                <script nonce="<?= SystemURLs::getCSPNonce() ?>" ><!--
                    function codename() {
                        if (document.labelform.bulkmailpresort.checked) {
                            document.labelform.bulkmailquiet.disabled = false;
                        }
                        else {
                            document.labelform.bulkmailquiet.disabled = true;
                            document.labelform.bulkmailquiet.checked = false;
                        }
                    }

                    //-->
                </SCRIPT>
                </div>
                <!-- /.box-body -->
                </div>
                <!-- /.box -->
            <?php
            } 
            ?>
            <!-- Default box -->
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title"><?= gettext('Generate Labels') ?></h3>
                </div>
                <form method="get" action="Reports/PDFLabel.php" name="labelform">
                  <div class="box-body">
                      <?php
                        LabelUtils::LabelGroupSelect('groupbymode');
                      ?>
                      <div class="row">
                        <div class="col-md-6">
                            <?= gettext('Bulk Mail Presort') ?>
                        </div>                           
                        <div class="col-md-6">
                            <input name="bulkmailpresort" type="checkbox" onclick="codename()" id="BulkMailPresort" value="1" <?= (array_key_exists('buildmailpresort', $_COOKIE) && $_COOKIE['bulkmailpresort'])?'checked':'' ?>><br>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-md-6">
                          <?= gettext('Quiet Presort') ?>
                        </div>
                        <div class="col-md-6">
                            <!-- // This would be better with $_SESSION variable -->
                            <!-- // instead of cookie ... (save $_SESSION in MySQL) -->
                            <input <?= (array_key_exists('buildmailpresort', $_COOKIE) && !$_COOKIE['bulkmailpresort'])?'disabled ':'' ?> name="bulkmailquiet" type="checkbox" onclick="codename()" id="QuietBulkMail" value="1" <?= (array_key_exists('bulkmailquiet', $_COOKIE) && $_COOKIE['bulkmailquiet'] && array_key_exists('buildmailpresort', $_COOKIE) && $_COOKIE['bulkmailpresort'])?'checked':'' ?>>
                        </div>
                      </div>
                            <?php
                              LabelUtils::ToParentsOfCheckBox('toparents');
                              LabelUtils::LabelSelect('labeltype');
                              LabelUtils::FontSelect('labelfont');
                              LabelUtils::FontSizeSelect('labelfontsize');
                              LabelUtils::StartRowStartColumn();
                              LabelUtils::IgnoreIncompleteAddresses();
                              LabelUtils::LabelFileType(); 
                            ?>
                  </div>
                  <div class="row">
                    <div class="col-md-5"></div>
                    <div class="col-md-4">
                      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" class="btn btn-primary" value="<?= gettext('Generate Labels') ?>" name="Submit">
                    </div>
                  </div>
                  <br>
                </form>
            <!-- /.box-body -->
            </div>


            <?php
    }
} ?>

    <!-- END CART FUNCTIONS -->

    <!-- BEGIN CART LISTING -->
    <?php if (isset($iNumPersons) && $iNumPersons > 0): ?>
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <?= gettext('Your cart contains') . ' ' . $iNumPersons . ' ' . gettext('persons from') . ' ' . $iNumFamilies . ' ' . gettext('families') ?>
                    .</h3>
            </div>
            <div class="box-body">
                <table class="table table-hover dt-responsive" id="cart-listing-table" style="width:100%;">
                  <thead>
                    <tr>
                        <th><?= gettext('Name') ?></th>
                        <th><?= gettext('Address') ?></th>
                        <th><?= gettext('Email') ?></th>
                        <th><?= gettext('Remove') ?></th>
                        <th><?= gettext('Classification') ?></th>
                        <th><?= gettext('Family Role') ?></th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    $sEmailLink = '';
                    $iEmailNum = 0;
                    $email_array = [];

                    foreach ($ormCartItems as $person) {
                        $sEmail = SelectWhichInfo($person->getEmail(), !is_null($person->getFamily())?$person->getFamily()->getEmail():null, false);
                        if (strlen($sEmail) == 0 && strlen($person->getWorkEmail()) > 0) {
                            $sEmail = $person->getWorkEmail();
                        }

                        if (strlen($sEmail)) {
                            $sValidEmail = gettext('Yes');
                            if (!stristr($sEmailLink, $sEmail)) {
                                $email_array[] = $sEmail;

                                if ($iEmailNum == 0) {
                                    // Comma is not needed before first email address
                                    $sEmailLink .= $sEmail;
                                    $iEmailNum++;
                                } else {
                                    $sEmailLink .= $sMailtoDelimiter . $sEmail;
                                }
                            }
                        } else {
                            $sValidEmail = gettext('No');
                        }

                        $sAddress1 = SelectWhichInfo($person->getAddress1(), !is_null($person->getFamily())?$person->getFamily()->getAddress1():null, false);
                        $sAddress2 = SelectWhichInfo($person->getAddress2(), !is_null($person->getFamily())?$person->getFamily()->getAddress2():null, false);

                        if (strlen($sAddress1) > 0 || strlen($sAddress2) > 0) {
                            $sValidAddy = gettext('Yes');
                        } else {
                            $sValidAddy = gettext('No');
                        }

                        $personName = $person->getFirstName() . ' ' . $person->getLastName();
                        $thumbnail = SystemURLs::getRootPath() . '/api/persons/' . $person->getId() . '/thumbnail'; ?>

                        <tr>
                            <td>
                                <img src="<?= $thumbnail ?>" class="direct-chat-img initials-image">&nbsp
                                <a href="<?= SystemURLs::getRootPath() ?>/PersonView.php?PersonID=<?= $person->getId() ?>">
                                    <?= FormatFullName($person->getTitle(), $person->getFirstName(), $person->getMiddleName(), $person->getLastName(), $person->getSuffix(), 1) ?>
                                </a>
                            </td>
                            <td><?= $sValidAddy ?></td>
                            <td><?= $sValidEmail ?></td>
                            <td><a class="RemoveFromPeopleCart btn btn-danger" data-personid="<?= $person->getId() ?>"><?= gettext('Remove') ?></a>
                            </td>
                            <td><?= $aClassificationName[$person->getClsId()] ?></td>
                            <td><?= $aFamilyRoleName[$person->getFmrId()] ?></td>
                        </tr>
                        <?php
                    } ?>

                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
    <!-- END CART LISTING -->

    <script nonce="<?= SystemURLs::getCSPNonce() ?>" >
        $(document).ready(function () {
          $("#cart-listing-table").DataTable(window.CRM.plugin.dataTable);
          $("#cart-label-table").DataTable({
            responsive:true,
            paging: false,
            searching: false,
            ordering: false,
            info:     false,
            //dom: window.CRM.plugin.dataTable.dom,
            fnDrawCallback: function( settings ) {
              $("#selector thead").remove(); 
            }
          });

          $(document).on("click", ".emptyCart", function (e) {
            window.CRM.cart.empty(function(){
              document.location.reload();
            });
          });

          $(document).on("click", ".RemoveFromPeopleCart", function (e) {
            clickedButton = $(this);
            e.stopPropagation();
            window.CRM.cart.removePerson([clickedButton.data("personid")],function() {
              document.location.reload();
            });
          });

         });
    </script>

    <?php

require 'Include/Footer.php';
?>
