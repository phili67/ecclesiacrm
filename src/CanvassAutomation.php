<?php
/*******************************************************************************
 *
 *  filename    : CanvassAutomation.php
 *  last change : 2005-02-21
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2001-2005 Deane Barker, Chris Gebhardt, Michael Wilt, Tim Dearborn
 *
 ******************************************************************************/

//Include the function library
require 'Include/Config.php';
require 'Include/Functions.php';

use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\utils\RedirectUtils;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\dto\CanvassUtilities;
use EcclesiaCRM\utils\MiscUtils;

//Set the page title
$sPageTitle = _('Canvass Automation');

// Security: User must have canvasser permission to use this form
if (!SessionUser::getUser()->isCanvasserEnabled()) {
    RedirectUtils::Redirect('v2/dashboard');
    exit;
}

$iFYID = MiscUtils::CurrentFY();
if (array_key_exists('idefaultFY', $_SESSION)) {
    $iFYID = $_SESSION['idefaultFY'];
}
if (array_key_exists('FYID', $_POST)) {
    $iFYID = InputUtils::LegacyFilterInput($_POST['FYID'], 'int');
} // Use FY from the form if it was set

$_SESSION['idefaultFY'] = $iFYID; // Remember default fiscal year

$processNews = '';


// Service the action buttons
if (isset($_POST['SetDefaultFY'])) {
    if (isset($_POST['SetDefaultFYConfirm'])) {
        $processNews = CanvassUtilities::CanvassSetDefaultFY($iFYID);
    } else {
        $processNews = _('Not confirmed.');
    }
}
if (isset($_POST['AssignCanvassers'])) {
    if (isset($_POST['AssignCanvassersConfirm'])) {
        $processNews = CanvassUtilities::CanvassAssignCanvassers('Canvassers');
    } else {
        $processNews = _('Not confirmed.');
    }
}

