<?php
/*******************************************************************************
 *
 *  filename    : peopledashboard.php
 *  last change : 2019-07-01
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : 2019 Philippe Logel all right reserved not MIT licence
 *                This code can't be incorporated in another software without authorization
 *
 ******************************************************************************/

use EcclesiaCRM\SessionUser;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Utils\MiscUtils;

use EcclesiaCRM\FamilyQuery;

require $sRootDocument . '/Include/Header.php';

$families = FamilyQuery::create()->filterByDateDeactivated(NULL)->filterByLongitude(0)->_and()->filterByLatitude(0)->limit(100)->find();

?>

<!-- Quick Actions Card -->
<div class="card card-outline card-primary shadow-sm mb-3">
    <div class="card-header py-2 d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0"><i class="fas fa-user-friends mr-1"></i><?= _('People Management') ?></h3>
    </div>
    <div class="card-body py-3">
        <div class="d-flex flex-wrap gap- align-items-center" style="gap:0.5rem;">
            <?php
            if (SessionUser::getUser()->isShowMapEnabled()) {
                ?>
                <a href="<?= $sRootPath ?>/v2/people/geopage" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-globe-africa mr-1"></i><?= _('Family Geographic') ?>
                </a>
                <a href="<?= $sRootPath ?>/v2/map/-1" class="btn btn-sm btn-outline-secondary">
                    <i class="far fa-map mr-1"></i><?= _('Family Map') ?>
                </a>
                <a href="<?= $sRootPath ?>/v2/people/UpdateAllLatLon" class="btn btn-sm btn-warning">
                    <?php if ($families->count() > 0): ?>
                        <span class="badge badge-danger mr-1"><?= $families->count() ?></span>
                    <?php endif; ?>
                    <i class="fas fa-map-pin mr-1"></i><?= _('Update Coordinates') ?>
                </a>
                <?php
            }
            ?>
        </div>
        <?php
        if ($sEmailLink) {
            // Add default email if default email has been set and is not already in string
            if (SystemConfig::getValue('sToEmailAddress') != '' && !stristr($sEmailLink, SystemConfig::getValue('sToEmailAddress'))) {
                $sEmailLink .= SessionUser::getUser()->MailtoDelimiter().SystemConfig::getValue('sToEmailAddress');
            }
            $sEmailLink = urlencode($sEmailLink);  // Mailto should comply with RFC 2368
            if (SessionUser::getUser()->isEmailEnabled()) { // Does user have permission to email groups
                // Display link
                ?>
                <div class="d-flex flex-wrap gap-2 align-items-center mt-3" style="gap:0.5rem;">
                    <div class="btn-group" role="group">
                        <a class="btn btn-sm btn-success" href="mailto:<?= mb_substr($sEmailLink, 0, -3) ?>" target="_blank">
                            <i class="far fa-envelope mr-1"></i><?= _('Email All')?>
                        </a>
                        <button type="button" class="btn btn-sm btn-success dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <span class="sr-only">Menu déroulant</span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-left">
                            <?= MiscUtils::generateGroupRoleEmailDropdown($roleEmails, 'mailto:') ?>
                        </div>
                    </div>
                    <div class="btn-group" role="group">
                        <a class="btn btn-sm btn-info" href="mailto:?bcc=<?= mb_substr($sEmailLink, 0, -3) ?>" target="_blank">
                            <i class="fas fa-envelope mr-1"></i><?=_('Email All (BCC)') ?>
                        </a>
                        <button type="button" class="btn btn-sm btn-info dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <span class="sr-only">Menu déroulant</span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-left">
                            <?= MiscUtils::generateGroupRoleEmailDropdown($roleEmails, 'mailto:?bcc=') ?>
                        </div>
                    </div>
                </div>
                <?php
            }
        }
        ?>
    </div>
