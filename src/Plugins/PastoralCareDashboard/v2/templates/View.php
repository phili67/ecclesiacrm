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

?>

<!-- Pastoral care -->

<?php
// The person can see the pastoral care
if (SessionUser::getUser()->isPastoralCareEnabled()) {
    /*
     * Now we can draw the view
     */

    ?>

    <div class="card <?= $plugin->getPlgnColor() ?> <?= $pastoralServiceStats['PastoralcareAlertType'] ?> <?= $plugin->getName() ?>" style="position: relative; left: 0px; top: 0px;" data-name="<?= $plugin->getName() ?>">
        <div class="card-header border-0 ui-sortable-handle">
            <h5 class="card-title"><i class="fas fa-heartbeat"></i> <?= _("Pastoral Care") ?></h5>
            <div class="card-tools">
                <button type="button" class="btn bg-danger btn-sm" data-card-widget="remove">
                    <i class="fas fa-times"></i>
                </button>
                <button type="button" class="btn btn-danger btn-sm" data-card-widget="collapse" title="Collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">

            <div class="row">
                <div class="col-md-7">
                    <?php
                    ?>
                    <h5 class="alert-heading"><i class="fas fa-heartbeat"></i> <i
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

                <h5 class="alert-heading"><i class="fas fa-heartbeat"></i> <i class="fas fa-male"
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

        <div class="col-md-5">
            <h5 class="alert-heading"><i class="fas fa-heartbeat"></i> <?= _("Statistics") ?></h5>
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
    </div>
    <?php
}
?>
