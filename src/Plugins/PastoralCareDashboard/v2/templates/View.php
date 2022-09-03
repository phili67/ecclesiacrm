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

    <div class="card <?= $plugin->getPlgnColor() ?> <?= $pastoralServiceStats['PastoralcareAlertType'] ?> <?= $plugin->getName() ?> <?= $Card_collapsed ?>" style="position: relative; left: 0px; top: 0px;" data-name="<?= $plugin->getName() ?>">
        <div class="card-header border-0 ui-sortable-handle">
            <h5 class="card-title"><i class="fas fa-heartbeat"></i> <?= dgettext("messages-PastoralCareDashboard","Pastoral Care") ?></h5>
            <div class="card-tools">
                <button type="button" class="btn bg-danger btn-sm" data-card-widget="remove">
                    <i class="fas fa-times"></i>
                </button>
                <button type="button" class="btn btn-danger btn-sm" data-card-widget="collapse" title="Collapse">
                    <i class="fas <?= $Card_collapsed_button?>"></i>
                </button>
            </div>
        </div>
        <div class="card-body"  style="<?= $Card_body ?>">

            <div class="row">
                <div class="col-md-7">
                    <?php
                    ?>
                    <h5 class="alert-heading"><i class="fas fa-heartbeat"></i> <i
                            class="fas fa-user"></i> <?= dgettext("messages-PastoralCareDashboard","Latest") . " " . dgettext("messages-PastoralCareDashboard","Individual Pastoral Care") ?></h5>
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
                    <p><?= dgettext("messages-PastoralCareDashboard","None") ?></p>
                    <?php
                }

                ?>
                <hr style="background-color: <?= $pastoralServiceStats['PastoralcareAlertTypeHR'] ?>; height: 1px; border: 0;">

                <h5 class="alert-heading"><i class="fas fa-heartbeat"></i> <i class="fas fa-male"
                                                                              style="right: 124px"></i>
                    <i class="fas fa-female" style="right: 67px"></i>
                    <i class="fas fa-child"></i> <?= dgettext("messages-PastoralCareDashboard","Last") . " " . dgettext("messages-PastoralCareDashboard","Family Pastoral Care") ?></h5>
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
                               class="btn btn-link-menu" style="text-decoration: none;"><?= dgettext("messages-PastoralCareDashboard","Family") ?>
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
                <p><?= dgettext("messages-PastoralCareDashboard","None") ?></p>
                <?php
            }
            ?>
        </div>

        <div class="col-md-5">
            <h5 class="alert-heading"><i class="fas fa-heartbeat"></i> <?= dgettext("messages-PastoralCareDashboard","Statistics") ?></h5>
            <label><?= dgettext("messages-PastoralCareDashboard","Period  from") . " : " . $pastoralServiceStats['startPeriod'] . " " . dgettext("messages-PastoralCareDashboard","to") . " " . $pastoralServiceStats['endPeriod'] ?></label>
            <br/>
            <?= dgettext("messages-PastoralCareDashboard","Members") ?>
            <ul>
                <li>
                    <b><?= $pastoralServiceStats['CountNotViewPersons'] ?></b> : <?= dgettext("messages-PastoralCareDashboard","Persons not reached") ?>
                    (<b><?= $pastoralServiceStats['PercentNotViewPersons'] ?> %</b>).
                </li>
                <li>
                    <b><?= $pastoralServiceStats['CountNotViewFamilies'] ?></b> : <?= dgettext("messages-PastoralCareDashboard","Families not reached") ?>
                    (<b><?= $pastoralServiceStats['PercentViewFamilies'] ?> %</b>).
                </li>
                <li>
                    <b><?= $pastoralServiceStats['CountPersonSingle'] ?></b> : <?= dgettext("messages-PastoralCareDashboard","Single Persons not reached") ?>
                    (<b><?= $pastoralServiceStats['PercentPersonSingle'] ?> %</b>).
                </li>
                <li>
                    <b><?= $pastoralServiceStats['CountNotViewRetired'] ?></b> : <?= dgettext("messages-PastoralCareDashboard","Retired Persons not reached") ?>
                    (<b><?= $pastoralServiceStats['PercentRetiredViewPersons'] ?> %</b>).
                </li>
            </ul>
            <?= dgettext("messages-PastoralCareDashboard","Young People") ?>
            <ul>
                <li>
                    <b><?= $pastoralServiceStats['CountNotViewYoung'] ?></b> : <?= dgettext("messages-PastoralCareDashboard","Young People not reached") ?>
                    (<b><?= $pastoralServiceStats['PercentViewYoung'] ?> %</b>).
                </li>
            </ul>
            <p class="text-center">
                <a class="btn btn-light align-center" href="<?= $sRootPath ?>/v2/pastoralcare/dashboard"
                   data-toggle="tooltip" data-placement="top" title="<?= dgettext("messages-PastoralCareDashboard","Visit/Call your church members") ?>"
                   style="color:black;text-decoration: none" role="button"><?= dgettext("messages-PastoralCareDashboard","Manage Pastoral Care") ?></a>
            </p>
        </div>
        </div>
    </div>
    </div>
    <?php
}
?>
