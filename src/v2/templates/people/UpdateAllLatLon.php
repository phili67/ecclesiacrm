<?php
/*******************************************************************************
 *
 *  filename    : UpdateAllLatLon.php
 *  last change : 2026-04-16
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : 2019 Philippe Logel all right reserved not MIT licence                
 *
 ******************************************************************************/

use EcclesiaCRM\FamilyQuery;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\Utils\InputUtils;
use Propel\Runtime\ActiveQuery\Criteria;

$jobSessionKey = 'update_all_lat_lon_job';
$throttleDelayMs = 1500;
$recentLogLimit = 15;

if (!isset($_SESSION[$jobSessionKey]) || !is_array($_SESSION[$jobSessionKey])) {
  $_SESSION[$jobSessionKey] = [
    'running' => false,
    'initial_total' => 0,
    'processed' => 0,
    'success' => 0,
    'failed' => [],
    'last_result' => null,
    'started_at' => null,
  ];
}

$action = $_GET['action'] ?? null;

if ($action === 'export_failed_csv') {
  $delimiter = SessionUser::getUser()->CSVExportDelemiter();
  $charset = SessionUser::getUser()->CSVExportCharset();
  $failedFamilies = $_SESSION[$jobSessionKey]['failed'] ?? [];
  $failedFamilyIds = array_column($failedFamilies, 'id');
  $familyMap = [];

  if (!empty($failedFamilyIds)) {
    $families = FamilyQuery::create()
      ->filterById($failedFamilyIds)
      ->find();

    foreach ($families as $family) {
      $familyMap[$family->getId()] = $family;
    }
  }

  header('Pragma: no-cache');
  header('Expires: 0');
  header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
  header('Content-Description: File Transfer');
  header('Content-Type: text/csv;charset=' . $charset);
  header('Content-Disposition: attachment; filename=UnresolvedAddresses-' . date('Ymd-His') . '.csv');
  header('Content-Transfer-Encoding: binary');

  $out = fopen('php://output', 'w');

  if ($charset == 'UTF-8') {
    fputs($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
  }

  fputcsv($out, [
    InputUtils::translate_special_charset(_('Family ID'), $charset),
    InputUtils::translate_special_charset(_('Family'), $charset),
    InputUtils::translate_special_charset(_('Address 1'), $charset),
    InputUtils::translate_special_charset(_('Address 2'), $charset),
    InputUtils::translate_special_charset(_('City'), $charset),
    InputUtils::translate_special_charset(_('State'), $charset),
    InputUtils::translate_special_charset(_('Zip'), $charset),
    InputUtils::translate_special_charset(_('Country'), $charset),
    InputUtils::translate_special_charset(_('Address'), $charset),
  ], $delimiter);

  foreach ($failedFamilies as $failedFamily) {
    $family = $familyMap[$failedFamily['id']] ?? null;

    fputcsv($out, [
      $failedFamily['id'] ?? '',
      InputUtils::translate_special_charset($failedFamily['name'] ?? '', $charset),
      InputUtils::translate_special_charset($family?->getAddress1() ?? '', $charset),
      InputUtils::translate_special_charset($family?->getAddress2() ?? '', $charset),
      InputUtils::translate_special_charset($family?->getCity() ?? '', $charset),
      InputUtils::translate_special_charset($family?->getState() ?? '', $charset),
      InputUtils::translate_special_charset($family?->getZip() ?? '', $charset),
      InputUtils::translate_special_charset($family?->getCountry() ?? '', $charset),
      InputUtils::translate_special_charset($failedFamily['address'] ?? '', $charset),
    ], $delimiter);
  }

  fclose($out);
  exit;
}

require $sRootDocument . '/Include/Header.php';

$buildBaseQuery = static function () {
  return FamilyQuery::create()
    ->filterByDateDeactivated(null)
    ->filterByLongitude(0)
    ->_and()
    ->filterByLatitude(0);
};

$countPendingFamilies = static function (array $failedIds = []) use ($buildBaseQuery) {
  $query = $buildBaseQuery();

  if (!empty($failedIds)) {
    $query->filterById($failedIds, Criteria::NOT_IN);
  }

  return $query->count();
};

$findNextFamily = static function (array $failedIds = []) use ($buildBaseQuery) {
  $query = $buildBaseQuery();

  if (!empty($failedIds)) {
    $query->filterById($failedIds, Criteria::NOT_IN);
  }

  return $query->orderById()->findOne();
};

$pendingCount = $countPendingFamilies();

if ($action === 'reset') {
  $_SESSION[$jobSessionKey] = [
    'running' => false,
    'initial_total' => 0,
    'processed' => 0,
    'success' => 0,
    'failed' => [],
    'last_result' => null,
    'started_at' => null,
  ];
} elseif ($action === 'start') {
  $_SESSION[$jobSessionKey] = [
    'running' => true,
    'initial_total' => $pendingCount,
    'processed' => 0,
    'success' => 0,
    'failed' => [],
    'last_result' => null,
    'started_at' => time(),
  ];
} elseif ($action === 'resume') {
  $_SESSION[$jobSessionKey]['running'] = true;
  if (empty($_SESSION[$jobSessionKey]['started_at'])) {
    $_SESSION[$jobSessionKey]['started_at'] = time();
  }
  if (empty($_SESSION[$jobSessionKey]['initial_total'])) {
    $_SESSION[$jobSessionKey]['initial_total'] = $pendingCount;
  }
} elseif ($action === 'stop') {
  $_SESSION[$jobSessionKey]['running'] = false;
}

$jobState = &$_SESSION[$jobSessionKey];
$failedIds = array_column($jobState['failed'], 'id');

if ($jobState['running']) {
  $family = $findNextFamily($failedIds);

  if ($family !== null) {
    $oldLatitude = $family->getLatitude();
    $oldLongitude = $family->getLongitude();

    try {
      $family->updateLanLng();

      $family->reload();
      $newLatitude = $family->getLatitude();
      $newLongitude = $family->getLongitude();

      if (!empty($newLatitude) && !empty($newLongitude) && ($newLatitude != $oldLatitude || $newLongitude != $oldLongitude)) {
        $jobState['success']++;
        $jobState['last_result'] = [
          'status' => 'success',
          'family' => $family->getName(),
          'address' => $family->getAddress(),
          'latitude' => $newLatitude,
          'longitude' => $newLongitude,
        ];
      } else {
        $jobState['failed'][] = [
          'id' => $family->getId(),
          'name' => $family->getName(),
          'address' => $family->getAddress(),
        ];
        $jobState['last_result'] = [
          'status' => 'warning',
          'family' => $family->getName(),
          'address' => $family->getAddress(),
          'message' => _('No coordinates found for this address.'),
        ];
      }
    } catch (\Throwable $e) {
      $jobState['failed'][] = [
        'id' => $family->getId(),
        'name' => $family->getName(),
        'address' => $family->getAddress(),
      ];
      $jobState['last_result'] = [
        'status' => 'danger',
        'family' => $family->getName(),
        'address' => $family->getAddress(),
        'message' => $e->getMessage(),
      ];
    }

    $jobState['processed']++;
  } else {
    $jobState['running'] = false;
    if ($jobState['initial_total'] > 0) {
      $jobState['last_result'] = [
        'status' => 'info',
        'family' => null,
        'address' => null,
        'message' => _('Geocoding is complete.'),
      ];
    }
  }
}

$failedIds = array_column($jobState['failed'], 'id');
$remainingCount = $countPendingFamilies($failedIds);
$initialTotal = (int) $jobState['initial_total'];
$processedCount = min((int) $jobState['processed'], $initialTotal > 0 ? $initialTotal : (int) $jobState['processed']);
$successCount = (int) $jobState['success'];
$failedCount = count($jobState['failed']);
$progressPercent = $initialTotal > 0 ? (int) round(($processedCount / max($initialTotal, 1)) * 100) : 0;
$canStart = $pendingCount > 0;
$canResume = !$jobState['running'] && $remainingCount > 0 && $initialTotal > 0;
$currentPath = strtok($_SERVER['REQUEST_URI'], '?');
$autoContinueUrl = $currentPath . '?action=resume';

?>

<div class="card card-primary card-outline">
  <div class="card-header">
    <h3 class="card-title"><i class="fas fa-map-marked-alt mr-1"></i><?= _('Families without Geo Info') ?> : <?= $pendingCount ?></h3>
  </div>
  <div class="card-body">
    <div class="alert alert-light border mb-3">
      <div class="d-flex align-items-start">
        <i class="fas fa-info-circle text-success mr-2 mt-1"></i>
        <div>
          <div class="font-weight-bold"><?= _('Nominatim protection') ?></div>
          <div class="text-muted small"><?= _('The process now geocodes one family at a time, waits between requests, and can be stopped at any moment to avoid overloading Nominatim.') ?></div>
        </div>
      </div>
    </div>

    <div class="mb-3">
      <a href="<?= $currentPath ?>?action=start" class="btn btn-success <?= $canStart ? '' : 'disabled' ?>"><i class="fas fa-play mr-1"></i><?= _('Start') ?></a>
      <a href="<?= $currentPath ?>?action=resume" class="btn btn-primary <?= $canResume ? '' : 'disabled' ?>"><i class="fas fa-redo mr-1"></i><?= _('Resume') ?></a>
      <a href="<?= $currentPath ?>?action=stop" class="btn btn-warning <?= $jobState['running'] ? '' : 'disabled' ?>"><i class="fas fa-pause mr-1"></i><?= _('Stop') ?></a>
      <a href="<?= $currentPath ?>?action=reset" class="btn btn-secondary"><i class="fas fa-undo mr-1"></i><?= _('Reset') ?></a>
    </div>

    <div class="progress mb-2" style="height: 1.5rem;">
      <div class="progress-bar progress-bar-striped <?= $jobState['running'] ? 'progress-bar-animated' : '' ?>" role="progressbar" style="width: <?= $progressPercent ?>%;" aria-valuenow="<?= $progressPercent ?>" aria-valuemin="0" aria-valuemax="100">
        <?= $progressPercent ?>%
      </div>
    </div>

    <div class="row mb-3">
      <div class="col-md-3"><strong><i class="fas fa-layer-group text-muted mr-1"></i><?= _('Total queued') ?>:</strong> <?= $initialTotal ?></div>
      <div class="col-md-3"><strong><i class="fas fa-tasks text-primary mr-1"></i><?= _('Processed') ?>:</strong> <?= $processedCount ?></div>
      <div class="col-md-3"><strong><i class="fas fa-check-circle text-success mr-1"></i><?= _('Successful') ?>:</strong> <?= $successCount ?></div>
      <div class="col-md-3"><strong><i class="fas fa-hourglass-half text-warning mr-1"></i><?= _('Remaining') ?>:</strong> <?= $remainingCount ?></div>
    </div>

    <?php if (!empty($jobState['last_result'])) { ?>
      <div class="alert alert-<?= $jobState['last_result']['status'] === 'success' ? 'success' : ($jobState['last_result']['status'] === 'warning' ? 'warning' : ($jobState['last_result']['status'] === 'danger' ? 'danger' : 'info')) ?> mb-3">
        <?php if (!empty($jobState['last_result']['family'])) { ?>
          <strong>
            <i class="fas <?= $jobState['last_result']['status'] === 'success' ? 'fa-check-circle' : ($jobState['last_result']['status'] === 'warning' ? 'fa-exclamation-triangle' : ($jobState['last_result']['status'] === 'danger' ? 'fa-times-circle' : 'fa-info-circle')) ?> mr-1"></i><?= $jobState['last_result']['family'] ?>
          </strong><br>
        <?php } ?>

        <?php if (!empty($jobState['last_result']['address'])) { ?>
          <span><i class="fas fa-map-pin mr-1"></i><?= $jobState['last_result']['address'] ?></span><br>
        <?php } ?>

        <?php if ($jobState['last_result']['status'] === 'success') { ?>
          <span><i class="fas fa-location-arrow mr-1"></i><?= _('Latitude') ?>: <?= $jobState['last_result']['latitude'] ?>, <?= _('Longitude') ?>: <?= $jobState['last_result']['longitude'] ?></span>
        <?php } elseif (!empty($jobState['last_result']['message'])) { ?>
          <span><?= $jobState['last_result']['message'] ?></span>
        <?php } ?>
      </div>
    <?php } ?>

    <?php if ($jobState['running']) { ?>
      <div class="alert alert-info mb-0">
        <i class="fas fa-sync-alt mr-1"></i>
        <?= _('Automatic continuation is enabled.') ?>
        <?= sprintf(_('One request is sent every %s seconds so Nominatim is not flooded.'), number_format($throttleDelayMs / 1000, 1)) ?>
      </div>
            <script nonce="<?= \EcclesiaCRM\dto\SystemURLs::getCSPNonce() ?>">
        window.setTimeout(function () {
          window.location.href = '<?= $autoContinueUrl ?>';
        }, <?= $throttleDelayMs ?>);
      </script>
    <?php } ?>
  </div>
</div>

<?php if ($failedCount > 0) { ?>
  <div class="card card-warning card-outline">
    <div class="card-header">
      <div class="d-flex align-items-center justify-content-between">
        <h3 class="card-title mb-0"><i class="fas fa-exclamation-triangle mr-1"></i><?= _('Addresses not resolved yet') ?> : <?= $failedCount ?></h3>
        <a href="<?= $currentPath ?>?action=export_failed_csv" class="btn btn-outline-warning btn-sm">
          <i class="fas fa-file-csv mr-1"></i><?= _('Export CSV') ?>
        </a>
      </div>
    </div>
    <div class="card-body">
      <ul class="mb-0">
                <?php foreach (array_slice(array_reverse($jobState['failed']), 0, $recentLogLimit) as $failedFamily) { ?>
          <li>
            <strong><i class="fas fa-home text-warning mr-1"></i><a href="<?= FamilyQuery::create()->findPk($failedFamily['id'])->getViewURI() ?>"><?= $failedFamily['name'] ?></a></strong>
            <?php if (!empty($failedFamily['address'])) { ?>
              <span> - <?= $failedFamily['address'] ?></span>
            <?php } ?>
          </li>
        <?php } ?>
      </ul>
    </div>
  </div>
<?php } ?>

<?php require $sRootDocument . '/Include/Footer.php'; ?>