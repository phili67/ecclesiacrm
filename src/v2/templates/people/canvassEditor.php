<?php
/*******************************************************************************
 *
 *  filename    : templates/canvassEditor.php
 *  last change : 2023-06-21
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2001, 2002, 2003, 2013, 2023 Deane Barker, Chris Gebhardt, Michael Wilt, Philippe Logel
 
 ******************************************************************************/

use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\Utils\OutputUtils;
use EcclesiaCRM\Utils\RedirectUtils;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\dto\CanvassUtilities;
use EcclesiaCRM\CanvassDataQuery;
use EcclesiaCRM\CanvassData;

//Is this the second pass?
if (isset($_POST['Submit'])) {
    $iCanvasser = InputUtils::LegacyFilterInput($_POST['Canvasser']);
    if (!$iCanvasser) {
        $iCanvasser = 0;
    }
    $dDate = InputUtils::FilterDate($_POST['Date']);
    $tPositive = InputUtils::LegacyFilterInput($_POST['Positive']);
    $tCritical = InputUtils::LegacyFilterInput($_POST['Critical']);
    $tInsightful = InputUtils::LegacyFilterInput($_POST['Insightful']);
    $tFinancial = InputUtils::LegacyFilterInput($_POST['Financial']);
    $tSuggestion = InputUtils::LegacyFilterInput($_POST['Suggestion']);
    $bNotInterested = isset($_POST['NotInterested']);
    if ($bNotInterested == '') {
        $bNotInterested = 0;
    }
    $tWhyNotInterested = InputUtils::LegacyFilterInput($_POST['WhyNotInterested']);

    // New canvas input (add)
    if ($iCanvassID < 1) {
        $canvas = new CanvassData();

        $canvas->setFamilyId($iFamily);
        $canvas->setCanvasser($iCanvasser);
        $canvas->setFyid($iFYID);
        $canvas->setDate($dDate);
        $canvas->setPositive($tPositive);
        $canvas->setCritical($tCritical);
        $canvas->setInsightful($tInsightful);
        $canvas->setFinancial($tFinancial);
        $canvas->setSuggestion($tSuggestion);
        $canvas->setNotInterested($bNotInterested);
        $canvas->setWhyNotInterested($tWhyNotInterested);

        $canvas->save();

        //Execute the SQL
        $iCanvassID = $canvas->getId();
    } else {
        $canvas = CanvassDataQuery::Create()->findOneByFamilyId ($iFamily);

        $canvas->setFamilyId($iFamily);
        $canvas->setCanvasser($iCanvasser);
        $canvas->setFyid($iFYID);
        $canvas->setDate($dDate);
        $canvas->setPositive($tPositive);
        $canvas->setCritical($tCritical);
        $canvas->setInsightful($tInsightful);
        $canvas->setFinancial($tFinancial);
        $canvas->setSuggestion($tSuggestion);
        $canvas->setNotInterested($bNotInterested);
        $canvas->setWhyNotInterested($tWhyNotInterested);

        $canvas->save();
    }

    if (isset($_POST['Submit'])) {
        // Check for redirection to another page after saving information:
        if ($linkBack != '') {
            RedirectUtils::Redirect($linkBack);
        } else {
            RedirectUtils::Redirect('v2/people/canvass/editor/'.$iFamily."/".$iFYID."/".$origLinkBack.(($iCanvassID> 0)?"/".$iCanvassID:""));
        }
    }
} else {
    $canvas = CanvassDataQuery::Create()->filterByFyid($iFYID)->findOneByFamilyId ($iFamily);

    if (!is_null ($canvas)) {
        $iCanvassID         = $canvas->getId();
        $iCanvasser         = $canvas->getCanvasser();
        $iFYID              = $canvas->getFyid();
        if (!is_null($canvas->getDate())) {
            $dDate = $canvas->getDate()->format('Y-m-d');
        } else {
            $dDate = '';
        }
        $tPositive          = $canvas->getPositive();
        $tCritical          = $canvas->getCritical();
        $tInsightful        = $canvas->getInsightful();
        $tFinancial         = $canvas->getFinancial();
        $tSuggestion        = $canvas->getSuggestion();
        $bNotInterested     = $canvas->getNotInterested();
        $tWhyNotInterested  = $canvas->getWhyNotInterested();
    } else {
        // Set some default values
        $iCanvasser = SessionUser::getUser()->getPersonId();
        $dDate = date('Y-m-d');

        $dDate = '';
        $tPositive = '';
        $tCritical = '';
        $tInsightful = '';
        $tFinancial = '';
        $tSuggestion = '';
        $bNotInterested = false;
        $tWhyNotInterested = '';
    }
}

// Get the lists of canvassers for the drop-down
$canvassers      = CanvassUtilities::CanvassGetCanvassers('Canvassers');
$braveCanvassers = CanvassUtilities::CanvassGetCanvassers('BraveCanvassers');