</div>
<!-- Stat cards -->
<div class="row mb-2">
    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
        <div class="card border-0 shadow-sm h-100" style="border-top:3px solid #1cc88a!important;">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-uppercase text-muted" style="font-size:.7rem;font-weight:700;letter-spacing:.05em;"><?= _('Singles') ?></span>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:36px;height:36px;background:rgba(28,200,138,.15);">
                        <i class="fas fa-male" style="color:#1cc88a;font-size:16px;"></i>
                    </div>
                </div>
                <div class="h2 mb-0 font-weight-bold" id="singleCNT"><?= $PeopleAndSundaySchoolCountStats['singleCount'] ?></div>
            </div>
            <div class="card-footer bg-transparent border-0 pt-0 pb-2 px-3">
                <a href="<?= $sRootPath ?>/v2/people/list/singles" class="small font-weight-bold" style="color:#1cc88a;">
                    <?= _('View Singles') ?> <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
        <div class="card border-0 shadow-sm h-100" style="border-top:3px solid #4e73df!important;">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-uppercase text-muted" style="font-size:.7rem;font-weight:700;letter-spacing:.05em;"><?= _('Families') ?></span>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:36px;height:36px;background:rgba(78,115,223,.15);">
                        <i class="fa-solid fa-people-roof" style="color:#4e73df;font-size:16px;"></i>
                    </div>
                </div>
                <div class="h2 mb-0 font-weight-bold" id="realFamilyCNT"><?= $PeopleAndSundaySchoolCountStats['familyCount'] ?></div>
            </div>
            <div class="card-footer bg-transparent border-0 pt-0 pb-2 px-3">
                <a href="<?= $sRootPath ?>/v2/people/list/family" class="small font-weight-bold" style="color:#4e73df;">
                    <?= _('View Families') ?> <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
        <div class="card border-0 shadow-sm h-100" style="border-top:3px solid #6f42c1!important;">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-uppercase text-muted" style="font-size:.7rem;font-weight:700;letter-spacing:.05em;"><?= _('People') ?></span>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:36px;height:36px;background:rgba(111,66,193,.15);">
                        <i class="fas fa-user" style="color:#6f42c1;font-size:16px;"></i>
                    </div>
                </div>
                <div class="h2 mb-0 font-weight-bold" id="peopleStatsDashboard"><?= $PeopleAndSundaySchoolCountStats['personCount'] ?></div>
            </div>
            <div class="card-footer bg-transparent border-0 pt-0 pb-2 px-3">
                <a href="<?= $sRootPath ?>/v2/people/list/person" class="small font-weight-bold" style="color:#6f42c1;">
                    <?= _('See All People') ?> <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
        </div>
    </div>
    <?php if (SystemConfig::getBooleanValue("bEnabledSundaySchool")): ?>
    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
        <div class="card border-0 shadow-sm h-100" style="border-top:3px solid #f6c23e!important;">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-uppercase text-muted" style="font-size:.7rem;font-weight:700;letter-spacing:.05em;"><?= _('Sunday School') ?></span>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:36px;height:36px;background:rgba(246,194,62,.15);">
                        <i class="fas fa-children" style="color:#f6c23e;font-size:16px;"></i>
                    </div>
                </div>
                <div class="h2 mb-0 font-weight-bold" id="groupStatsSundaySchoolKids"><?= $PeopleAndSundaySchoolCountStats['sundaySchoolCountStats']['sundaySchoolkids'] ?></div>
            </div>
            <div class="card-footer bg-transparent border-0 pt-0 pb-2 px-3">
                <a href="<?= $sRootPath ?>/v2/sundayschool/dashboard" class="small font-weight-bold" style="color:#f6c23e;">
                    <?= _('More info') ?> <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
        <div class="card border-0 shadow-sm h-100" style="border-top:3px solid #e74a3b!important;">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-uppercase text-muted" style="font-size:.7rem;font-weight:700;letter-spacing:.05em;"><?= _('Groups') ?></span>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:36px;height:36px;background:rgba(231,74,59,.15);">
                        <i class="fas fa-users" style="color:#e74a3b;font-size:16px;"></i>
                    </div>
                </div>
                <div class="h2 mb-0 font-weight-bold" id="groupsCountDashboard"><?= $PeopleAndSundaySchoolCountStats['groupsCount'] ?></div>
            </div>
            <div class="card-footer bg-transparent border-0 pt-0 pb-2 px-3">
                <a href="<?= $sRootPath ?>/v2/group/list" class="small font-weight-bold" style="color:#e74a3b;">
                    <?= _('More info') ?> <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
        <div class="card border-0 shadow-sm h-100" style="border-top:3px solid #36b9cc!important;">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-uppercase text-muted" style="font-size:.7rem;font-weight:700;letter-spacing:.05em;"><?= _('Volunteers') ?></span>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:36px;height:36px;background:rgba(54,185,204,.15);">
                        <i class="fas fa-hands-helping" style="color:#36b9cc;font-size:16px;"></i>
                    </div>
                </div>
                <div class="h2 mb-0 font-weight-bold" id="volunteerOpportunitiesCountDashboard"><?= $PeopleAndSundaySchoolCountStats['VolunteerOpportunitiesCount'] ?></div>
            </div>
            <div class="card-footer bg-transparent border-0 pt-0 pb-2 px-3">
                <a href="<?= $sRootPath ?>/v2/volunteeropportunity" class="small font-weight-bold" style="color:#36b9cc;">
                    <?= _('More info') ?> <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
        </div>
    </div>