if (isset($_POST['AssignNonPledging'])) {
    if (isset($_POST['AssignNonPledgingConfirm'])) {
        $processNews = CanvassUtilities::CanvassAssignNonPledging('BraveCanvassers', $iFYID);
    } else {
        $processNews = _('Not confirmed.');
    }
}
if (isset($_POST['ClearCanvasserAssignments'])) {
    if (isset($_POST['ClearCanvasserAssignmentsConfirm'])) {
        CanvassUtilities::CanvassClearCanvasserAssignments();
        $processNews = _('Cleared all canvasser assignments.');
    } else {
        $processNews = _('Not confirmed.');
    }
}
if (isset($_POST['SetAllOkToCanvass'])) {
    if (isset($_POST['SetAllOkToCanvassConfirm'])) {
        CanvassUtilities::CanvassSetAllOkToCanvass();
        $processNews = _('Set Ok To Canvass for all families.');
    } else {
        $processNews = _('Not confirmed.');
    }
}
if (isset($_POST['ClearAllOkToCanvass'])) {
    if (isset($_POST['ClearAllOkToCanvassConfirm'])) {
        CanvassUtilities::CanvassClearAllOkToCanvass();
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

require 'Include/Header.php';

if ($processNews != '') {
    ?>
    <div class="alert alert-warning alert-dismissable">
        <i class="fa fa-info"></i>
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <strong><span style="color: red;"><?= $processNews ?></span></strong>
    </div>
    <?php
}
?>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header with-border">
                <h3 class="card-title"><?= _('Report Details') ?></h3>
            </div>
            <div class="card-body">
                <form method="post" action="CanvassAutomation.php" name="CanvassAutomation">

                    <div class="row">
                        <div class="col-md-3">
                            <p><?= _('Fiscal Year:') ?>
                                <?php MiscUtils::PrintFYIDSelect($iFYID, 'FYID') ?>
                            </p>
                        </div>
                    </div>

                    <table border="1" width="100%" align="left" style="border-color:lightgrey;padding: 10px;"
                           cellpadding="10">
                        <tr>
                            <td align="left" width="75%">
                                <p><input type="checkbox" name="SetDefaultFYConfirm"> <?= _('Check to confirm') ?></p>
                            </td>
                            <td align="center" width="25%">
                                <input type="submit" class="btn btn-sm btn-primary"
                                       value="<?= _('Set default fiscal year') ?>"
                                       name="SetDefaultFY">
                            </td>
                        </tr>

                        <tr>
                            <td align="left" width="75%">
                                <?= _('Randomly assign canvassers to all Families.  The Canvassers are taken from the &quot;Canvassers&quot; Group.') ?>
                                <p><input type="checkbox" name="AssignCanvassersConfirm"> <?= _('Check to confirm') ?>
                                </p>
                            </td>
                            <td align="center" width="25%">
                                <input type="submit" class="btn btn-sm btn-success"
                                       value="<?= _('Assign Canvassers') ?>"
                                       name="AssignCanvassers">
                            </td>
                        </tr>

                        <tr>
                            <td align="left" width="75%">
                                <?= _('Randomly assign canvassers to non-pledging Families.  The Canvassers are taken from the &quot;BraveCanvassers&quot; Group.') ?>
                                <p><input type="checkbox" name="AssignNonPledgingConfirm"> <?= _('Check to confirm') ?>
                                </p>
                            </td>
                            <td align="center" width="25%">
                                <input type="submit" class="btn btn-sm btn-success"
                                       value="<?= _('Assign To Non Pledging') ?>"
                                       name="AssignNonPledging">
                            </td>
                        </tr>

                        <tr>
                            <td align="left" width="75%">
                                <?= _('Clear all the canvasser assignments for all families.') ?>
                                <p> <?= _('Important note: this will lose any canvasser assignments that have been made by hand.') ?></p>
                                <input type="checkbox"
                                       name="ClearCanvasserAssignmentsConfirm"> <?= _('Check to confirm') ?>
                            </td>
                            <td align="center" width="25%">
                                <input type="submit" class="btn btn-sm btn-danger"
                                       value="<?= _('Clear Canvasser Assignments') ?>"
                                       name="ClearCanvasserAssignments">
                            </td>
                        </tr>

                        <tr>
                            <td align="left" width="75%">
                                <?= _('Turn on the &quot;Ok To Canvass&quot; field for all Families.') ?>
                                <p> <?= _('Important note: this will lose any &quot;Ok To Canvass&quot; fields that have been set by hand.'); ?></p>
                                <input type="checkbox" name="SetAllOkToCanvassConfirm"> <?= _('Check to confirm') ?>
                            </td>
                            <td align="center" width="25%">
                                <input type="submit" class="btn btn-sm btn-primary"
                                       value="<?= _('Enable Canvass for All Families') ?>"
                                       name="SetAllOkToCanvass">
                            </td>
                        </tr>

                        <tr>
                            <td align="left" width="75%">
                                <?= _('Turn off the &quot;Ok To Canvass&quot; field for all Families') ?>
                                <p> <?= _('Important note: this will lose any &quot;Ok To Canvass&quot; fields that have been set by hand.'); ?></p>
                                <input type="checkbox" name="ClearAllOkToCanvassConfirm"> <?= _('Check to confirm') ?>
                            </td>
                            <td align="center" width="25%">
                                <input type="submit" class="btn btn-sm btn-primary"
                                       value="<?= _('Disable Canvass for All Families') ?>"
                                       name="ClearAllOkToCanvass">
                            </td>
                        </tr>

                        <tr>
                            <td align="left" width="75%">
                                <?= _('Generate a PDF containing briefing sheets for all Families, sorted by canvasser.') ?>
                            </td>
                            <td align="center" width="25%">
                                <input type="submit" class="btn btn-sm btn-info" value="<?= _('Briefing Sheets') ?>"
                                       name="BriefingSheets">
                            </td>
                        </tr>

                        <tr>
                            <td align="left" width="75%">
                                <?= _('Generate a PDF containing a progress report.  The progress report includes information on the overall progress of the canvass, and the progress of individual canvassers.') ?>
                            </td>
                            <td align="center" width="25%">
                                <input type="submit" class="btn btn-sm btn-info" value="<?= _('Progress Report') ?>"
                                       name="ProgressReport">
                            </td>
                        </tr>

                        <tr>
                            <td align="left" width="75%">
                                <?= _('Generate a PDF containing a summary report.  The summary report includes comments extracted from the canvass data.') ?>
                            </td>
                            <td align="center" width="25%">
                                <input type="submit" class="btn btn-sm btn-info" value="<?= _('Summary Report') ?>"
                                       name="SummaryReport">
                            </td>
                        </tr>

                        <tr>
                            <td align="left" width="75%">
                                <?= _('Generate a PDF containing a report of the families marked &quot;Not Interested&quot; by the canvasser.') ?>
                            </td>
                            <td align="center" width="25%">
                                <input type="submit" class="btn btn-sm btn-info"
                                       value="<?= _('Not Interested Report') ?>"
                                       name="NotInterestedReport">
                            </td>
                        </tr>
                    </table>
                    <br>
                </form>
            </div>
        </div>

    </div>
</div>
<?php require 'Include/Footer.php' ?>
