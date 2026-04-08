<?php
/*******************************************************************************
 *
 *  filename    : templates/canvassAutomation.php
 *  last change : 2023-06-21
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2001, 2002, 2003, 2013, 2023 Deane Barker, Chris Gebhardt, Michael Wilt, Philippe Logel
 *
 ******************************************************************************/

use EcclesiaCRM\dto\CanvassUtilities;
use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\Utils\MiscUtils;
use EcclesiaCRM\Utils\RedirectUtils;

$iFYID = MiscUtils::CurrentFY();
if (array_key_exists('idefaultFY', $_SESSION)) {
    $iFYID = $_SESSION['idefaultFY'];
}
if (array_key_exists('FYID', $_POST)) {
    $iFYID = InputUtils::LegacyFilterInput($_POST['FYID'], 'int');
} // Use FY from the form if it was set

$_SESSION['idefaultFY'] = $iFYID; // Remember default fiscal year

$processNews = '';
$canvassUtilities = new CanvassUtilities();

// Service the action buttons
if (isset($_POST['SetDefaultFY'])) {
    if (isset($_POST['SetDefaultFYConfirm'])) {
        $processNews = $canvassUtilities->CanvassSetDefaultFY($iFYID);
    } else {
        $processNews = _('Not confirmed.');
    }
}

if (isset($_POST['AssignCanvassers'])) {
    if (isset($_POST['AssignCanvassersConfirm'])) {
        $processNews = $canvassUtilities->CanvassAssignCanvassers('Canvassers');
    } else {
        $processNews = _('Not confirmed.');
    }
}

if (isset($_POST['AssignNonPledging'])) {
    if (isset($_POST['AssignNonPledgingConfirm'])) {
        $processNews = $canvassUtilities->CanvassAssignNonPledging('BraveCanvassers', $iFYID);
    } else {
        $processNews = _('Not confirmed.');
    }
}

if (isset($_POST['ClearCanvasserAssignments'])) {
    if (isset($_POST['ClearCanvasserAssignmentsConfirm'])) {
        $canvassUtilities->CanvassClearCanvasserAssignments();
        $processNews = _('Cleared all canvasser assignments.');
    } else {
        $processNews = _('Not confirmed.');
    }
}

if (isset($_POST['SetAllOkToCanvass'])) {
    if (isset($_POST['SetAllOkToCanvassConfirm'])) {
        $canvassUtilities->CanvassSetAllOkToCanvass();
        $processNews = _('Set Ok To Canvass for all families.');
    } else {
        $processNews = _('Not confirmed.');
    }
}

if (isset($_POST['ClearAllOkToCanvass'])) {
    if (isset($_POST['ClearAllOkToCanvassConfirm'])) {
        $canvassUtilities->CanvassClearAllOkToCanvass();
        $processNews = _('Disabled Ok To Canvass for all families.');
    } else {
        $processNews = _('ClearAllOkToCanvass button not confimed.');
    }
}

if (isset($_POST['BriefingSheets'])) {
    RedirectUtils::Redirect('Reports/CanvassReports.php?FYID=' . $iFYID . '&WhichReport=Briefing');
}
if (isset($_POST['ProgressReport'])) {
    RedirectUtils::Redirect('Reports/CanvassReports.php?FYID=' . $iFYID . '&WhichReport=Progress');
}
if (isset($_POST['SummaryReport'])) {
    RedirectUtils::Redirect('Reports/CanvassReports.php?FYID=' . $iFYID . '&WhichReport=Summary');
}
if (isset($_POST['NotInterestedReport'])) {
    RedirectUtils::Redirect('Reports/CanvassReports.php?FYID=' . $iFYID . '&WhichReport=NotInterested');
}

require $sRootDocument . '/Include/Header.php';

if ($processNews != '') {
    ?>
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <i class="fas fa-info-circle mr-1"></i><strong><?= $processNews ?></strong>
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    </div>
    <?php
}
?>