</div><!-- /.row -->
<div class="row">
    <div class="col-lg-6 mb-3">
        <div class="card card-outline card-primary shadow-sm h-100">
            <div class="card-header border-0">
                <h3 class="card-title"><i class="fas fa-file-alt mr-2"></i><?= _('Reports') ?></h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                    <button type="button" class="btn btn-tool" data-card-widget="remove"><i class="fas fa-times"></i></button>
                </div>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <a href="<?= $sRootPath ?>/v2/group/reports" class="font-weight-bold">
                            <i class="fas fa-users mr-2 text-primary"></i><?= _('Reports on groups and roles') ?>
                        </a>
                        <small class="d-block text-muted mt-1"><?= _('Report on group and roles selected (it may be a multi-page PDF).') ?></small>
                    </li>
                    <?php if (SessionUser::getUser()->isCreateDirectoryEnabled()): ?>
                    <li class="list-group-item">
                        <a href="<?= $sRootPath ?>/v2/people/directory/report" class="font-weight-bold">
                            <i class="fas fa-address-book mr-2 text-primary"></i><?= _('People Directory') ?>
                        </a>
                        <small class="d-block text-muted mt-1"><?= _('Printable directory of all people, grouped by family where assigned') ?></small>
                    </li>
                    <?php endif; ?>
                    <?php if (SessionUser::getUser()->isFinanceEnabled()): ?>
                    <li class="list-group-item">
                        <a href="<?= $sRootPath ?>/v2/people/ReminderReport" class="font-weight-bold">
                            <i class="fas fa-hand-holding-usd mr-2 text-primary"></i><?= _('Pledge Reminder Report') ?>
                        </a>
                        <small class="d-block text-muted mt-1"><?= _('Printable Pledge Reminder of all people, grouped by family where assigned') ?></small>
                    </li>
                    <?php endif; ?>
                    <li class="list-group-item">
                        <a href="<?= $sRootPath ?>/v2/people/LettersAndLabels" class="font-weight-bold">
                            <i class="fas fa-envelope-open-text mr-2 text-primary"></i><?= _('Letters and mailing labels for data confirmations') ?>
                        </a>
                        <small class="d-block text-muted mt-1"><?= _('Generate letters and mailing labels for data confirmations.') ?></small>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-lg-6 mb-3">
        <div class="card card-outline card-primary shadow-sm h-100">
            <div class="card-header border-0">
                <h3 class="card-title"><i class="fas fa-sync-alt mr-2"></i><?= _('Self Update') ?> <?= _('Reports') ?></h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                    <button type="button" class="btn btn-tool" data-card-widget="remove"><i class="fas fa-times"></i></button>
                </div>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <a href="<?= $sRootPath ?>/members/self-register.php" class="font-weight-bold">
                            <i class="fas fa-user-plus mr-2 text-primary"></i><?= _('Self Register') ?> <?= _('Reports') ?>
                        </a>
                        <small class="d-block text-muted mt-1"><?= _('List families that were created via self registration.') ?></small>
                    </li>
                    <li class="list-group-item">
                        <a href="<?= $sRootPath ?>/members/self-verify-updates.php" class="font-weight-bold">
                            <i class="fas fa-check-circle mr-2 text-primary"></i><?= _('Self Verify Updates') ?>
                        </a>
                        <small class="d-block text-muted mt-1"><?= _('Families who commented via self verify links') ?></small>
                    </li>
                    <li class="list-group-item">
                        <a href="<?= $sRootPath ?>/members/online-pending-verify.php" class="font-weight-bold">
                            <i class="fas fa-hourglass-half mr-2 text-primary"></i><?= _('Pending Self Verify') ?>
                        </a>
                        <small class="d-block text-muted mt-1"><?= _('Families with valid self verify links') ?></small>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-lg-6 mb-3">
        <div class="card card-outline card-secondary shadow-sm">
            <div class="card-header border-0">
                <h3 class="card-title"><i class="fas fa-chart-pie mr-2"></i><?= _('Family Roles') ?></h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                    <button type="button" class="btn btn-tool" data-card-widget="remove"><i class="fas fa-times"></i></button>
                </div>
            </div>
            <div class="card-body no-padding">
                <?php
                $demColors = ['#4e73df','#e74a3b','#36b9cc','#f6c23e','#1cc88a','#858796','#fd7e14','#6f42c1'];
                $demIdx = 0;
                ?>
                <table class="table table-sm mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th><?= _('Role / Gender') ?></th>
                            <th><?= _('% of People') ?></th>
                            <th class="text-center" style="width:55px"><?= _('Count') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($demographicStats as $demStat):
                        $demPct = $personCount > 0 ? round($demStat['value'] / $personCount * 100) : 0;
                        $demColor = $demColors[$demIdx % count($demColors)];
                        $demIdx++;
                    ?>
                        <tr>
                            <td class="align-middle">
                                <a href="<?= $sRootPath ?>/v2/people/list/person/<?= $demStat['gender'] ?>/<?= $demStat['role'] ?>/-1"><?= _($demStat['key']) ?></a>
                            </td>
                            <td class="align-middle">
                                <div class="d-flex align-items-center">
                                    <div class="progress flex-grow-1 mr-2" style="height:8px;border-radius:4px;">
                                        <div class="progress-bar" role="progressbar"
                                             style="width:<?= $demPct ?>%;background-color:<?= $demColor ?>;border-radius:4px;"
                                             aria-valuenow="<?= $demPct ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <small class="text-muted" style="min-width:32px;"><?= $demPct ?>%</small>
                                </div>
                            </td>
                            <td class="text-center align-middle">
                                <span class="badge badge-pill" style="background-color:<?= $demColor ?>;color:#fff;"><?= $demStat['value'] ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-6 mb-3">
        <div class="card card-outline card-secondary shadow-sm">
            <div class="card-header border-0">
                <h3 class="card-title"><i class="fas fa-chart-bar mr-2"></i><?= _('People Classification') ?></h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                    <button type="button" class="btn btn-tool" data-card-widget="remove"><i class="fas fa-times"></i></button>
                </div>
            </div>
            <?php
            $clsColors = ['#1cc88a','#4e73df','#f6c23e','#e74a3b','#36b9cc','#fd7e14','#6f42c1','#858796'];
            $clsIdx = 0;
            ?>
            <div class="card-body no-padding">
                <table class="table table-sm mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th><?= _('Classification') ?></th>
                            <th><?= _('% of People') ?></th>
                            <th class="text-center" style="width:55px"><?= _('Count') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($personStats as $key => $value):
                        if ($key == 'ClassificationCount') continue;
                        $clsPct = $personStats['ClassificationCount'] > 0 ? round($value / $personStats['ClassificationCount'] * 100) : 0;
                        $clsColor = $clsColors[$clsIdx % count($clsColors)];
                        $clsIdx++;
                    ?>
                        <tr>
                            <td class="align-middle">
                                <a href='<?= $sRootPath ?>/v2/people/list/person/-1/-1/<?= $classifications->$key ?>'><?= _($key) ?></a>
                            </td>
                            <td class="align-middle">
                                <div class="d-flex align-items-center">
                                    <div class="progress flex-grow-1 mr-2" style="height:8px;border-radius:4px;">
                                        <div class="progress-bar" role="progressbar"
                                             style="width:<?= $clsPct ?>%;background-color:<?= $clsColor ?>;border-radius:4px;"
                                             aria-valuenow="<?= $clsPct ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <small class="text-muted" style="min-width:32px;"><?= $clsPct ?>%</small>
                                </div>
                            </td>
                            <td class="text-center align-middle">
                                <span class="badge badge-pill" style="background-color:<?= $clsColor ?>;color:#fff;"><?= $value ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-lg-6 mb-3">
        <div class="card card-outline card-info shadow-sm">
            <div class="card-header border-0">
                <h3 class="card-title"><i class="far fa-address-card mr-2"></i><?= _('Gender Demographics') ?></h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                    <button type="button" class="btn btn-tool" data-card-widget="remove"><i class="fas fa-times"></i></button>
                </div>
            </div>
            <div class="card-body">
                <canvas id="gender-donut" style="height:250px"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-6 mb-3">
        <div class="card card-outline card-info shadow-sm">
            <div class="card-header border-0">
                <h3 class="card-title"><i class="fas fa-birthday-cake mr-2"></i><?= _('Age Histogram') ?></h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                    <button type="button" class="btn btn-tool" data-card-widget="remove"><i class="fas fa-times"></i></button>
                </div>
            </div>
            <div class="card-body">
                <canvas id="age-stats-bar" style="height:250px"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- this page specific inline scripts -->
