<?php

/*******************************************************************************
 *
 *  filename    : lettersandlabels.php
 *                2006 Ed Davis
 *  last change : 2024-01-31
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : 2024 Philippe Logel all right reserved not MIT licence
 *
 ******************************************************************************/

use EcclesiaCRM\Base\FamilyQuery;
use EcclesiaCRM\Base\PersonQuery;
use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\Utils\RedirectUtils;
use EcclesiaCRM\Utils\LabelUtils;

use EcclesiaCRM\PersonCustomMasterQuery;
use EcclesiaCRM\FamilyCustomMasterQuery;
use EcclesiaCRM\ListOptionQuery;

require $sRootDocument . '/Include/Header.php';


// Get the list of custom person fields
$ormPersonPersonCustomFields = PersonCustomMasterQuery::Create()->orderByCustomOrder()->find();
$numPersonCustomFields = $ormPersonPersonCustomFields->count();

$ormFamilyPersonCustomFields = FamilyCustomMasterQuery::Create()->orderByCustomOrder()->find();
$numFamilyCustomFields = $ormFamilyPersonCustomFields->count();


// Get Field Security List Matrix
$ormSecurityGrps = ListOptionQuery::Create()
    ->orderByOptionSequence()
    ->findById(5);

foreach ($ormSecurityGrps as $ormSecurityGrp) {
    $aSecurityType[$ormSecurityGrp->getOptionId()] = $ormSecurityGrp->getOptionName();
}

// Is this the second pass?
if (isset($_POST['realAction']) && ($_POST['realAction'] == 'SubmitNewsLetter' || $_POST['realAction'] == 'SubmitConfirmReport' 
    || $_POST['realAction'] == 'SubmitConfirmLabels' || $_POST['realAction'] == 'SubmitConfirmReportEmail') ) {
    $sLabelFormat = InputUtils::LegacyFilterInput($_POST['labeltype']);
    $sFontInfo = $_POST['labelfont'];
    $sFontSize = $_POST['labelfontsize'];
    $bRecipientNamingMethod = $_POST['recipientnamingmethod'];
    $sLabelInfo = '&labelfont=' . urlencode($sFontInfo) . '&labelfontsize=' . $sFontSize . "&recipientnamingmethod=" . $bRecipientNamingMethod;

    // set the default values for the PersonCustomMasterQuery
    $ormPersonCustomFields = PersonCustomMasterQuery::create()
        ->orderByCustomOrder()
        ->find();

    $customPersonFields = []; 

    if ( $ormPersonCustomFields->count() > 0) {
        $iFieldNum = 0;
        foreach ($ormPersonCustomFields as $customField) {        
            if (isset($_POST["bCustomPerson".$customField->getCustomOrder()])) {
                $customField->setCustomConfirmationDatas(True);
            } else {
                $customField->setCustomConfirmationDatas(False);
            }
            $customField->save();
        }        
    }
    // end of storing the default values for the PersonCustomMasterQuery

    // set the default values for the PersonCustomMasterQuery
    $ormFamilyCustomFields = FamilyCustomMasterQuery::create()
        ->orderByCustomOrder()
        ->find();

    $customFamilyFields = []; 

    if ( $ormFamilyCustomFields->count() > 0) {
        $iFieldNum = 0;
        foreach ($ormFamilyCustomFields as $customField) {        
            if (isset($_POST["bCustomFamily".$customField->getCustomOrder()])) {
                $customField->setCustomConfirmationDatas(True);
            } else {
                $customField->setCustomConfirmationDatas(False);
            }
            $customField->save();
        }        
    }
    // end of storing the default values for the PersonCustomMasterQuery

    #TODO : to do the same with FamilyCustomMasterQuery

    if ($_POST['realAction'] == 'SubmitNewsLetter') {
        $_SESSION['POST_Datas'] = $_POST;
        RedirectUtils::Redirect('Reports/NewsLetterLabels.php?labeltype=' . $sLabelFormat . $sLabelInfo);
    } elseif ($_POST['realAction'] == 'SubmitConfirmReport' ) {
        $_SESSION['POST_Datas'] = $_POST;
        RedirectUtils::Redirect('Reports/ConfirmReport.php');
    } elseif ($_POST['realAction'] == 'SubmitConfirmLabels') {
        $_SESSION['POST_Datas'] = $_POST;
        RedirectUtils::Redirect('Reports/ConfirmLabels.php?labeltype=' . $sLabelFormat . $sLabelInfo);
    } elseif ($_POST['realAction'] == 'SubmitConfirmReportEmail') {
        $_SESSION['POST_Datas'] = $_POST;
        RedirectUtils::Redirect('Reports/ConfirmReportEmail.php');
    }
} else {
    $sLabelFormat = 'Tractor';
}
?>