require $sRootDocument . '/Include/Header.php';
?>

<form method="post" action="<?= $sRootPath ?>/v2/people/canvass/editor/<?= $iFamily ?>/<?= $iFYID ?>/<?= $origLinkBack ?><?= ($iCanvassID> 0)?"/".$iCanvassID:""?>" name="CanvassEditor">
    <div class="card">
        <div class="card-header">
            <h1 class="card-title"><?= _("Opinions about canvassing the person/family")?></h3>
        </div>
        <div class="card-body">
        <?php
            if ((!is_null($canvassers) && $canvassers->count() > 0) ||
                (!is_null($braveCanvassers) && $braveCanvassers->count() > 0)) {
        ?>
            <div class="row">
                <div class="col-lg-3">
                <?= _('Canvasser') ?>:
                </div>
                <div class="col-lg-9">
                <select name='Canvasser' class= "form-control form-control-sm"><option value="0"><?= _('None selected') ?></option>
                <?php
                    if (!is_null($braveCanvassers) && $braveCanvassers->count() != 0) {
                        foreach ($braveCanvassers as $braveCanvasser) {
                    ?>
                            <option value="<?= $braveCanvasser->getId() ?>" <?= ($braveCanvasser->getId() == $iCanvasser)?' selected':'' ?>>
                                <?= $braveCanvasser->getFirstName().' '.$braveCanvasser->getLastName() ?>
                            </option>
                    <?php
                        }
                    }
                    if (!is_null($canvassers) && $canvassers->count() != 0) {
                        foreach ($canvassers as $canvasser) {
                    ?>
                            <option value="<?= $canvasser->getId() ?>" <?= ($canvasser->getId() == $iCanvasser)?' selected':'' ?>>
                                <?= $canvasser->getFirstName().' '.$canvasser->getLastName() ?>
                            </option>
                    <?php
                        }
                    }
                    ?>
                    </select>
                </div>
            </div>
        <?php
        }
        ?>
            <div class="row">
                <div class="col-lg-2">
                    <label><?= _('Date') ?></label>
                </div>
                <div class="col-lg-5">
                <input type="text" name="Date" value="<?= OutputUtils::change_date_for_place_holder($dDate) ?>"
                    maxlength="10" id="sel1" size="11"  class="form-control pull-right active date-picker"
                    placeholder="<?= SystemConfig::getValue("sDatePickerPlaceHolder") ?>"?><span style="color:red"><?= $sDateError ?></span>
                </div>
            </div>
            <br>
            <div class="row">
                <div class="col-lg-2">
                    <label><?= _('Positive') ?></label>
                </div>
                <div class="col-lg-5">
                <textarea name="Positive" rows="3" style="width:100%" class= "form-control form-control-sm"><?= $tPositive ?></textarea>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-2">
                    <label><?= _('Critical') ?></label>
                </div>
                <div class="col-lg-5">
                <textarea name="Critical" rows="3" style="width:100%" class= "form-control form-control-sm"><?= $tCritical ?></textarea>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-2">
                    <label><?= _('Insightful') ?></label>
                </div>
                <div class="col-lg-5">
                <textarea name="Insightful" rows="3" style="width:100%" class= "form-control form-control-sm"><?= $tInsightful ?></textarea>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-2">
                    <label><?= _('Financial') ?></label>
                </div>
                <div class="col-lg-5">
                <textarea name="Financial" rows="3" style="width:100%" class= "form-control form-control-sm"><?= $tFinancial ?></textarea>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-2">
                <label><?= _('Suggestions') ?></label>
                </div>
                <div class="col-lg-5">
                <textarea name="Suggestion" rows="3" style="width:100%" class= "form-control form-control-sm"><?= $tSuggestion ?></textarea>
                </div>
            </div>
            <hr/>
            <div class="row">
                <div class="col-lg-2">
                    <label><?= _('Not Interested') ?></label>
                </div>
                <div class="col-lg-5">
                <input type="checkbox" Name="NotInterested" value="1" <?= ($bNotInterested)?' checked':'' ?>>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-2">
                    <label><?= _('Why Not Interested?') ?></label>
                </div>
                <div class="col-lg-5">
                <textarea name="WhyNotInterested" rows="3" style="width:100%" class= "form-control form-control-sm"><?= $tWhyNotInterested ?></textarea>
                </div>
            </div>
        </div>
        <div class="card-footer">
                    <input type="submit" class="btn btn-primary" value="&check; <?= _('Save') ?>" name="Submit">
                    <input type="button" class="btn btn-default" value="x <?= _('Cancel') ?>" name="Cancel" 
                        onclick="javascript:document.location='<?= $sRootPath ?>/<?= (strlen($linkBack) > 0)?$linkBack:'v2/dashboard' ?>';">
        </div>
    </div>
</form>


<?php require $sRootDocument . '/Include/Footer.php'; ?>