<script nonce="<?= $sCSPNonce ?>">
    $(function() {
        //-------------
        //- PIE CHART -
        //-------------
        // Get context with jQuery - using jQuery's .get() method.
        var PieData = <?php
            $Labels          = [];
            $Datas           = [];
            $BackgroundColor = [];
            $borderColor     = [];

            foreach ($adultsGender as $adultGender) {
                if ($adultGender->getGender() == 1) {
                    $Labels[]           = _('Men');
                    $Datas[]            = $adultGender->getNumb();
                    $BackgroundColor[]  = "rgba(78, 115, 223, 0.85)";
                    $borderColor[]      = "rgba(78, 115, 223, 1)";
                } else if ($adultGender->getGender() == 2) {
                    $Labels[]           = _('Women');
                    $Datas[]            = $adultGender->getNumb();
                    $BackgroundColor[]  = "rgba(231, 74, 59, 0.85)";
                    $borderColor[]      = "rgba(231, 74, 59, 1)";
                }
            }

            foreach ($kidsGender as $kidGender) {
                if ($kidGender->getGender() == 1) {
                    $Labels[]           = _('Boys');
                    $Datas[]            = $kidGender->getNumb();
                    $BackgroundColor[]  = "rgba(54, 185, 204, 0.85)";
                    $borderColor[]      = "rgba(54, 185, 204, 1)";
                } else if ($kidGender->getGender() == 2) {
                    $Labels[]           = _('Girls');
                    $Datas[]            = $kidGender->getNumb();
                    $BackgroundColor[]  = "rgba(246, 194, 62, 0.85)";
                    $borderColor[]      = "rgba(246, 194, 62, 1)";
                }
            }

            $datasets = new StdClass();

            $datasets->label           = _('# of People');
            $datasets->data            = $Datas;
            $datasets->backgroundColor = $BackgroundColor;
            $datasets->borderColor     = $borderColor;
            $datasets->borderWidth     = 2;
            $datasets->hoverBorderWidth = 3;


            $res = new StdClass();

            $res->datasets   = [];
            $res->datasets[] = $datasets;
            $res->labels     = $Labels;

            echo json_encode($res,JSON_NUMERIC_CHECK);
            ?>;

        var pieChartCanvas = $("#gender-donut").get(0).getContext("2d");
        var pieChart = new Chart(pieChartCanvas, {
            type: 'doughnut',
            data: PieData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutoutPercentage: 62,
                animation: {
                    animateRotate: true,
                    animateScale: true,
                    duration: 900,
                    easing: 'easeInOutQuart'
                },
                legend: {
                    display: true,
                    position: 'right',
                    labels: {
                        padding: 16,
                        usePointStyle: true,
                        fontSize: 12
                    }
                },
                tooltips: {
                    backgroundColor: 'rgba(30,30,30,0.85)',
                    titleFontSize: 13,
                    bodyFontSize: 12,
                    cornerRadius: 6,
                    callbacks: {
                        label: function(tooltipItem, data) {
                            var dataset = data.datasets[tooltipItem.datasetIndex];
                            var total = dataset.data.reduce(function(a, b) { return a + b; }, 0);
                            var current = dataset.data[tooltipItem.index];
                            var pct = total > 0 ? Math.round((current / total) * 100) : 0;
                            return ' ' + data.labels[tooltipItem.index] + ': ' + current + ' (' + pct + '%)';
                        }
                    }
                }
            }
        });

        var histDatas = <?php
            $Labels = [];
            $Datas  = [];
            $BackgroundColor = [];
            $borderColor     = [];

            foreach ($ageStats as $age => $value) {
                $datasets = new StdClass();

                $datasets->x  = $age;
                $datasets->y  = $value;

                $Labels[] = $age;
                $Datas[]  = $datasets;
                $BackgroundColor[]  = "rgba(78, 115, 223, 0.75)";
                $borderColor[]      = "rgba(78, 115, 223, 1)";
            }

            $datasets = new StdClass();

            $datasets->label           = _('# number of people');
            $datasets->data            = $Datas;
            $datasets->backgroundColor = $BackgroundColor;
            $datasets->borderColor     = $borderColor;
            $datasets->borderWidth     = 1;
            $datasets->hoverBackgroundColor = "rgba(78, 115, 223, 1)";


            $res = new StdClass();

            $res->datasets   = [];
            $res->datasets[] = $datasets;
            $res->labels     = $Labels;

            echo json_encode($res,JSON_NUMERIC_CHECK);
            ?>;

        var ageStatsCanvas = $("#age-stats-bar").get(0).getContext("2d");

        var AgeChart = new Chart(ageStatsCanvas, {
            type: 'bar',
            data: histDatas,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 900,
                    easing: 'easeInOutQuart'
                },
                legend: {
                    display: false
                },
                tooltips: {
                    backgroundColor: 'rgba(30,30,30,0.85)',
                    titleFontSize: 13,
                    bodyFontSize: 12,
                    cornerRadius: 6,
                    callbacks: {
                        label: function(tooltipItem) {
                            return ' ' + tooltipItem.yLabel + ' <?= _("people") ?>';
                        }
                    }
                },
                scales: {
                    xAxes: [{
                        display: true,
                        barPercentage: 0.85,
                        gridLines: {
                            display: false
                        },
                        scaleLabel: {
                            display: true,
                            labelString: '<?= _("Age") ?>',
                            fontSize: 12
                        },
                        ticks: {
                            maxRotation: 45,
                            minRotation: 0,
                            autoSkip: true,
                            maxTicksLimit: 20
                        }
                    }],
                    yAxes: [{
                        gridLines: {
                            color: 'rgba(0,0,0,0.06)',
                            zeroLineColor: 'rgba(0,0,0,0.15)'
                        },
                        scaleLabel: {
                            display: true,
                            labelString: '<?= _("# of People") ?>',
                            fontSize: 12
                        },
                        ticks: {
                            beginAtZero: true,
                            precision: 0
                        }
                    }]
                }
            }
        });



    });
</script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>