<div class="row">
    <div class="col-lg-12">
        <div class="card card-primary card-outline">
            <div class="card-header border-1 d-flex flex-wrap justify-content-between align-items-center">
                <h3 class="card-title mb-0"><i class="fas fa-clipboard-list mr-1"></i><?= _('Canvass Automation') ?></h3>
                <span class="badge badge-secondary mt-2 mt-sm-0"><?= _('Administration') ?></span>
            </div>
            <div class="card-body">
                <div class="alert alert-info mb-3">
                    <i class="fas fa-circle-info mr-1"></i>
                    <?= _('Use this page to configure fiscal year settings, assign canvassers, and generate canvass reports.') ?>
                </div>

                <form method="post" action="<?= $sRootPath ?>/v2/people/canvass/automation" name="CanvassAutomation">
                    <div class="row align-items-center mb-3">
                        <div class="col-md-2">
                            <label class="mb-0" for="FYID"><?= _('Fiscal Year:') ?></label>
                        </div>
                        <div class="col-md-4 mt-2 mt-md-0">
                            <?php MiscUtils::PrintFYIDSelect($iFYID, 'FYID') ?>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle mb-0">
                            <thead>
                            <tr>
                                <th style="width: 70%"><?= _('Action Details') ?></th>
                                <th class="text-center" style="width: 30%"><?= _('Run Action') ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>
                                    <div class="font-weight-bold"><?= _('Set this fiscal year as default') ?></div>
                                    <div class="small text-muted mb-2"><?= _('This default fiscal year will be used in canvass workflows.') ?></div>
                                    <div class="form-check mb-0">
                                        <input class="form-check-input" type="checkbox" name="SetDefaultFYConfirm" id="SetDefaultFYConfirm">
                                        <label class="form-check-label" for="SetDefaultFYConfirm"><?= _('Check to confirm') ?></label>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <input type="submit" class="btn btn-primary btn-sm btn-block" value="<?= _('Set default fiscal year') ?>" name="SetDefaultFY">
                                </td>
                            </tr>

                            <tr>
                                <td>
                                    <div class="font-weight-bold"><?= _('Assign canvassers to all families') ?></div>
                                    <div class="small text-muted mb-2"><?= _('Canvassers are randomly selected from the "Canvassers" group.') ?></div>
                                    <div class="form-check mb-0">
                                        <input class="form-check-input" type="checkbox" name="AssignCanvassersConfirm" id="AssignCanvassersConfirm">
                                        <label class="form-check-label" for="AssignCanvassersConfirm"><?= _('Check to confirm') ?></label>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <input type="submit" class="btn btn-success btn-sm btn-block" value="<?= _('Assign Canvassers') ?>" name="AssignCanvassers">
                                </td>
                            </tr>

                            <tr>
                                <td>
                                    <div class="font-weight-bold"><?= _('Assign canvassers to non-pledging families') ?></div>
                                    <div class="small text-muted mb-2"><?= _('Canvassers are randomly selected from the "BraveCanvassers" group.') ?></div>
                                    <div class="form-check mb-0">
                                        <input class="form-check-input" type="checkbox" name="AssignNonPledgingConfirm" id="AssignNonPledgingConfirm">
                                        <label class="form-check-label" for="AssignNonPledgingConfirm"><?= _('Check to confirm') ?></label>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <input type="submit" class="btn btn-success btn-sm btn-block" value="<?= _('Assign To Non Pledging') ?>" name="AssignNonPledging">
                                </td>
                            </tr>

                            <tr>
                                <td>
                                    <div class="font-weight-bold"><?= _('Clear all canvasser assignments') ?></div>
                                    <div class="small text-muted mb-2"><?= _('Important: this removes manual canvasser assignments too.') ?></div>
                                    <div class="form-check mb-0">
                                        <input class="form-check-input" type="checkbox" name="ClearCanvasserAssignmentsConfirm" id="ClearCanvasserAssignmentsConfirm">
                                        <label class="form-check-label" for="ClearCanvasserAssignmentsConfirm"><?= _('Check to confirm') ?></label>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <input type="submit" class="btn btn-danger btn-sm btn-block" value="<?= _('Clear Canvasser Assignments') ?>" name="ClearCanvasserAssignments">
                                </td>
                            </tr>

                            <tr>
                                <td>
                                    <div class="font-weight-bold"><?= _('Enable "Ok To Canvass" for all families') ?></div>
                                    <div class="small text-muted mb-2"><?= _('Important: this overrides manual values already set.') ?></div>
                                    <div class="form-check mb-0">
                                        <input class="form-check-input" type="checkbox" name="SetAllOkToCanvassConfirm" id="SetAllOkToCanvassConfirm">
                                        <label class="form-check-label" for="SetAllOkToCanvassConfirm"><?= _('Check to confirm') ?></label>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <input type="submit" class="btn btn-primary btn-sm btn-block" value="<?= _('Enable Canvass for All Families') ?>" name="SetAllOkToCanvass">
                                </td>
                            </tr>

                            <tr>
                                <td>
                                    <div class="font-weight-bold"><?= _('Disable "Ok To Canvass" for all families') ?></div>
                                    <div class="small text-muted mb-2"><?= _('Important: this overrides manual values already set.') ?></div>
                                    <div class="form-check mb-0">
                                        <input class="form-check-input" type="checkbox" name="ClearAllOkToCanvassConfirm" id="ClearAllOkToCanvassConfirm">
                                        <label class="form-check-label" for="ClearAllOkToCanvassConfirm"><?= _('Check to confirm') ?></label>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <input type="submit" class="btn btn-primary btn-sm btn-block" value="<?= _('Disable Canvass for All Families') ?>" name="ClearAllOkToCanvass">
                                </td>
                            </tr>

                            <tr>
                                <td>
                                    <div class="font-weight-bold"><?= _('Generate briefing sheets PDF') ?></div>
                                    <div class="small text-muted mb-0"><?= _('Creates briefing sheets for all families, sorted by canvasser.') ?></div>
                                </td>
                                <td class="text-center">
                                    <input type="submit" class="btn btn-info btn-sm btn-block" value="<?= _('Briefing Sheets') ?>" name="BriefingSheets">
                                </td>
                            </tr>

                            <tr>
                                <td>
                                    <div class="font-weight-bold"><?= _('Generate progress report PDF') ?></div>
                                    <div class="small text-muted mb-0"><?= _('Includes overall canvass progress and individual canvasser progress.') ?></div>
                                </td>
                                <td class="text-center">
                                    <input type="submit" class="btn btn-info btn-sm btn-block" value="<?= _('Progress Report') ?>" name="ProgressReport">
                                </td>
                            </tr>

                            <tr>
                                <td>
                                    <div class="font-weight-bold"><?= _('Generate summary report PDF') ?></div>
                                    <div class="small text-muted mb-0"><?= _('Includes comments extracted from canvass data.') ?></div>
                                </td>
                                <td class="text-center">
                                    <input type="submit" class="btn btn-info btn-sm btn-block" value="<?= _('Summary Report') ?>" name="SummaryReport">
                                </td>
                            </tr>

                            <tr>
                                <td>
                                    <div class="font-weight-bold"><?= _('Generate not interested report PDF') ?></div>
                                    <div class="small text-muted mb-0"><?= _('Lists families marked as "Not Interested" by canvassers.') ?></div>
                                </td>
                                <td class="text-center">
                                    <input type="submit" class="btn btn-info btn-sm btn-block" value="<?= _('Not Interested Report') ?>" name="NotInterestedReport">
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require $sRootDocument . '/Include/Footer.php'; ?>
