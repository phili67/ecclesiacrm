<?php
/*******************************************************************************
 *
 *  filename    : pastoralcareperson.php
 *  last change : 2020-01-03
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : 2018 Philippe Logel all right reserved not MIT licence
 *                This code can't be incoprorated in another software without any authorization
 *
 ******************************************************************************/

use EcclesiaCRM\SessionUser;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Utils\OutputUtils;
use EcclesiaCRM\dto\ChurchMetaData;
use EcclesiaCRM\dto\SystemURLs;


require $sRootDocument . '/Include/Header.php';

?>


<?php
if ($ormPastoralCares->count() == 0) {
    ?>
    <div class="alert alert-info"><?= _("Please add some records with the button below.") ?></div>

    <?php
}
$sFamilyEmails = [];
?>

<div class="card card-primary card-body">
    <div class="margin">
        <img src="/api/persons/<?= $currentPersonID ?>/photo" class="initials-image profile-user-img img-responsive img-rounded img-circle" style="width:70px; height:70px;display:inline-block">
        <div class="btn-group">
            <?php
            foreach ($ormPastoralTypeCares as $ormPastoralTypeCare) {
                $type_and_desc = $ormPastoralTypeCare->getTitle() . ((!empty($ormPastoralTypeCare->getDesc())) ? " (" . $ormPastoralTypeCare->getDesc() . ")" : "");
                ?>
                <a class="btn btn-app newPastorCare" data-typeid="<?= $ormPastoralTypeCare->getId() ?>"
                   data-visible="<?= ($ormPastoralTypeCare->getVisible()) ? 1 : 0 ?>"
                   data-typeDesc="<?= $type_and_desc ?>"><i
                        class="fas fa-sticky-note"></i><?= _("Add Pastoral Care Notes") ?></a>
                <?php
                break;
            }
            ?>
            <button type="button" class="btn btn-app dropdown-toggle" data-toggle="dropdown">
                <span class="caret"></span>
                <span class="sr-only">Menu déroulant</span>
            </button>
            <div class="dropdown-menu" role="menu">
                <?php
                foreach ($ormPastoralTypeCares as $ormPastoralTypeCare) {
                    $type_and_desc = $ormPastoralTypeCare->getTitle() . ((!empty($ormPastoralTypeCare->getDesc())) ? " (" . $ormPastoralTypeCare->getDesc() . ")" : "");
                    ?>
                    <a class="dropdown-item newPastorCare" data-typeid="<?= $ormPastoralTypeCare->getId() ?>"
                           data-visible="<?= ($ormPastoralTypeCare->getVisible()) ? 1 : 0 ?>"
                           data-typeDesc="<?= $type_and_desc ?>"><?= $type_and_desc ?></a>
                    <?php
                }
                ?>
            </div>
        </div>
        <a class="btn btn-app" href="<?= $sRootPath ?>/PrintPastoralCarePerson.php?PersonID=<?= $currentPersonID ?>"><i
                class="fas fa-print"></i> <?= _("Printable Page") ?></a>
        <a class="btn btn-app bg-orange" id="add-event"><i class="far fa-calendar-plus"></i><?= _("Appointment") ?></a>

        <div class="btn-group pull-right">
            <a class="btn btn-app filterByPastor" data-personid="<?= SessionUser::getUser()->getPerson()->getId() ?>"><i
                    class="fas fa-sticky-note"></i><?= SessionUser::getUser()->getPerson()->getFullName() ?></a>
            <button type="button" class="btn btn-app dropdown-toggle" data-toggle="dropdown">
                <span class="caret"></span>
                <span class="sr-only">Menu déroulant</span>
            </button>
            <div class="dropdown-menu" role="menu">
                <li><a class="dropdown-item filterByPastorAll"><?= _("Everyone") ?></a></li>
                <?php
                foreach ($ormPastors as $ormPastor) {
                    ?>
                    <a class="dropdown-item filterByPastor"
                           data-pastorid="<?= $ormPastor->getPastorId() ?>"><?= $ormPastor->getPastorName() ?></a>
                    <?php
                }
                ?>
            </div>
        </div>
        <div class="pull-right" style="margin-right:15px;margin-top:10px">
            <h4><?= _("Filters") ?></h4>
        </div>
    </div>
