<?php
/*******************************************************************************
 *
 *  filename    : templates/canvassEditor.php
 *  last change : 2023-06-21
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2001, 2002, 2003, 2013, 2023 Deane Barker, Chris Gebhardt, Michael Wilt, Philippe Logel
 *
 ******************************************************************************/

use EcclesiaCRM\CanvassData;
use EcclesiaCRM\CanvassDataQuery;
use EcclesiaCRM\dto\CanvassUtilities;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\Utils\RedirectUtils;

// Is this the second pass?
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

    // New canvass input (add)
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
        $iCanvassID = $canvas->getId();
    } else {
        $canvas = CanvassDataQuery::create()->findOneByFamilyId($iFamily);

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

    // Check for redirection to another page after saving information
    if ($linkBack != '') {
        RedirectUtils::Redirect($linkBack);
    } else {
        RedirectUtils::Redirect('v2/people/canvass/editor/' . $iFamily . '/' . $iFYID . '/' . $origLinkBack . (($iCanvassID > 0) ? '/' . $iCanvassID : ''));
    }
} else {
    $canvas = CanvassDataQuery::create()->filterByFyid($iFYID)->findOneByFamilyId($iFamily);

    if (!is_null($canvas)) {
        $iCanvassID = $canvas->getId();
        $iCanvasser = $canvas->getCanvasser();
        $iFYID = $canvas->getFyid();
        if (!is_null($canvas->getDate())) {
            $dDate = $canvas->getDate()->format('Y-m-d');
        } else {
            $dDate = '';
        }
        $tPositive = $canvas->getPositive();
        $tCritical = $canvas->getCritical();
        $tInsightful = $canvas->getInsightful();
        $tFinancial = $canvas->getFinancial();
        $tSuggestion = $canvas->getSuggestion();
        $bNotInterested = $canvas->getNotInterested();
        $tWhyNotInterested = $canvas->getWhyNotInterested();
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

$sDateError = $sDateError ?? '';

// Get the lists of canvassers for the drop-down
$canvassers = CanvassUtilities::CanvassGetCanvassers('Canvassers');
$braveCanvassers = CanvassUtilities::CanvassGetCanvassers('BraveCanvassers');

require $sRootDocument . '/Include/Header.php';
?>

<div class="d-flex justify-content-end mb-2">
    <a class="btn btn-sm btn-outline-secondary" href="<?= $sRootPath ?>/<?= (strlen($linkBack) > 0) ? $linkBack : 'v2/dashboard' ?>">
        <i class="fas fa-arrow-left mr-1"></i><?= _('Back') ?>
    </a>
</div>

<form method="post" action="<?= $sRootPath ?>/v2/people/canvass/editor/<?= $iFamily ?>/<?= $iFYID ?>/<?= $origLinkBack ?><?= ($iCanvassID > 0) ? '/' . $iCanvassID : '' ?>" name="CanvassEditor">
    <div class="card card-primary card-outline">
        <div class="card-header border-1 d-flex flex-wrap justify-content-between align-items-center">
            <h3 class="card-title mb-0"><i class="fas fa-comments mr-1"></i><?= _('Canvass Notes for Family/Person') ?></h3>
            <span class="badge badge-secondary mt-2 mt-sm-0"><?= _('FY') ?> <?= $iFYID ?></span>
        </div>
        <div class="card-body">
            <div class="alert alert-info mb-3">
                <i class="fas fa-circle-info mr-1"></i>
                <?= _('Record feedback from canvassing visits, including observations and follow-up information.') ?>
            </div>

            <div class="card card-outline card-secondary mb-3">
                <div class="card-header py-2">
                    <h4 class="card-title mb-0"><i class="fas fa-user-check mr-1"></i><?= _('Session Context') ?></h4>
                </div>
                <div class="card-body">

                    <?php
                    if ((!is_null($canvassers) && $canvassers->count() > 0) ||
                        (!is_null($braveCanvassers) && $braveCanvassers->count() > 0)) {
                    ?>
                    <div class="form-group row mb-3">
                        <label class="col-lg-2 col-form-label"><?= _('Canvasser') ?></label>
                        <div class="col-lg-6">
                            <select name="Canvasser" class="form-control form-control-sm">
                                <option value="0"><?= _('None selected') ?></option>
                                <?php
                                if (!is_null($braveCanvassers) && $braveCanvassers->count() != 0) {
                                    foreach ($braveCanvassers as $braveCanvasser) {
                                ?>
                                    <option value="<?= $braveCanvasser->getId() ?>" <?= ($braveCanvasser->getId() == $iCanvasser) ? ' selected' : '' ?>>
                                        <?= $braveCanvasser->getFirstName() . ' ' . $braveCanvasser->getLastName() ?>
                                    </option>
                                <?php
                                    }
                                }
                                if (!is_null($canvassers) && $canvassers->count() != 0) {
                                    foreach ($canvassers as $canvasser) {
                                ?>
                                    <option value="<?= $canvasser->getId() ?>" <?= ($canvasser->getId() == $iCanvasser) ? ' selected' : '' ?>>
                                        <?= $canvasser->getFirstName() . ' ' . $canvasser->getLastName() ?>
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

                    <div class="form-group row mb-0">
                        <label class="col-lg-2 col-form-label" for="sel1"><?= _('Date') ?></label>
                        <div class="col-lg-4">
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                </div>
                                <input type="text"
                                       name="Date"
                                       value="<?= \EcclesiaCRM\Utils\OutputUtils::change_date_for_place_holder($dDate) ?>"
                                       maxlength="10"
                                       id="sel1"
                                       size="11"
                                       class="form-control date-picker"
                                       placeholder="<?= \EcclesiaCRM\Dto\SystemConfig::getValue('sDatePickerPlaceHolder') ?>">
                            </div>
                            <small class="text-danger"><?= $sDateError ?></small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-outline card-secondary mb-3">
                <div class="card-header py-2">
                    <h4 class="card-title mb-0"><i class="fas fa-pen-nib mr-1"></i><?= _('Observations') ?></h4>
                </div>
                <div class="card-body">
                    <div class="form-group row">
                        <label class="col-lg-2 col-form-label"><?= _('Positive') ?></label>
                        <div class="col-lg-8">
                            <textarea name="Positive" rows="3" class="form-control form-control-sm" placeholder="<?= _('Positive feedback and strengths...') ?>"><?= $tPositive ?></textarea>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-lg-2 col-form-label"><?= _('Critical') ?></label>
                        <div class="col-lg-8">
                            <textarea name="Critical" rows="3" class="form-control form-control-sm" placeholder="<?= _('Concerns or blockers...') ?>"><?= $tCritical ?></textarea>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-lg-2 col-form-label"><?= _('Insightful') ?></label>
                        <div class="col-lg-8">
                            <textarea name="Insightful" rows="3" class="form-control form-control-sm" placeholder="<?= _('Important insights to keep...') ?>"><?= $tInsightful ?></textarea>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-lg-2 col-form-label"><?= _('Financial') ?></label>
                        <div class="col-lg-8">
                            <textarea name="Financial" rows="3" class="form-control form-control-sm" placeholder="<?= _('Financial context or comments...') ?>"><?= $tFinancial ?></textarea>
                        </div>
                    </div>

                    <div class="form-group row mb-0">
                        <label class="col-lg-2 col-form-label"><?= _('Suggestions') ?></label>
                        <div class="col-lg-8">
                            <textarea name="Suggestion" rows="3" class="form-control form-control-sm" placeholder="<?= _('Suggested next steps...') ?>"><?= $tSuggestion ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-outline card-secondary">
                <div class="card-header py-2">
                    <h4 class="card-title mb-0"><i class="fas fa-flag mr-1"></i><?= _('Interest Status') ?></h4>
                </div>
                <div class="card-body">
                    <div class="form-group row">
                        <label class="col-lg-2 col-form-label" for="NotInterested"><?= _('Not Interested') ?></label>
                        <div class="col-lg-8">
                            <div class="custom-control custom-switch mt-1">
                                <input type="checkbox" class="custom-control-input" id="NotInterested" name="NotInterested" value="1" <?= ($bNotInterested) ? ' checked' : '' ?>>
                                <label class="custom-control-label" for="NotInterested"><?= _('Mark this family/person as not interested') ?></label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row mb-0">
                        <label class="col-lg-2 col-form-label"><?= _('Why Not Interested?') ?></label>
                        <div class="col-lg-8">
                            <textarea name="WhyNotInterested" rows="3" class="form-control form-control-sm" placeholder="<?= _('Explain why...') ?>"><?= $tWhyNotInterested ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer d-flex flex-wrap justify-content-between align-items-center">
            <small class="text-muted mb-2 mb-md-0">
                <i class="fas fa-shield-alt mr-1"></i><?= _('Your updates will be saved for this fiscal year context.') ?>
            </small>
            <div class="d-flex flex-wrap">
                <button type="submit" class="btn btn-primary mr-2 mb-2 mb-md-0" name="Submit">
                    <i class="fas fa-save mr-1"></i><?= _('Save') ?>
                </button>
                <button type="button" class="btn btn-outline-secondary mb-2 mb-md-0" name="Cancel"
                        onclick="javascript:document.location='<?= $sRootPath ?>/<?= (strlen($linkBack) > 0) ? $linkBack : 'v2/dashboard' ?>';">
                    <i class="fas fa-times mr-1"></i><?= _('Cancel') ?>
                </button>
            </div>
        </div>
    </div>
</form>

<?php require $sRootDocument . '/Include/Footer.php'; ?>
