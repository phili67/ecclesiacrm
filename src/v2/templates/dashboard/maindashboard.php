<?php
/*******************************************************************************
 *
 *  filename    : MainDashboard.php
 *  description : menu that appears after login, shows login attempts
 *
 *  http://www.ecclesiacrm.com/
 *
 *  2020 Philippe Logel
 *
 ******************************************************************************/

// Include the function library
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Service\MailChimpService;
use EcclesiaCRM\SessionUser;

// we place this part to avoid a problem during the upgrade process
// Set the page title
require $sRootDocument . '/Include/Header.php';

$mailchimp = new MailChimpService();

$isActive = $mailchimp->isActive();

$load_Elements = false;

if ($isActive == true) {
    $isLoaded = $mailchimp->isLoaded();

    if (!$isLoaded) {
        $load_Elements = true;
        ?>
        <br/><br/><br/>
        <div class="row">
            <div class="col-md-3"></div>
            <div class="col-md-6">
                <div class="text-center">
                    <h2 class="headline text-primary"><i class="fas fa-spin fa-spinner"></i> <?= _("Loading in progress") ?> ....</h2>
                </div>

                <div class="error-content">
                    <h3>
                        <i class="fas fa-exclamation-triangle text-primary"></i> <?= gettext("Loading datas for the proper functioning of EcclesiaCRM") ?>.
                    </h3>

                    <p>
                    <ul>
                        <li>
                            <?= _("Importing data from Mailchimp") ?>.
                        </li>
                        <li>
                            <?= _("EcclesiaCRM data integrity check") ?>.
                        </li>
                        <li>
                            <?= _("Verification of the technical data of the hosting for the proper functioning of EcclesiaCRM") ?>.
                        </li>
                    </ul>
                    </p>
                </div>
            </div>
        </div>
        <?php
    }
}

