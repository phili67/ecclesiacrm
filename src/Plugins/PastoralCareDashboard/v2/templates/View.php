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
$Stats = null;

if (SessionUser::getUser()->isPastoralCareEnabled()) {
    $pastoralService = new PastoralCareService();
    $pastoralServiceStats = $pastoralService->stats();
    $range = $pastoralService->getRange();
    $caresPersons = $pastoralService->lastContactedPersons();
    $caresFamilies = $pastoralService->lastContactedFamilies();
    $Stats = $pastoralServiceStats; // Assuming stats are the same
}

// Function to render care list
function renderCareList($cares, $type) {
    global $sRootPath;
    if ($cares->count() == 0) {
        echo '<p class="text-muted">' . dgettext("messages-PastoralCareDashboard", "None") . '</p>';
        return;
    }

    echo '<ul class="list-group list-group-flush">';

    foreach ($cares as $care) {
        if ($type === 'person' && is_null($care->getPersonRelatedByPersonId())) continue;
        if ($type === 'family' && is_null($care->getFamily())) continue;

        $id = $type === 'person' ? $care->getPersonId() : $care->getFamilyId();
        $name = $type === 'person' ? $care->getPersonRelatedByPersonId()->getFullName() : $care->getFamily()->getName();
        $url = $sRootPath . "/v2/pastoralcare/{$type}/{$id}";
        $date = $care->getDate()->format(SystemConfig::getValue('sDateFormatLong'));
        $icon = $type === 'person' ? 'fas fa-user' : 'fas fa-home';

        echo "<li class='list-group-item d-flex justify-content-between align-items-center'>
                <div>
                    <i class='{$icon} me-2 text-primary'></i>
                    <a href='{$url}' class='text-decoration-none'>{$name}</a>
                </div>
                <small class='text-muted'>{$date}</small>
              </li>";
    }

    echo '</ul>';
}

// Function to render stats table
function renderStatsTable($Stats) {
    $statsData = [
        ['icon' => 'fas fa-user', 'label' => _("Persons"), 'percent' => $Stats['PercentNotViewPersons'], 'count' => $Stats['CountNotViewPersons'], 'color' => $Stats['personColor']],
        ['icon' => 'fas fa-users', 'label' => _("Families"), 'percent' => $Stats['PercentViewFamilies'], 'count' => $Stats['CountNotViewFamilies'], 'color' => $Stats['familyColor']],
        ['icon' => 'fas fa-user', 'label' => _("Singles"), 'percent' => $Stats['PercentPersonSingle'], 'count' => $Stats['CountPersonSingle'], 'color' => $Stats['singleColor']],
        ['icon' => 'fas fa-user-clock', 'label' => _("Retired"), 'percent' => $Stats['PercentRetiredViewPersons'], 'count' => $Stats['CountNotViewRetired'], 'color' => $Stats['retiredColor']],
        ['icon' => 'fas fa-child', 'label' => _("Young People"), 'percent' => $Stats['PercentViewYoung'], 'count' => $Stats['CountNotViewYoung'], 'color' => $Stats['youngColor']],
    ];

    echo '<table class="table table-borderless mb-0">
            <thead>
                <tr>
                    <th>' . _('Members') . '</th>
                    <th>' . _('Progress') . '</th>
                    <th>' . _('Count') . '</th>
                </tr>
            </thead>
            <tbody>';

    foreach ($statsData as $stat) {
        $progressWidth = round($stat['percent']);
        echo "<tr>
                <td><i class='{$stat['icon']} me-2 text-{$stat['color']}'></i>{$stat['label']}</td>
                <td>
                    <div class='progress' style='height: 20px;'>
                        <div class='progress-bar bg-{$stat['color']}' role='progressbar' style='width: {$progressWidth}%;' aria-valuenow='{$stat['percent']}' aria-valuemin='0' aria-valuemax='100'>{$stat['percent']}%</div>
                    </div>
                </td>
                <td><span class='badge bg-{$stat['color']}'>{$stat['count']}</span></td>
              </tr>";
    }

    echo '</tbody></table>';
}

?>

<!-- Pastoral care -->
<?php if (SessionUser::getUser()->isPastoralCareEnabled() && $pastoralServiceStats): ?>
    <div class="card border-0 shadow-sm <?= $Card_collapsed ?>" style="position: relative; left: 0px; top: 0px;" data-name="<?= $plugin->getName() ?>">
        <div class="card-header <?= $pastoralServiceStats['PastoralcareAlertType'] ?> border-0 ui-sortable-handle">
            <h5 class="card-title mb-0">
                <i class="fas fa-heartbeat"></i> <?= dgettext("messages-PastoralCareDashboard", "Pastoral Care") ?>
                (<?= dgettext("messages-PastoralCareDashboard", "Period from") ?> <?= $pastoralServiceStats['startPeriod'] ?> <?= dgettext("messages-PastoralCareDashboard", "to") ?> <?= $pastoralServiceStats['endPeriod'] ?>)
            </h5>
            <div class="card-tools">
                <button type="button" class="btn btn-sm text-white" data-card-widget="remove">
                    <i class="fas fa-times"></i>
                </button>
                <button type="button" class="btn btn-sm text-white" data-card-widget="collapse" title="Collapse">
                    <i class="fas <?= $Card_collapsed_button ?>"></i>
                </button>
            </div>
        </div>
        <div class="card-body" style="<?= $Card_body ?>">
            <div class="container-fluid px-0">
                <div class="row">
                    <div class="col-12 col-xl-7">
                        <h6 class="text-black mb-3">
                            <i class="fas fa-user"></i> <?= dgettext("messages-PastoralCareDashboard", "Latest") ?> <?= dgettext("messages-PastoralCareDashboard", "Individual Pastoral Care") ?>
                        </h6>
                        <?php renderCareList($caresPersons, 'person'); ?>

                        <br>
                        <h6 class="text-black mb-3">
                            <i class="fas fa-users"></i> <?= dgettext("messages-PastoralCareDashboard", "Last") ?> <?= dgettext("messages-PastoralCareDashboard", "Family Pastoral Care") ?>
                        </h6>
                        <?php renderCareList($caresFamilies, 'family'); ?>
                    </div>

                    <div class="col-12 col-xl-5">
                        <h6 class="text-black mb-3">
                            <i class="fas fa-chart-bar"></i> <?= dgettext("messages-PastoralCareDashboard", "Statistics") ?>
                        </h6>
                        <div class="alert alert-info shadow-sm" role="alert">
                            <i class="fa fa-warning"></i> <?= _("Statistics about persons, families ... who remain to be contacted.") ?>
                        </div>
                        <?php renderStatsTable($Stats); ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-end">
            <a class="btn btn-<?= $pastoralServiceStats['PastoralcareAlertTypeButton'] ?> btn-sm text-white" href="<?= $sRootPath ?>/v2/pastoralcare/dashboard"
               data-toggle="tooltip" data-placement="top" title="<?= dgettext("messages-PastoralCareDashboard", "Visit/Call your church members") ?>"
               style="color:black;text-decoration: none" role="button">
                <i class="fas fa-external-link-alt"></i> <?= dgettext("messages-PastoralCareDashboard", "Manage Pastoral Care") ?>
            </a>
        </div>
    </div>
<?php endif; ?>