</div>

<?php
    if (!is_null($family) &&count($family->getActivatedPeople()) > 1) {
?>

    <div class="card card-default">
        <div class="card-header with-border">
            <h3 class="card-title">
                <?= _("Family Members") ?> : <a href="<?= SystemURLs::getRootPath() ?>/v2/pastoralcare/family/<?= $family->getId() ?>"><?= $family->getName()?></a>
            </h3>
            <div class="card-tools pull-right">
                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
            </div>
        </div>
        <div class="card-body">
            <table class="table user-list table-hover data-person" width="100%">
                <thead>
                <tr>
                    <th><span><?= _("Members") ?></span></th>
                    <th class="text-center"><span><?= _("Role") ?></span></th>
                    <th><span><?= _("Classification") ?></span></th>
                    <th><span><?= _("Birthday") ?></span></th>
                    <th><span><?= _("Email") ?></span></th>
                </tr>
                </thead>
                <tbody>
                <?php
                foreach ($family->getActivatedPeople() as $person) {
                    if ($currentPersonID == $person->getId() ) {
                        continue;
                    }
                    ?>
                    <tr>
                        <td>
                            <img
                                src="<?= SystemURLs::getRootPath() ?>/api/persons/<?= $person->getId() ?>/thumbnail"
                                width="40" height="40"
                                class="initials-image img-circle"/>
                            <a href="<?= SystemURLs::getRootPath() ?>/v2/pastoralcare/person/<?= $person->getId() ?>"
                               class="user-link"><?= $person->getFullName() ?> </a>
                        </td>
                        <td class="text-center">
                            <?php
                            $famRole = $person->getFamilyRoleName();
                            $labelColor = 'label-default';
                            if ($famRole == _('Head of Household')) {
                            } elseif ($famRole == _('Spouse')) {
                                $labelColor = 'label-info';
                            } elseif ($famRole == _('Child')) {
                                $labelColor = 'label-warning';
                            }
                            ?>
                            <span class='label <?= $labelColor ?>'> <?= $famRole ?></span>
                        </td>
                        <td>
                            <?= $person->getClassification() ? $person->getClassification()->getOptionName() : "" ?>
                        </td>
                        <td>
                            <?= OutputUtils::FormatBirthDate($person->getBirthYear(),
                                $person->getBirthMonth(), $person->getBirthDay(), "-", $person->getFlags()) ?>
                        </td>
                        <td>
                            <?php $tmpEmail = $person->getEmail();
                            if ($tmpEmail != "") {
                                array_push($sFamilyEmails, $tmpEmail);
                                ?>
                                <a href="mailto:<?= $tmpEmail ?>"><?= $tmpEmail ?></a>
                                <?php
                            }
                            ?>
                        </td>
                    </tr>
                    <?php
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>

<?php } ?>

<?php
if ($ormPastoralCares->count() > 0) {
    ?>
    <div class="timeline">
        <!-- timeline time label -->
        <div class="time-label">
        <span class="bg-red">
          <?= (new DateTime(''))->format($sDateFormatLong) ?>
        </span>
        </div>
        <!-- /.timeline-label -->
        <!-- timeline item -->
        <?php
        foreach ($ormPastoralCares as $ormPastoralCare) {
            ?>
            <div class="item-<?= $ormPastoralCare->getPastorId() ?> all-items">
                <i class="fas fa-clock bg-blue"></i>
                <div class="timeline-item">
      <span class="time">
          <i class="fas fa-clock"></i> <?= $ormPastoralCare->getDate()->format($sDateFormatLong . ' H:i:s') ?>
      </span>

                    <h3 class="timeline-header">
                        <b><?= $ormPastoralCare->getPastoralCareType()->getTitle() . "</b>  : " ?><a
                                href="<?= $sRootPath . "/PersonView.php?PersonID=" . $ormPastoralCare->getPastorId() ?>"><?= $ormPastoralCare->getPastorName() ?></a>
                    </h3>
                    <div class="timeline-body">
                        <?php if ($ormPastoralCare->getVisible() || $ormPastoralCare->getPastorId() == $currentPastorId) {
                        echo $ormPastoralCare->getText();
                        ?>
                    </div>
                    <div class="timeline-footer">
                        <?php
                        if (SessionUser::getUser()->isAdmin() || $ormPastoralCare->getPastorId() == $currentPastorId) {
                            ?>
                            <a class="btn btn-primary btn-xs modify-pastoral"
                               data-id="<?= $ormPastoralCare->getId() ?>"><?= _("Modify") ?></a>
                            <a class="btn btn-danger btn-xs delete-pastoral"
                               data-id="<?= $ormPastoralCare->getId() ?>"><?= _("Delete") ?></a>
                            <?php
                        }
                        ?>
                    </div>
                    <?php
                    } else {
                        ?>
                        <div class="timeline-footer">
                            <a class="btn btn-danger btn-xs delete-pastoral"
                               data-id="<?= $ormPastoralCare->getId() ?>"><?= _("Delete") ?></a>
                        </div>
                        <?php
                    }
                    ?>
                </div>
            </div>
            <?php
        }
        ?>
        <!-- END timeline item -->
        <div>
            <i class="fas fa-clock bg-gray"></i>
        </div>
    </div>

    <?php
}
?>

<div class="text-center">
    <input type="button" class="btn btn-success" value="<?= _('Return To Person View') ?>" name="Cancel"
           onclick="javascript:document.location='<?= $sRootPath . '/PersonView.php?PersonID=' . $currentPersonID ?>';">

    <input type="button" class="btn btn-default" value="<?= _('Return To PastoralCare Dashboard') ?>" name="Cancel"
           onclick="javascript:document.location='<?= $sRootPath ?>/v2/pastoralcare/dashboard';">

    <input type="button" class="btn btn-default" value="<?= _('Return To PastoralCare Members List') ?>" name="Cancel"
           onclick="javascript:document.location='<?= $sRootPath ?>/v2/pastoralcare/membersList';">
</div>

<?php require $sRootDocument . '/Include/Footer.php'; ?>

<link href="<?= $sRootPath ?>/skin/external/bootstrap-colorpicker/bootstrap-colorpicker.min.css" rel="stylesheet">

<script src="<?= $sRootPath ?>/skin/external/bootstrap-datetimepicker/bootstrap-datetimepicker.min.js"></script>
<script src="<?= $sRootPath ?>/skin/external/bootstrap-colorpicker/bootstrap-colorpicker.min.js"
        type="text/javascript"></script>

<script src="<?= $sRootPath ?>/skin/external/ckeditor/ckeditor.js"></script>
<script src="<?= $sRootPath ?>/skin/js/ckeditor/ckeditorextension.js"></script>

<script nonce="<?= $sCSPNonce ?>">
    var currentPersonID = <?= $currentPersonID ?>;
    var currentPastorId = <?= $currentPastorId ?>;
    var sPageTitle = '<?= $sPageTitle ?>';

    window.CRM.churchloc = {
        lat: <?= OutputUtils::number_dot(ChurchMetaData::getChurchLatitude()) ?>,
        lng: <?= OutputUtils::number_dot(ChurchMetaData::getChurchLongitude()) ?>};
    window.CRM.mapZoom = <?= SystemConfig::getValue("iLittleMapZoom")?>;
</script>

<script src="<?= $sRootPath ?>/skin/js/pastoralcare/PastoralCarePerson.js"></script>
<script src="<?= $sRootPath ?>/skin/js/calendar/EventEditor.js"></script>

<?php
if (SystemConfig::getValue('sMapProvider') == 'OpenStreetMap') {
    ?>
    <script src="<?= $sRootPath ?>/skin/js/calendar/OpenStreetMapEvent.js"></script>
    <?php
} else if (SystemConfig::getValue('sMapProvider') == 'GoogleMaps') {
    ?>
    <!--Google Map Scripts -->
    <script src="https://maps.googleapis.com/maps/api/js?key=<?= SystemConfig::getValue('sGoogleMapKey') ?>"></script>

    <script src="<?= $sRootPath ?>/skin/js/calendar/GoogleMapEvent.js"></script>
    <?php
} else if (SystemConfig::getValue('sMapProvider') == 'BingMaps') {
    ?>
    <script src="<?= $sRootPath ?>/skin/js/calendar/BingMapEvent.js"></script>
    <?php
}
?>
