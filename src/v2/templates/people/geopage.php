<?php
/*******************************************************************************
 *
 *  filename    : geopage.php
 *  last change : 2023-05-15
 *  description : Philippe Logel All right reserved
 *
 ******************************************************************************/

use EcclesiaCRM\PersonQuery; 

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Utils\OutputUtils;
use EcclesiaCRM\Utils\GeoUtils;

use Propel\Runtime\ActiveQuery\Criteria;

// Security
require $sRootDocument . '/Include/Header.php';
?>
<form class="form-horizontal" method="POST" action="<?= $sRootPath ?>/v2/people/geopage" name="GeoPage">
    <div class="card card-primary">
        <div class="card-header  border-1">
            <div class="card-title">
                <h4><?= _("Set your elements") ?></h4>
            </div>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label for="Family"
                       class="control-label col-xs-12 col-sm-3 col-md-3 col-lg-3"><?= _('Select Family:') ?></label>
                <div class="col-xs-12 col-sm-9">
                    <select name='Family' data-placeholder="<?= _('Select a family') ?>"
                            class="form-control choiceSelectBox"
                            style="width: 100%">
                        <option></option>
                        <?php
                        foreach ($families

                        as $family) {
                        ?>
                        <option
                            value="<?= $family->getId() ?>" <?= ($iFamily == $family->getId()) ? ' selected' : '' ?>>
                            <?= $family->getName() ?> - <?= $family->getAddress() ?>
                            <?php
                            }
                            ?>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label for="NumNeighbors"
                       class="control-label col-xs-12 col-sm-3 col-md-3 col-lg-3"><?= _('Maximum number of neighbors:') ?></label>
                <div class="col-xs-12 col-sm-9">
                    <input type="text" class= "form-control form-control-sm" name="NumNeighbors" value="<?= $iNumNeighbors ?>">
                </div>
            </div>
            <div class="form-group">
                <label for="MaxDistance" class="control-label col-xs-12 col-sm-3 col-md-3 col-lg-3">
                    <?= _('Maximum distance') . ' (' . _(SystemConfig::getValue('sDistanceUnit')) . "): " ?>
                </label>
                <div class="col-xs-12 col-sm-9">
                    <input type="text" class= "form-control form-control-sm" name="MaxDistance" value="<?= $nMaxDistance ?>">
                </div>
            </div>
            <div class="form-group">
                <label for="Classification0"
                       class="control-label col-xs-12 col-sm-3 col-md-3 col-lg-3"><?= _('Show neighbors with these classifications:') ?></label>
                <div class="row col-sm-offset-3">
                    <?php
                    foreach ($aClassificationName as $key => $value) {
                        $sClassNum = 'Classification' . $key;
                        $checked = (!$bClassificationPost || isset($_POST["$sClassNum"])); ?>
                        <div class="col-xs-6">
                            <label class="checkbox-inline">
                                <input type="checkbox" value="Guardian" value="1" name="Classification<?= $key ?>"
                                       id="<?= $value ?>" <?= ($checked ? 'checked' : '') ?> > <?= _($value) ?>
                            </label>
                        </div>
                        <?php
                    }
                    ?>
                </div>
            </div>
            <div class="form-group">
                <div class="col-xs-offset-2 col-xs-8">
                    <input type="submit" class="btn btn-primary" name="FindNeighbors"
                           value="<?= _('Show Neighbors') ?>">
                </div>
            </div>
        </div>

        <?php
        if (isset($_POST['FindNeighbors']) && !$iFamily) {
            ?>
            <div class="alert alert-warning">
                <?= _("Please select a Family.") ?>
            </div>
            <?php
        }
        ?>

        <!--Datafile section -->
        <div class="card card-secondary collapsed-card">
            <div class="card-header border-1">
                <h3 class="card-title"><?= _('Make Data File') ?></h3>
                <div class="card-tools pull-right">
                    <button class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button>
                </div><!-- /.box-tools -->
            </div><!-- /.box-header -->
            <div class="card-body">
                <div class="form-group">
                    <label for="CoordFileFormat"
                           class="control-label col-xs-12 col-sm-3 col-md-3 col-lg-3"><?= _('Data file format:') ?></label>
                    <div class="col-xs-12 col-sm-9">
                        <label class="radio-inline">
                            <input type="radio" name="CoordFileFormat"
                                   value="GPSVisualizer" <?= ($sCoordFileFormat == 'GPSVisualizer' ? ' checked' : '') ?> >
                            <?= _('GPS Visualizer') ?>
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="CoordFileFormat"
                                   value="StreetAtlasUSA" <?= ($sCoordFileFormat == 'StreetAtlasUSA' ? ' checked' : '') ?> >
                            <?= _('Street Atlas USA') ?>
                        </label>
                    </div>
                </div>
                <div class="form-group">
                    <label for="CoordFileFamilies"
                           class="control-label col-xs-12 col-sm-3 col-md-3 col-lg-3"><?= _('Include families in coordinate file:') ?></label>
                    <div class="col-xs-12 col-sm-9">
                        <label class="radio-inline">
                            <input type="radio" name="CoordFileFamilies"
                                   value="AllFamilies" <?= ($sCoordFileFamilies == 'AllFamilies' ? ' checked' : '') ?> >
                            <?= _('All Families') ?>
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="CoordFileFamilies"
                                   value="NeighborFamilies" <?= ($sCoordFileFamilies == 'NeighborFamilies' ? ' checked' : '') ?> >
                            <?= _('Neighbor Families') ?>
                        </label>
                    </div>
                </div>
                <div class="form-group">
                    <label for="CoordFileName"
                           class="control-label col-xs-12 col-sm-3 col-md-3 col-lg-3"><?= _('Coordinate data base file name:') ?></label>
                    <div class="col-xs-12 col-sm-9">
                        <input type="text" class= "form-control form-control-sm" name="CoordFileName" value="<?= $sCoordFileName ?>">
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-xs-offset-2 col-xs-8">
                        <input type="submit" class="btn btn-primary" name="DataFile"
                               value="<?= _('Make Data File') ?>">
                    </div>
                </div>
            </div><!-- /.box-body -->
        </div><!-- /.box -->


        <?php
        $aPersonIDs = [];

        if ($iFamily != 0 &&
            (isset($_POST['FindNeighbors']) ||
                isset($_POST['PersonIDList']))
        ) {
            $resultsByDistance = GeoUtils::FamilyInfoByDistance($iFamily);

            $counter = 0; ?>
            <!-- Column Headings -->
            <div class="car card-success">
                <div class="card-header  border-1">
                    <div class="card-title">
                        <h4><?= _("Results") ?></h4>
                    </div>
                </div>
                <div class="card-body">
                    <table id="neighbours" class="table table-striped table-bordered data-table dataTable no-footer"
                           cellspacing="0" role="grid">
                        <!--table class="table table-striped"-->
                        <thead>
                        <tr class="success">
                            <td><strong><?= _('Distance') ?> </strong></td>
                            <td><strong><?= _('Direction') ?></strong></td>
                            <td><strong><?= _('Name') ?>     </strong></td>
                            <td><strong><?= _('Address') ?>  </strong></td>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        foreach ($resultsByDistance as $oneResult) {
                            if ($counter >= $iNumNeighbors || $oneResult['Distance'] > $nMaxDistance) {
                                break;
                            } // Determine how many people in this family will be listed
                            $persons = PersonQuery::Create()->filterByFamId($oneResult['fam_ID']);
                            if ($bClassificationPost) {
                                $persons->_and()->filterByClsId($sClassificationList, Criteria::IN);
                            }
                            $persons->find();

                            $numListed = $persons->count();
                            if (!$numListed) { // skip familes with zero members
                                continue;
                            }
                            $counter++;
                            ?>
                            <tr class="info" style="background-color: rgba(18, 145, 190, 0.1) !important;">
                                <td><?= OutputUtils::number_localized($oneResult['Distance']) ?> </td>
                                <td><?= $oneResult['Bearing'] ?> <?= OutputUtils::GetRouteFromCoordinates($oneResult['fam_Latitude'], $oneResult['fam_Longitude']) ?>
                                </td>
                                <td><b><a href="<?= $sRootPath ?>/v2/people/family/view/<?= $oneResult['fam_ID'] ?>"><?= $oneResult['fam_Name'] ?> </a> </b></td>
                                <td>
                                    <?= OutputUtils::GetLinkMapFromCoordinates($oneResult['fam_Latitude'], $oneResult['fam_Longitude'], $oneResult['fam_Address']) ?>
                                </td>
                            </tr>
                            <?php
                            foreach ($persons as $person) {
                                if (!in_array($person->getId(), $aPersonIDs)) {
                                    $aPersonIDs[] = $person->getId();
                                } ?>
                                <tr>
                                    <td><BR></td>
                                    <td><BR></td>
                                    <td align="right"><?= $person->getFirstName() . ' ' . $person->getLastName() ?> </td>
                                    <td align="left"><?= $aClassificationName[$person->getClsId()] ?></td>
                                </tr>
                                <?php
                            }
                        } ?>
                        </tbody>
                    </table>
                </div>


                <?php
                $sPersonIDList = implode(',', $aPersonIDs); ?>

                <div class="card-footer">
                    <input type="hidden" name="PersonIDList" value="<?= $sPersonIDList ?>">

                    <div class="row">
                        <div class="col-4">
                            <a id="AddAllToCart" class="btn btn-primary"><?= _('Add All to Cart') ?></a>
                        </div>
                        <div class="col-4">
                            <input name="IntersectCart" type="submit" class="btn btn-primary"
                                   value="<?= _('Intersect with Cart') ?>">
                        </div>
                        <div class="col-4">
                            <a id="RemoveAllFromCart" class="btn btn-danger"><?= _('Remove All from Cart') ?></a>
                        </div>

                    </div>
                </div>
            </div>
            <?php
        }
        ?>

    </div><!-- /.card -->
</form>

<script nonce="<?= $sCSPNonce ?>">
    var listPeople =<?= json_encode($aPersonIDs)?>;
</script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>

<script src="<?= $sRootPath ?>/skin/js/GeoPage.js"></script>
