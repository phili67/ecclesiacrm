<?php

use EcclesiaCRM\PluginQuery;
use EcclesiaCRM\Map\PluginUserRoleTableMap;

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Service\PastoralCareService;
use EcclesiaCRM\SessionUser;

$plugin = PluginQuery::create()
    ->usePluginUserRoleQuery()
    ->addAsColumn('PlgnColor', PluginUserRoleTableMap::COL_PLGN_USR_RL_COLOR)
    ->endUse()
    ->findOneById($PluginId);

$pastoralServiceStats = null;
$range = null;
$caresPersons = null;
$caresFamilies = null;

if (SessionUser::getUser()->isPastoralCareEnabled()) {

    // Persons and Families never been searched

    $pastoralService = new PastoralCareService();

    /*
     *  get all the stats of the pastoral care service
     */

    $pastoralServiceStats = $pastoralService->stats();

    /*
     *  get the period for the pastoral care
     */

    $range = $pastoralService->getRange();

    /*
     *  last pastoralcare search persons for the current system user
     */

    $caresPersons = $pastoralService->lastContactedPersons();

    /*
     *  last pastoralcare search families for the current system user
     */

    $caresFamilies = $pastoralService->lastContactedFamilies();
}

$Stats = $pastoralService->stats();

?>

<!-- Pastoral care -->