<div class="alert alert-info">
    <i class="fa-solid fa-circle-info"></i>
    <?=
        _("Here you can choose to run reports to confirm the data stored in CRM, for families or on an individual basis.<br>
        - in PDF format for printing<br>
        - or by e-mail (please note that at this level, e-mail is massive).")
    ?>
</div>

<form method="post" action="<?= $sRootPath ?>/v2/people/LettersAndLabels" id="Myform">
    <input id="personsId" name="personsId" type="hidden" value="" />
    <input id="familiesId" name="familiesId" type="hidden" value="" />
    <input id="realAction" name="realAction" type="hidden" value="" />

    <div class="row">
        <div class="col-md-10">
            <div class="card card-secondary">
                <div class="card-header border-1">
                    <h3 class="card-title"><?= gettext('People Reports') ?></h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <h3><?= _("Mail Label") ?></h1>
                                <hr />
                                <?php
                                LabelUtils::LabelSelect('labeltype');
                                LabelUtils::FontSelect('labelfont');
                                LabelUtils::FontSizeSelect('labelfontsize');
                                ?>
                                <div class="row">
                                    <div class="col-md-6"><label><?= _("Recipient Naming Method") ?></label></div>
                                    <div class="col-md-6">
                                        <select class="form-control form-control-sm" name="recipientnamingmethod">
                                            <option value="salutationutility"><?= gettext("Salutation Utility") ?></option>
                                            <option value="familyname"><?= gettext("Family Name") ?></option>
                                        </select>
                                    </div>
                                </div>
                        </div>
                        <div class="col-md-4">
                            <h3><?= _("Person Custom Fields") ?></h1>
                                <hr />
                                <?php
                                if ($numPersonCustomFields > 0) {
                                    foreach ($ormPersonPersonCustomFields as $ormPersonPersonCustomField) {
                                        if (($aSecurityType[$ormPersonPersonCustomField->getCustomFieldSec()] == 'bAll') || ($_SESSION[$aSecurityType[$ormPersonPersonCustomField->getCustomFieldSec()]])) {
                                ?>
                                            <input type="checkbox" Name="bCustomPerson<?= $ormPersonPersonCustomField->getCustomOrder() ?>" value="<?= $ormPersonPersonCustomField->getCustomConfirmationDatas()?'1':'0' ?>" <?= $ormPersonPersonCustomField->getCustomConfirmationDatas()?'checked':'' ?>> <?= $ormPersonPersonCustomField->getCustomName() ?><br>
                                <?php
                                        }
                                    }
                                }
                                ?>

                                <hr/>

                                <h3><?= _("Family Custom Fields") ?></h1>
                                <hr />
                                <?php
                                if ($numFamilyCustomFields > 0) {
                                    foreach ($ormFamilyPersonCustomFields as $ormFamilyPersonCustomField) {
                                        if (($aSecurityType[$ormFamilyPersonCustomField->getCustomFieldSec()] == 'bAll') || ($_SESSION[$aSecurityType[$ormFamilyPersonCustomField->getCustomFieldSec()]])) {
                                ?>
                                            <input type="checkbox" Name="bCustomFamily<?= $ormFamilyPersonCustomField->getCustomOrder() ?>" value="<?= $ormFamilyPersonCustomField->getCustomConfirmationDatas()?'1':'0' ?>" <?= $ormFamilyPersonCustomField->getCustomConfirmationDatas()?'checked':'' ?>> <?= $ormFamilyPersonCustomField->getCustomName() ?><br>
                                <?php
                                        }
                                    }
                                }
                                ?>
                        </div>
                        <div class="col-md-4">
                            <h3><?= _("Person Classifications") ?></h1>
                            <select name="classList[]" style="width:100%" multiple id="classList">
                                <?php
                                    //Get Classifications for the drop-down
                                    $ormClassifications = ListOptionQuery::Create()
                                        ->orderByOptionSequence()
                                        ->findById(1);
                                    foreach ($ormClassifications as $ormClassification) {
                                    ?>
                                        <option value="<?= $ormClassification->getOptionID() ?>"><?= $ormClassification->getOptionName() ?>&nbsp;
                                        <?php
                                    }
                                    ?>
                            </select>

                            <hr />

                            <h3><?= _("by") ?></h1>

                            <select class="form-control form-control-sm" name="letterandlabelsnamingmethod" id="letterandlabelsnamingmethod">
                                <option value="family"><?= gettext("Addresses") ?></option>
                                <option value="person"><?= gettext("Persons") ?></option>
                            </select>

                            <hr/>

                            <div class="row">
                                <div class="col-md-2">
                                    <label for="check_all"><?= _("Test") ?></label>
                                </div>
                                <div class="col-md-10">
                                    <select name="person-family-search" class="person-family-search"
                                            class="form-control select2" style="width:100%"></select>
                                </div>                                
                            </div>
                            <div class="row">
                                <div class="col-md-2">
                                    <label for="check_all"><?= _("Selected Members") ?></label>
                                </div>
                                <div class="col-md-9">
                                    <div id="users"><?= _("None") ?></div>
                                </div>                                
                                <div class="col-md-1">
                                    <i class="fa fa-trash-can" id="remove-users"></i>                                                                
                                </div>                                
                            </div>

                            <hr />

                            <h3><?= _("by") ?></h1>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group"><label><?= _("Minimum Age") ?></label>
                                        <input size="5" name="minAge" type="text" value="0" class="form-control form-control-sm">
                                        <div class="help-block">
                                            <div><?= _("The minimum age for which you want records returned.") ?></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group"><label><?= _("Maximum Age") ?></label>
                                        <input size="5" name="maxAge" type="text" value="130" class="form-control form-control-sm">
                                        <div class="help-block">
                                            <div><?= _("The maximum age for which you want records returned.") ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button class="btn btn-success" type="submit" name="SubmitNewsLetter" value="SubmitNewsLetter">
                        <i class="fas fa-file-pdf"></i> <?= gettext('Newsletter labels') ?>
                    </button>
                    <button class="btn btn-primary" type="submit" name="SubmitConfirmReport" value="SubmitConfirmReport">
                        <i class="fas fa-file-pdf"></i> <?= gettext('Confirm data letter') ?>
                    </button>
                    <button class="btn btn-primary" type="submit" name="SubmitConfirmLabels" value="SubmitConfirmLabels">
                        <i class="fas fa-file-pdf"></i> <?= gettext('Confirm data labels') ?>
                    </button>
                    <button class="btn btn-danger" type="submit" name="SubmitConfirmReportEmail" value="SubmitConfirmReportEmail">
                        <i class="fas fa-paper-plane"></i> <?= gettext('Confirm data Email') ?>
                    </button>

                    <input type="button" class="btn btn-default" name="Cancel" value="x <?= gettext('Cancel') ?>" onclick="javascript:document.location = '<?= $sRootPath ?>/v2/dashboard';">
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card card-primary">
            <div class="card-header border-1">
                    <h3 class="card-title"><?= gettext('Email Confirmation') ?></h3>
                </div>
                <div class="card-body">
                    <?php
                        $personsConfirmDone = PersonQuery::create()
                                ->findByConfirmReport('Done');
                        $personsConfirmPending = PersonQuery::create()
                                ->findByConfirmReport('Pending');  
                        $familiesConfirmDone = FamilyQuery::create()
                                ->findByConfirmReport('Done');
                        $familiesPending = FamilyQuery::create()
                                ->findByConfirmReport('Pending');                        
                    ?>
                    <label><?= _("Pending Persons") ?></label> : <a href="<?= $sRootPath ?>/v2/query/view/35"><span id="pending-count-persons"><?= $personsConfirmPending->count() ?></span></a><br>
                    <label><?= _("Confirmed persons") ?></label> : <a href="<?= $sRootPath ?>/v2/query/view/37"><span id="done-count-persons"><?= $personsConfirmDone->count() ?></span></a><br/>
                    <button type="button" class="btn btn-danger btn-xs" id="delete-pending-persons"><i class="fa fa-trash-can"></i> <?= _("Delete Pending") ?></button>
                    <button type="button" class="btn btn-danger btn-xs" id="delete-done-confirmation-persons"><i class="fa fa-trash-can"></i> <?= _("Delete Done Confirmation") ?></button>

                    <hr/>

                    <label><?= _("Pending Families") ?></label> : <a href="<?= $sRootPath ?>/v2/query/view/36"><span id="pending-count-families"><?= $familiesPending->count() ?></span></a><br>
                    <label><?= _("Confirmed Families") ?></label> : <a href="<?= $sRootPath ?>/v2/query/view/38"><span id="done-count-families"><?= $familiesConfirmDone->count() ?></span></a><br>                
                    <button type="button" class="btn btn-danger btn-xs" id="delete-pending-families"><i class="fa fa-trash-can"></i> <?= _("Delete Pending") ?></button>
                    <button type="button" class="btn btn-danger btn-xs" id="delete-done-confirmation-families"><i class="fa fa-trash-can"></i> <?= _("Delete Done Confirmation") ?></button>
                </div>
            </div>
        </div>
    </div>

    
</form>

<?php require $sRootDocument . '/Include/Footer.php'; ?>

<script src="<?= $sRootPath ?>/skin/js/people/letterandlabels.js"></script>