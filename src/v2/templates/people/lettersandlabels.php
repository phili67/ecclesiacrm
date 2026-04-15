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

<div class="d-flex justify-content-end mb-2">
    <a class="btn btn-sm btn-outline-secondary" href="#"
       onclick="if (window.history.length > 1) { window.history.back(); } else { window.location.href='<?= $sRootPath ?>/v2/dashboard'; } return false;">
        <i class="fas fa-arrow-left mr-1"></i><?= _('Back') ?>
    </a>
</div>

<div class="alert alert-info d-flex align-items-start">
    <i class="fa-solid fa-circle-info mt-1 mr-2"></i>
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
        <div class="col-lg-9">
            <div class="card card-outline card-secondary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-file-alt mr-1"></i><?= gettext('People Reports') ?></h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card card-outline card-secondary h-100 mb-3 mb-md-0">
                            <div class="card-body py-2">
                            <h5 class="mb-2"><?= _("Mail Label") ?></h5>
                                <hr class="mt-1" />
                                <?php
                                LabelUtils::LabelSelect('labeltype');
                                LabelUtils::FontSelect('labelfont');
                                LabelUtils::FontSizeSelect('labelfontsize');
                                ?>
                                <div class="row mt-2">
                                    <div class="col-md-6"><label><?= _("Recipient Naming Method") ?></label></div>
                                    <div class="col-md-6">
                                        <select class="form-control form-control-sm" name="recipientnamingmethod">
                                            <option value="salutationutility"><?= gettext("Salutation Utility") ?></option>
                                            <option value="familyname"><?= gettext("Family Name") ?></option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card card-outline card-secondary h-100 mb-3 mb-md-0">
                            <div class="card-body py-2">
                            <h5 class="mb-2"><?= _("Person Custom Fields") ?></h5>
                                <hr class="mt-1" />
                                <?php
                                if ($numPersonCustomFields > 0) {
                                    foreach ($ormPersonPersonCustomFields as $ormPersonPersonCustomField) {
                                        if (($aSecurityType[$ormPersonPersonCustomField->getCustomFieldSec()] == 'bAll') || ($_SESSION[$aSecurityType[$ormPersonPersonCustomField->getCustomFieldSec()]])) {
                                ?>
                                            <div class="form-check mb-1">
                                                <input class="form-check-input" type="checkbox" name="bCustomPerson<?= $ormPersonPersonCustomField->getCustomOrder() ?>" value="<?= $ormPersonPersonCustomField->getCustomConfirmationDatas()?'1':'0' ?>" <?= $ormPersonPersonCustomField->getCustomConfirmationDatas()?'checked':'' ?>>
                                                <label class="form-check-label"><?= $ormPersonPersonCustomField->getCustomName() ?></label>
                                            </div>
                                <?php
                                        }
                                    }
                                }
                                ?>

                                <hr/>

                                <h5 class="mb-2"><?= _("Family Custom Fields") ?></h5>
                                <hr class="mt-1" />
                                <?php
                                if ($numFamilyCustomFields > 0) {
                                    foreach ($ormFamilyPersonCustomFields as $ormFamilyPersonCustomField) {
                                        if (($aSecurityType[$ormFamilyPersonCustomField->getCustomFieldSec()] == 'bAll') || ($_SESSION[$aSecurityType[$ormFamilyPersonCustomField->getCustomFieldSec()]])) {
                                ?>
                                            <div class="form-check mb-1">
                                                <input class="form-check-input" type="checkbox" name="bCustomFamily<?= $ormFamilyPersonCustomField->getCustomOrder() ?>" value="<?= $ormFamilyPersonCustomField->getCustomConfirmationDatas()?'1':'0' ?>" <?= $ormFamilyPersonCustomField->getCustomConfirmationDatas()?'checked':'' ?>>
                                                <label class="form-check-label"><?= $ormFamilyPersonCustomField->getCustomName() ?></label>
                                            </div>
                                <?php
                                        }
                                    }
                                }
                                ?>
                            </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card card-outline card-secondary h-100">
                            <div class="card-body py-2">
                            <h5 class="mb-2"><?= _("Person Classifications") ?></h5>
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

                            <h6 class="text-muted text-uppercase mb-2"><?= _("Selection Scope") ?></h6>

                            <select class="form-control form-control-sm" name="letterandlabelsnamingmethod" id="letterandlabelsnamingmethod">
                                <option value="family"><?= gettext("Addresses") ?></option>
                                <option value="person"><?= gettext("Persons") ?></option>
                            </select>

                            <hr/>

                            <div class="row">
                                <div class="col-md-2">
                                    <label><?= _("Search") ?></label>
                                </div>
                                <div class="col-md-10">
                                    <select name="person-family-search" class="person-family-search form-control select2" style="width:100%"></select>
                                </div>                                
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-2">
                                    <label><?= _("Selected") ?></label>
                                </div>
                                <div class="col-md-9">
                                    <div id="users" class="small text-muted"><?= _("None") ?></div>
                                </div>                                
                                <div class="col-md-1">
                                    <button type="button" class="btn btn-outline-secondary btn-xs" id="remove-users" title="<?= _('Clear selected') ?>">
                                        <i class="fa fa-trash-can"></i>
                                    </button>
                                </div>                                
                            </div>

                            <hr />

                            <h6 class="text-muted text-uppercase mb-2"><?= _("Age Range") ?></h6>
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
                    </div>
                </div>
                <div class="card-footer d-flex flex-wrap align-items-center">
                    <div class="btn-group mr-2 mb-2" role="group" aria-label="PDF reports">
                    <button class="btn btn-success" type="submit" name="SubmitNewsLetter" value="SubmitNewsLetter">
                        <i class="fas fa-file-pdf"></i> <?= gettext('Newsletter labels') ?>
                    </button>
                    <button class="btn btn-primary" type="submit" name="SubmitConfirmReport" value="SubmitConfirmReport">
                        <i class="fas fa-file-pdf"></i> <?= gettext('Confirm data letter') ?>
                    </button>
                    <button class="btn btn-primary" type="submit" name="SubmitConfirmLabels" value="SubmitConfirmLabels">
                        <i class="fas fa-file-pdf"></i> <?= gettext('Confirm data labels') ?>
                    </button>
                    </div>
                    <button class="btn btn-danger mr-2 mb-2" type="submit" name="SubmitConfirmReportEmail" value="SubmitConfirmReportEmail">
                        <i class="fas fa-paper-plane"></i> <?= gettext('Confirm data Email') ?>
                    </button>

                    <input type="button" class="btn btn-outline-secondary mb-2" name="Cancel" value="<?= gettext('Cancel') ?>" onclick="javascript:document.location = '<?= $sRootPath ?>/v2/dashboard';">
                </div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-envelope-open-text mr-1"></i><?= gettext('Email Confirmation') ?></h3>
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
                    <h6 class="text-muted text-uppercase mb-2"><?= _("Persons") ?></h6>
                    <div class="table-responsive mb-2">
                        <table class="table table-sm table-borderless mb-0">
                            <tbody>
                                <tr>
                                    <th class="pl-0 pr-2 font-weight-normal text-muted align-middle"><?= _("Pending Persons") ?></th>
                                    <td class="text-right pr-0 align-middle">
                                        <div class="row no-gutters justify-content-end align-items-center">
                                            <div class="col-3 text-right pr-2">
                                                <a href="<?= $sRootPath ?>/v2/query/view/35"><span id="pending-count-persons"><?= $personsConfirmPending->count() ?></span></a>
                                            </div>
                                            <div class="col-9">
                                                <button type="button" class="btn btn-outline-danger btn-xs btn-block text-nowrap" id="delete-pending-persons"><i class="fa fa-trash-can"></i> <?= _("Delete Pending") ?></button>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="pl-0 pr-2 font-weight-normal text-muted align-middle"><?= _("Confirmed persons") ?></th>
                                    <td class="text-right pr-0 align-middle">
                                        <div class="row no-gutters justify-content-end align-items-center">
                                            <div class="col-3 text-right pr-2">
                                                <a href="<?= $sRootPath ?>/v2/query/view/37"><span id="done-count-persons"><?= $personsConfirmDone->count() ?></span></a>
                                            </div>
                                            <div class="col-9">
                                                <button type="button" class="btn btn-outline-danger btn-xs btn-block text-nowrap" id="delete-done-confirmation-persons"><i class="fa fa-trash-can"></i> <?= _("Delete Done Confirmation") ?></button>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <hr class="my-3" />

                    <h6 class="text-muted text-uppercase mb-2"><?= _("Families") ?></h6>
                    <div class="table-responsive mb-2">
                        <table class="table table-sm table-borderless mb-0">
                            <tbody>
                                <tr>
                                    <th class="pl-0 pr-2 font-weight-normal text-muted align-middle"><?= _("Pending Families") ?></th>
                                    <td class="text-right pr-0 align-middle">
                                        <div class="row no-gutters justify-content-end align-items-center">
                                            <div class="col-3 text-right pr-2">
                                                <a href="<?= $sRootPath ?>/v2/query/view/36"><span id="pending-count-families"><?= $familiesPending->count() ?></span></a>
                                            </div>
                                            <div class="col-9">
                                                <button type="button" class="btn btn-outline-danger btn-xs btn-block text-nowrap" id="delete-pending-families"><i class="fa fa-trash-can"></i> <?= _("Delete Pending") ?></button>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="pl-0 pr-2 font-weight-normal text-muted align-middle"><?= _("Confirmed Families") ?></th>
                                    <td class="text-right pr-0 align-middle">
                                        <div class="row no-gutters justify-content-end align-items-center">
                                            <div class="col-3 text-right pr-2">
                                                <a href="<?= $sRootPath ?>/v2/query/view/38"><span id="done-count-families"><?= $familiesConfirmDone->count() ?></span></a>
                                            </div>
                                            <div class="col-9">
                                                <button type="button" class="btn btn-outline-danger btn-xs btn-block text-nowrap" id="delete-done-confirmation-families"><i class="fa fa-trash-can"></i> <?= _("Delete Done Confirmation") ?></button>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
</form>

<?php require $sRootDocument . '/Include/Footer.php'; ?>

<script src="<?= $sRootPath ?>/skin/js/people/letterandlabels.js"></script>