<?php
// The person can see the pastoral care
if (SessionUser::getUser()->isPastoralCareEnabled()) {
    /*
     * Now we can draw the view
     */

?>

    <div class="card <?= $plugin->getName() ?> <?= $Card_collapsed ?>" style="position: relative; left: 0px; top: 0px;" data-name="<?= $plugin->getName() ?>">
        <div class="card-header border-0 ui-sortable-handle">
            <h5 class="card-title"><i class="fas fa-heartbeat"></i> <?= dgettext("messages-PastoralCareDashboard", "Pastoral Care") ?>
                (<?= dgettext("messages-PastoralCareDashboard", "Period  from") . " : " . $pastoralServiceStats['startPeriod'] . " " . dgettext("messages-PastoralCareDashboard", "to") . " " . $pastoralServiceStats['endPeriod'] ?>)</h5>
            <div class="card-tools">
                <button type="button" class="btn btn-<?= $pastoralServiceStats['PastoralcareAlertTypeButton'] ?> btn-sm" data-card-widget="remove">
                    <i class="fas fa-times"></i>
                </button>
                <button type="button" class="btn btn-<?= $pastoralServiceStats['PastoralcareAlertTypeButton'] ?> btn-sm" data-card-widget="collapse" title="Collapse">
                    <i class="fas <?= $Card_collapsed_button ?>"></i>
                </button>
            </div>
        </div>
        <div class="card-body" style="<?= $Card_body ?>">
            <div class="container-fluid px-0">
                <div class="row">
                    <div class="col-12 col-xl-7">
                        <label class="alert-heading"> <i
                                class="fas fa-user"></i> 
                                <?= dgettext("messages-PastoralCareDashboard", "Latest") . " " . dgettext("messages-PastoralCareDashboard", "Individual Pastoral Care") ?>
                        </label>
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

                                <div class="col-6 col-sm-3">
                                    <label class="checkbox-inline">
                                        <a href="<?= $sRootPath . "/v2/pastoralcare/person/" . $care->getPersonId() ?>"
                                            class="btn btn-link-menu"
                                            style="text-decoration: none;"><?= $care->getPersonRelatedByPersonId()->getFullName() ?>
                                            (<?= $care->getDate()->format(SystemConfig::getValue('sDateFormatLong')) ?>)</a>
                                    </label>
                                </div>

                                <?php
                                $count_care += 1;    
                                $count_care %= 2;                            
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
            <p><?= dgettext("messages-PastoralCareDashboard", "None") ?></p>
        <?php
                        }

        ?>
        <hr style="background-color: <?= $pastoralServiceStats['PastoralcareAlertTypeHR'] ?>; height: 1px; border: 0;">

        <label class="alert-heading"> <i class="fas fa-male"
                style="right: 124px"></i>
            <i class="fas fa-female" style="right: 67px"></i>
            <i class="fas fa-child"></i> <?= dgettext("messages-PastoralCareDashboard", "Last") . " " . dgettext("messages-PastoralCareDashboard", "Family Pastoral Care") ?>
        </label>
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

                    <div class="col-6 col-sm-3">
                        <label class="checkbox-inline">
                            <a href="<?= $sRootPath . "/v2/pastoralcare/family/" . $care->getFamilyId() ?>"
                                class="btn btn-link-menu" style="text-decoration: none;"><?= dgettext("messages-PastoralCareDashboard", "Family") ?>
                                : <?= $care->getFamily()->getName() ?>
                                (<?= $care->getDate()->format(SystemConfig::getValue('sDateFormatLong')) ?>)</a>
                        </label>
                    </div>

                    <?php
                    $count_care += 1;
                    $count_care %= 2;
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
        <p><?= dgettext("messages-PastoralCareDashboard", "None") ?></p>
    <?php
        }
    ?>
        </div>

        <div class="col-12 col-xl-5">
            <label class="alert-heading"><i class="fa-solid fa-chart-bar"></i> <?= dgettext("messages-PastoralCareDashboard", "Statistics") ?></label>
            <div class="alert alert-default-info"><?= _("• Statistics about persons, families ... who remain to be contacted.") ?></div>
            <table class="table table-striped" width="100%">
                <thead>
                    <th><?= _('Members') ?></th>
                    <th>% <?= _('of members') . ' ' . _('to contact') ?></th>
                    <th><?= _('Count') ?></th>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <i class="fas fa-user"></i> <?= _("Persons") ?>
                        </td>
                        <td>
                            <div class="progress">
                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-<?= $Stats['personColor'] ?>" role="progressbar" style="width: <?= round($Stats['PercentNotViewPersons']) ?>%;" aria-valuenow="<?= $Stats['PercentNotViewPersons'] ?>" aria-valuemin="0" aria-valuemax="100"><?= $Stats['PercentNotViewPersons'] ?>%</div>
                            </div>
                        </td>
                        <td><span
                                class="badge bg-<?= $Stats['personColor'] ?>"><?= $Stats['CountNotViewPersons'] ?></span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <small><i class="fas fa-male"></i><i class="fas fa-female"></i><i class="fas fa-child"></i></small> <?= _("Families") ?>
                        </td>
                        <td>
                            <div class="progress">
                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-<?= $Stats['familyColor'] ?>" role="progressbar" style="width: <?= round($Stats['PercentViewFamilies']) ?>%;" aria-valuenow="<?= $Stats['PercentViewFamilies'] ?>" aria-valuemin="0" aria-valuemax="100"><?= $Stats['PercentViewFamilies'] ?>%</div>
                            </div>
                        </td>
                        <td><span
                                class="badge bg-<?= $Stats['familyColor'] ?>"><?= $Stats['CountNotViewFamilies'] ?></span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <i class="fas fa-user"></i> <?= _("Singles") ?>
                        </td>
                        <td>
                            <div class="progress">
                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-<?= $Stats['singleColor'] ?>" role="progressbar" style="width: <?= round($Stats['PercentPersonSingle']) ?>%;" aria-valuenow="<?= $Stats['PercentPersonSingle'] ?>" aria-valuemin="0" aria-valuemax="100"><?= $Stats['PercentPersonSingle'] ?>%</div>
                            </div>
                        </td>
                        <td><span
                                class="badge bg-<?= $Stats['singleColor'] ?>"><?= $Stats['CountPersonSingle'] ?></span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <i class="fas fa-user"></i> <?= _("Retired") ?>
                        </td>
                        <td>
                            <div class="progress">
                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-<?= $Stats['retiredColor'] ?>" role="progressbar" style="width: <?= round($Stats['PercentRetiredViewPersons']) ?>%;" aria-valuenow="<?= $Stats['PercentRetiredViewPersons'] ?>" aria-valuemin="0" aria-valuemax="100"><?= $Stats['PercentRetiredViewPersons'] ?>%</div>
                            </div>
                        </td>
                        <td><span
                                class="badge bg-<?= $Stats['retiredColor'] ?>"><?= $Stats['CountNotViewRetired'] ?></span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <i class="fas fa-child"></i> <?= _("Young People") ?>
                        </td>
                        <td>
                            <div class="progress">
                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-<?= $Stats['youngColor'] ?>" role="progressbar" style="width: <?= round($Stats['PercentViewYoung']) ?>%;" aria-valuenow="<?= $Stats['PercentViewYoung'] ?>" aria-valuemin="0" aria-valuemax="100"><?= $Stats['PercentViewYoung'] ?>%</div>
                            </div>
                        </td>
                        <td><span class="badge bg-<?= $Stats['youngColor'] ?>"><?= $Stats['CountNotViewYoung'] ?></span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
            </div>
        </div>
    <div class="card-footer d-flex justify-content-end">
        <a class="btn btn-<?= $pastoralServiceStats['PastoralcareAlertTypeButton'] ?>  btn-sm align-center" href="<?= $sRootPath ?>/v2/pastoralcare/dashboard"
            data-toggle="tooltip" data-placement="top" title="<?= dgettext("messages-PastoralCareDashboard", "Visit/Call your church members") ?>"
            style="color:black;text-decoration: none" role="button">
            <i class="fa fa-external-link-alt"></i> <?= dgettext("messages-PastoralCareDashboard", "Manage Pastoral Care") ?>
        </a>
    </div>
    </div>
<?php
}
?>