if (!$load_Elements) {

    ?>

    <!-- Small boxes (Stat box) -->
    <div class="row">
        <div class="col-lg-2 col-xs-6">
            <!-- small box -->
            <div class="small-box bg-gradient-lime">
                <div class="inner">
                    <h3 id="singleCNT">
                        0
                    </h3>
                    <p>
                        <?= _('Single Persons') ?>
                    </p>
                </div>
                <div class="icon">
                    <i class="fas fa-male"></i>
                </div>
                <div class="small-box-footer">
                    <a href="<?= $sRootPath ?>/v2/people/list/single" style="color:#ffffff">
                        <?= _('View') ?> <?= _("Singles") ?> <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        </div><!-- ./col -->
        <div class="col-lg-2 col-xs-6">
            <!-- small box -->
            <div class="small-box bg-gradient-blue">
                <div class="inner">
                    <h3 id="realFamilyCNT">
                        0
                    </h3>
                    <p>
                        <?= _("Families") ?>
                    </p>
                </div>
                <div class="icon">
                    <i class="fas fa-male" style="right: 124px"></i><i class="fas fa-female" style="right: 67px"></i><i
                        class="fas fa-child"></i>
                </div>
                <div class="small-box-footer">
                    <a href="<?= $sRootPath ?>/v2/people/list/family" style="color:#ffffff">
                        <?= _('View') ?> <?= _("Familles") ?> <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        </div><!-- ./col -->
        <div class="col-lg-2 col-xs-6">
            <!-- small box -->
            <div class="small-box bg-purple">
                <div class="inner">
                    <h3 id="peopleStatsDashboard">
                        0
                    </h3>
                    <p>
                        <?= _('People') ?>
                    </p>
                </div>
                <div class="icon">
                    <i class="fas fa-user"></i>
                </div>
                <a href="<?= $sRootPath ?>/v2/people/list/person" class="small-box-footer">
                    <?= _('See All People') ?> <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div><!-- ./col -->
        <?php
        if (SystemConfig::getBooleanValue("bEnabledSundaySchool")) {
            ?>
            <div class="col-lg-2 col-xs-6">
                <!-- small box -->
                <div class="small-box bg-yellow">
                    <div class="inner">
                        <h3 id="groupStatsSundaySchool">
                            0
                        </h3>
                        <p>
                            <?= _('Sunday School Classes') ?>
                        </p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-child"></i>
                    </div>
                    <a href="<?= $sRootPath ?>/v2/sundayschool/dashboard" class="small-box-footer">
                        <?= _('More info') ?> <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div><!-- ./col -->
            <?php
        }
        ?>
        <div class="col-lg-2 col-xs-6">
            <!-- small box -->
            <div class="small-box bg-maroon">
                <div class="inner">
                    <h3 id="groupsCountDashboard">
                        0
                    </h3>
                    <p>
                        <?= _('Groups') ?>
                    </p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
                <a href="<?= $sRootPath ?>/v2/group/list" class="small-box-footer">
                    <?= _('More info') ?> <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div><!-- ./col -->
    </div><!-- /.row -->


    <!-- GDPR -->
    <?php
    if (SessionUser::getUser()->isGdrpDpoEnabled() && SystemConfig::getBooleanValue('bGDPR')) {
        if ($numPersons + $numFamilies > 0) {
            ?>
            <div class="alert bg-gradient-maroon alert-dismissible " id="Menu_GDRP">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <h4 class="alert-heading"><?= _("GDPR") ?> (<?= _("message for the DPO") ?>)</h4>
                <div class="row">
                    <div class="col-sm-1">
                    </div>
                    <div class="col-sm-5">
                        <?php
                        if ($numPersons) {
                            ?>
                            <?php
                            if ($numPersons == 1) {
                                ?>
                                <?= $numPersons . " " . _("person must be deleted from the CRM.") ?>
                            <?php } else { ?>
                                <?= $numPersons . " " . _("persons must be deleted from the CRM.") ?>
                                <?php
                            }
                            ?>
                            <br>
                            <b><?= _("Click the") ?> <a
                                    href="<?= $sRootPath ?>/v2/personlist/GDRP"><?= _("link") ?></a> <?= _("to solve the problem.") ?>
                            </b>
                            <?php
                        } else {
                            ?>
                            <?= _("No Person to remove in the CRM.") ?>
                            <?php
                        }
                        ?>
                    </div>
                    <div class="col-sm-5">
                        <?php
                        if ($numFamilies) {
                            ?>
                            <?php
                            if ($numFamilies == 1) {
                                ?>
                                <?= $numFamilies . " " . _("family must be deleted from the CRM.") ?>
                            <?php } else { ?>
                                <?= $numFamilies . " " . _("families must be deleted from the CRM.") ?>
                                <?php
                            }
                            ?>
                            <br>
                            <b><?= _("Click the") ?> <a
                                    href="<?= $sRootPath ?>/v2/familylist/GDRP"><?= _("link") ?></a> <?= _("to solve the problem.") ?>
                            </b>
                            <?php
                        } else {
                            ?>
                            <?= _("No Family to remove in the CRM.") ?>
                            <?php
                        }
                        ?>
                    </div>
                    <div class="col-sm-1">
                    </div>
                </div>
            </div>
            <?php
        }
    }
    ?>

    <!-- birthday + anniversary -->

    <?php

    if ($showBanner && ($peopleWithBirthDaysCount > 0 || $AnniversariesCount > 0) && SessionUser::getUser()->isSeePrivacyDataEnabled()) {
        ?>
        <div class="alert bg-gradient-lightblue alert-dismissible " id="Menu_Banner">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>

        <?php
        $new_unclassified_row = false;
        $cout_unclassified_people = 0;
        $unclassified = "";

        $new_row = false;
        $count_people = 0;
        $classified = "";

        $new_row = false;
        $count_people = 0;

        foreach ($peopleWithBirthDays as $peopleWithBirthDay) {
            if ($peopleWithBirthDay->getOnlyVisiblePersonView()) {
                if ($new_unclassified_row == false) {
                    $unclassified .= '<div class="row">';
                    $new_unclassified_row = true;
                    $unclassified .= '<div class="col-sm-3">';
                    $unclassified .= '<label class="checkbox-inline">';

                    if ($peopleWithBirthDay->getUrlIcon() != '') {
                        $unclassified .= '<img src="' . $sRootPath . "/skin/icons/markers/" . $peopleWithBirthDay->getUrlIcon() . '">';
                    }

                    $unclassified .= '<a href="' . $peopleWithBirthDay->getViewURI() . '" class="btn btn-link-menu" style="text-decoration: none">' . $peopleWithBirthDay->getFullNameWithAge() . '</a>';

                    $unclassified .= '</label>';
                    $unclassified .= '</div>';

                    $cout_unclassified_people += 1;
                    $cout_unclassified_people %= 4;
                    if ($cout_unclassified_people == 0) {
                        $unclassified .= '</div>';
                        $new_unclassified_row = false;
                    }
                }

                if ($new_unclassified_row == true) {
                    $unclassified .= '</div>';
                }
                continue;
            }

            // we now work with the classified date
            if ($new_row == false) {
                $classified .= '<div class="row">';
                $new_row = true;
            }

            $classified .= '<div class="col-sm-3">';
            $classified .= '<label class="checkbox-inline">';

            if ($peopleWithBirthDay->getUrlIcon() != '') {
                $classified .= '<img src="' . $sRootPath . '/skin/icons/markers/' . $peopleWithBirthDay->getUrlIcon() . '">';
            }
            $classified .= '<a href="' . $peopleWithBirthDay->getViewURI() . '" class="btn btn-link-menu" style="text-decoration: none">' . $peopleWithBirthDay->getFullNameWithAge() . '</a>';
            $classified .= '</label>';
            $classified .= '</div>';

            $count_people += 1;
            $count_people %= 4;
            if ($count_people == 0) {
                $classified .= '</div>';
                $new_row = false;
            }
        }

        if ($new_row == true) {
            $classified .= '</div>';
        }

        if (!empty($classified)) {
            ?>
            <h5 class="alert-heading"><i class="fas fa-birthday-cake"></i> <?= _("Birthdates of the day") ?></h5>
            <?php
            echo $classified;
            ?>
            <?php
        } ?>

        <?php if ($AnniversariesCount > 0) {
            if ($peopleWithBirthDaysCount > 0) {
                ?>
                <hr style="background-color: green; height: 1px; border: 0;">
                <?php
            } ?>

            <h5 class="alert-heading"><i class="fas fa-birthday-cake"></i> <?= _("Anniversaries of the day") ?></h5>

            <?php
            $new_row = false;
            $count_people = 0;

            foreach ($Anniversaries as $Anniversary) {
                if ($new_row == false) {
                    ?>
                    <div class="row">

                    <?php $new_row = true;
                } ?>
                <div class="col-md-3">
                    <label class="checkbox-inline">
                        <a href="<?= $Anniversary->getViewURI() ?>" class="btn btn-link-menu"
                           style="text-decoration: none"><?= $Anniversary->getFamilyString() ?></a>
                    </label>
                </div>

                <?php
                $count_people += 1;
                $count_people %= 4;
                if ($count_people == 0) {
                    ?>
                    </div>
                    <?php
                    $new_row = false;
                }
            }

            if ($new_row == true) {
                ?>
                </div>
                <?php
            } ?>

            <?php
        } ?>

        <?php if ($unclassified) {
            if ($peopleWithBirthDaysCount > 0) {
                ?>
                <hr style="background-color: green; height: 1px; border: 0;">
                <?php
            } ?>

            <h5 class="alert-heading"><?= _("Unclassified birthdates") ?></h5>
            <div class="row">

                <?php
                echo $unclassified;
                ?>

            </div>
            <?php
        } ?>
        </div>

        <?php
    }

    ?>

    <!-- Pastoral care -->

    <?php
// The person can see the pastoral care
    if (SessionUser::getUser()->isPastoralCareEnabled()) {
        /*
         * Now we can draw the view
         */

        ?>
        <div class="alert <?= $pastoralServiceStats['PastoralcareAlertType'] ?> alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <div class="row">
                <div class="col-md-9">
                    <?php
                    ?>
                    <h5 class="alert-heading"><i class="fa fa-heartbeat"></i> <i
                            class="fas fa-user"></i> <?= _("Latest") . " " . _("Individual Pastoral Care") ?></h5>
                    <?php
                    if ($caresPersons->count() > 0) {
                    $count_care = 0;
                    $new_row = false;

                    foreach ($caresPersons as $care) {
                        if (is_null($care->getPersonRelatedByPersonId())) {
                            continue;
                        }
                        if ($new_row == false) {
                            ?>

                            <div class="row">

                            <?php
                            $new_row = true;
                        } ?>

                        <div class="col-sm-3">
                            <label class="checkbox-inline">
                                <a href="<?= $sRootPath . "/v2/pastoralcare/person/" . $care->getPersonId() ?>"
                                   class="btn btn-link-menu"
                                   style="text-decoration: none;"><?= $care->getPersonRelatedByPersonId()->getFullName() ?>
                                    (<?= $care->getDate()->format(SystemConfig::getValue('sDateFormatLong')) ?>)</a>
                            </label>
                        </div>

                        <?php
                        $count_care += 1;
                        $count_care %= 4;
                        if ($count_care == 0) {
                            ?>
                            </div>
                            <?php
                            $new_row = false;
                        }
                    }

                    if ($new_row == true) {
                    ?>
                </div>
                <?php
                }
                } else {
                    ?>
                    <p><?= _("None") ?></p>
                    <?php
                }

                ?>
                <hr style="background-color: <?= $pastoralServiceStats['PastoralcareAlertTypeHR'] ?>; height: 1px; border: 0;">

                <h5 class="alert-heading"><i class="fa fa-heartbeat"></i> <i class="fas fa-male"
                                                                             style="right: 124px"></i>
                    <i class="fas fa-female" style="right: 67px"></i>
                    <i class="fas fa-child"></i> <?= _("Last") . " " . _("Family Pastoral Care") ?></h5>
                <?php
                if ($caresFamilies->count() > 0) {
                $count_care = 0;
                $new_row = false;

                foreach ($caresFamilies as $care) {
                    if (is_null($care->getFamily())) {
                        continue;
                    }
                    if ($new_row == false) {
                        ?>
                        <div class="row">

                        <?php $new_row = true;
                    } ?>

                    <div class="col-sm-3">
                        <label class="checkbox-inline">
                            <a href="<?= $sRootPath . "/v2/pastoralcare/family/" . $care->getFamilyId() ?>"
                               class="btn btn-link-menu" style="text-decoration: none;"><?= _("Family") ?>
                                : <?= $care->getFamily()->getName() ?>
                                (<?= $care->getDate()->format(SystemConfig::getValue('sDateFormatLong')) ?>)</a>
                        </label>
                    </div>

                    <?php
                    $count_care += 1;
                    $count_care %= 4;
                    if ($count_care == 0) {
                        ?>
                        </div>
                        <?php
                        $new_row = false;
                    }
                    ?>
                    <?php
                }

                if ($new_row == true) {
                ?>
            </div>
            <?php
            }
            } else {
                ?>
                <p><?= _("None") ?></p>
                <?php
            }
            ?>
        </div>

        <div class="col-md-3">
            <h5 class="alert-heading"><i class="fa fa-heartbeat"></i> <?= _("Statistics") ?></h5>
            <label><?= _("Period  from") . " : " . $pastoralServiceStats['startPeriod'] . " " . _("to") . " " . $pastoralServiceStats['endPeriod'] ?></label>
            <br/>
            <?= _("Members") ?>
            <ul>
                <li>
                    <b><?= $pastoralServiceStats['CountNotViewPersons'] ?></b> : <?= _("Persons not reached") ?>
                    (<b><?= $pastoralServiceStats['PercentNotViewPersons'] ?> %</b>).
                </li>
                <li>
                    <b><?= $pastoralServiceStats['CountNotViewFamilies'] ?></b> : <?= _("Families not reached") ?>
                    (<b><?= $pastoralServiceStats['PercentViewFamilies'] ?> %</b>).
                </li>
                <li>
                    <b><?= $pastoralServiceStats['CountPersonSingle'] ?></b> : <?= _("Single Persons not reached") ?>
                    (<b><?= $pastoralServiceStats['PercentPersonSingle'] ?> %</b>).
                </li>
                <li>
                    <b><?= $pastoralServiceStats['CountNotViewRetired'] ?></b> : <?= _("Retired Persons not reached") ?>
                    (<b><?= $pastoralServiceStats['PercentRetiredViewPersons'] ?> %</b>).
                </li>
            </ul>
            <?= _("Young People") ?>
            <ul>
                <li>
                    <b><?= $pastoralServiceStats['CountNotViewYoung'] ?></b> : <?= _("Young People not reached") ?>
                    (<b><?= $pastoralServiceStats['PercentViewYoung'] ?> %</b>).
                </li>
            </ul>
            <p class="text-center">
                <a class="btn btn-light align-center" href="<?= $sRootPath ?>/v2/pastoralcare/dashboard"
                   data-toggle="tooltip" data-placement="top" title="<?= _("Visit/Call your church members") ?>"
                   style="color:black;text-decoration: none" role="button"><?= _("Manage Pastoral Care") ?></a>
            </p>
        </div>
        </div>
        </div>
        <?php
    }
    ?>

    <?php
    if ($depositData && SystemConfig::getBooleanValue('bEnabledFinance')) { // If the user has Finance permissions, then let's display the deposit line chart
        ?>
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fa fa-money"
                                                  style="font-size:26px"></i> <?= _('Deposit Tracking') ?></h3>
                        <div class="card-tools pull-right">
                            <div id="deposit-graph" class="chart-legend"></div>
                        </div>
                    </div><!-- /.box-header -->
                    <div class="card-body">
                        <canvas id="deposit-lineGraph" style="height:225px; width:100%"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }  //END IF block for Finance permissions to include HTML for Deposit Chart
    ?>

    <div class="row">
        <div class="col-md-6">
            <div class="card card-gray card-tabs">
                <div class="card-header p-0 pt-1 border-bottom-0">
                    <ul class="nav nav-tabs" id="custom-tabs-two-tab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="custom-tabs-latest-families-tab" data-toggle="pill"
                               href="#custom-tabs-latest-families" role="tab"
                               aria-controls="custom-tabs-latest-families"
                               aria-selected="true">
                                <i class="fas fa-male"></i><i class="fas fa-female"></i><i class="fas fa-child"></i><i
                                    class="fas fa-plus"></i> <?= _('Latest Families') ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="custom-tabs-updated-families-tab" data-toggle="pill"
                               href="#custom-tabs-updated-families" role="tab"
                               aria-controls="custom-tabs-updated-families"
                               aria-selected="false">
                                <i class="fas fa-female"></i><i class="fas fa-child"></i><i
                                    class="fas fa-check"></i> <?= _('Updated Families') ?>
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="custom-tabs-two-tabContent">
                        <div class="tab-pane fade  active show" id="custom-tabs-latest-families" role="tabpanel"
                             aria-labelledby="custom-tabs-latest-families-tab">
                            <table class="table table-striped table-bordered data-table dataTable no-footer dtr-inline"
                                   id="latestFamiliesDashboardItem"
                                   style="width:100%">
                                <thead>
                                <tr>
                                    <th data-field="name"><?= _('Family Name') ?></th>
                                    <th data-field="address"><?= _('Address') ?></th>
                                    <th data-field="city"><?= _('Created') ?></th>
                                </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                        <div class="tab-pane fade" id="custom-tabs-updated-families" role="tabpanel"
                             aria-labelledby="custom-tabs-updated-families-tab">
                            <table class=" table table-striped table-bordered data-table dataTable no-footer dtr-inline"
                                   id="updatedFamiliesDashboardItem"
                                   style="width:100%">
                                <thead>
                                <tr>
                                    <th data-field="name"><?= _('Family Name') ?></th>
                                    <th data-field="address"><?= _('Address') ?></th>
                                    <th data-field="city"><?= _('Updated') ?></th>
                                </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <!-- /.card -->
            </div>
        </div>

        <div class="col-md-6">
            <div class="card card-gray card-tabs">
                <div class="card-header p-0 pt-1 border-bottom-0">
                    <ul class="nav nav-tabs" id="custom-tabs-two-tab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="custom-tabs-latest-members-tab" data-toggle="pill"
                               href="#custom-tabs-latest-members" role="tab" aria-controls="custom-tabs-latest-members"
                               aria-selected="false">
                                <i class="fas fa-user-plus"></i> <?= _('Latest Members') ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="custom-tabs-two-settings-tab" data-toggle="pill"
                               href="#custom-tabs-two-settings" role="tab" aria-controls="custom-tabs-two-settings"
                               aria-selected="false">
                                <i class="fas fa-user"></i><i class="fas fa-check"></i> <?= _('Updated Members') ?>
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="custom-tabs-two-tabContent">
                        <div class="tab-pane fade   active show" id="custom-tabs-latest-members" role="tabpanel"
                             aria-labelledby="custom-tabs-latest-members-tab">
                            <table class=" table table-striped table-bordered data-table dataTable no-footer dtr-inline"
                                   id="latestPersonsDashboardItem"
                                   style="width:100%">
                                <thead>
                                <tr>
                                    <th data-field="lastname"><?= _('Name') ?></th>
                                    <th data-field="address"><?= _('Address') ?></th>
                                    <th data-field="city"><?= _('Updated') ?></th>
                                </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                        <div class="tab-pane fade" id="custom-tabs-two-settings" role="tabpanel"
                             aria-labelledby="custom-tabs-two-settings-tab">
                            <table class=" table table-striped table-bordered data-table dataTable no-footer dtr-inline"
                                   id="updatedPersonsDashboardItem"
                                   style="width:100%">
                                <thead>
                                <tr>
                                    <th data-field="lastname"><?= _('Name') ?></th>
                                    <th data-field="address"><?= _('Address') ?></th>
                                    <th data-field="city"><?= _('Updated') ?></th>
                                </tr>
                                </thead>
                                <tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <!-- /.card -->
            </div>
        </div>
    </div>

    <?php
} // end of $load_Elements
?>

    <!-- this page specific inline scripts -->
    <script nonce="<?= SystemURLs::getCSPNonce() ?>">
        window.CRM.attendeesPresences = false;
        window.CRM.bEnabledFinance = <?= (SystemConfig::getBooleanValue('bEnabledFinance')) ? 'true' : 'false' ?>;
        window.CRM.depositData = <?= ($depositData) ? $depositData : "false" ?>;
        window.CRM.timeOut = <?= SystemConfig::getValue("iEventsOnDashboardPresenceTimeOut") * 1000 ?>;
    </script>

    <script src="<?= $sRootPath ?>/skin/js/menu.js"></script>

<?php require $sRootDocument . '/Include/Footer.php';
