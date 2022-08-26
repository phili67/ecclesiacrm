<?php

use EcclesiaCRM\FamilyQuery;
use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\Map\PluginUserRoleTableMap;
use EcclesiaCRM\PluginQuery;

use EcclesiaCRM\SessionUser;
use EcclesiaCRM\dto\SystemConfig;

use Propel\Runtime\ActiveQuery\Criteria;

$plugin = PluginQuery::create()
    ->usePluginUserRoleQuery()
    ->addAsColumn('PlgnColor', PluginUserRoleTableMap::COL_PLGN_USR_RL_COLOR)
    ->endUse()
    ->findOneById($PluginId);


$numFamilies = 0;
$numPersons = 0;


$time = new \DateTime('now');
$newtime = $time->modify('-' . SystemConfig::getValue('iGdprExpirationDate') . ' year')->format('Y-m-d');

// when a family is completely deactivated : we seek the families with more than one member. A one person family = a fmaily with an address
$subQuery = FamilyQuery::create()
    ->withColumn('Family.Id', 'FamId')
    ->leftJoinPerson()
    ->withColumn('COUNT(Person.Id)', 'cnt')
    ->filterByDateDeactivated($newtime, Criteria::LESS_THAN)
    ->groupById();//groupBy('Family.Id');

$families = FamilyQuery::create()
    ->addSelectQuery($subQuery, 'res')
    ->where('res.cnt>1 AND Family.Id=res.FamId')
    ->find();

$numFamilies = $families->count();

// for the persons
$persons = PersonQuery::create()
    ->filterByDateDeactivated($newtime, Criteria::LESS_THAN)// GDRP
    ->_or() // or : this part is unusefull, it's only for debugging
    ->useFamilyQuery()
    ->filterByDateDeactivated($newtime, Criteria::LESS_THAN)// GDRP, when a Family is completely deactivated
    ->endUse()
    ->orderByLastName()
    ->find();

$numPersons = $persons->count();
?>

<!-- GDPR -->
<?php
if (SessionUser::getUser()->isGdrpDpoEnabled() && SystemConfig::getBooleanValue('bGDPR')) {
    if ($numPersons + $numFamilies > 0) {
        ?>
        <div class="card <?= $plugin->getPlgnColor() ?>  <?= $plugin->getName() ?>" style="position: relative; left: 0px; top: 0px;" data-name="<?= $plugin->getName() ?>" id="Menu_GDRP">
            <div class="card-header border-0 ui-sortable-handle">
                <h5 class="card-title"><?= dgettext("messages-GDPRDashboard","GDPR") ?> (<?= dgettext("messages-GDPRDashboard","message for the DPO") ?>)</h5>
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
                <div class="col-sm-1">
                </div>
                <div class="col-sm-5">
                    <?php
                    if ($numPersons) {
                        ?>
                        <?php
                        if ($numPersons == 1) {
                            ?>
                            <?= $numPersons . " " . dgettext("messages-GDPRDashboard","person must be deleted from the CRM.") ?>
                        <?php } else { ?>
                            <?= $numPersons . " " . dgettext("messages-GDPRDashboard","persons must be deleted from the CRM.") ?>
                            <?php
                        }
                        ?>
                        <br>
                        <b><?= dgettext("messages-GDPRDashboard","Click the") ?> <a
                                href="<?= $sRootPath ?>/v2/personlist/GDRP"><?= dgettext("messages-GDPRDashboard","link") ?></a> <?= dgettext("messages-GDPRDashboard","to solve the problem.") ?>
                        </b>
                        <?php
                    } else {
                        ?>
                        <?= dgettext("messages-GDPRDashboard","No Person to remove in the CRM.") ?>
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
                            <?= $numFamilies . " " . dgettext("messages-GDPRDashboard","family must be deleted from the CRM.") ?>
                        <?php } else { ?>
                            <?= $numFamilies . " " . dgettext("messages-GDPRDashboard","families must be deleted from the CRM.") ?>
                            <?php
                        }
                        ?>
                        <br>
                        <b><?= dgettext("messages-GDPRDashboard","Click the") ?> <a
                                href="<?= $sRootPath ?>/v2/familylist/GDRP"><?= dgettext("messages-GDPRDashboard","link") ?></a> <?= dgettext("messages-GDPRDashboard","to solve the problem.") ?>
                        </b>
                        <?php
                    } else {
                        ?>
                        <?= dgettext("messages-GDPRDashboard","No Family to remove in the CRM.") ?>
                        <?php
                    }
                    ?>
                </div>
                <div class="col-sm-1">
                </div>
            </div>
            </div>
        </div>
        <?php
    }
}
?>
