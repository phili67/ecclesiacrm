<?php
/*******************************************************************************
 *
 *  filename    : GeoPage.php
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2004-2005 Michael Wilt
 *
 *  Additional Contributors:
 *  2006 Ed Davis
 *
 *  Copyright Contributors
 *
 ******************************************************************************/

//Include the function library
require 'Include/Config.php';
require 'Include/Functions.php';

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\ListOptionQuery;
use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\FamilyQuery;
use EcclesiaCRM\Utils\GeoUtils;
use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\Utils\OutputUtils;

use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;

function CompareDistance($elem1, $elem2)
{
    if ($elem1['Distance'] > $elem2['Distance']) {
        return 1;
    } elseif ($elem1['Distance'] == $elem2['Distance']) {
        return 0;
    } else {
        return -1;
    }
}

function SortByDistance($array)
{
    $newArr = $array;
    usort($newArr, 'CompareDistance');
    return $newArr;
}

// Create an associated array of family information sorted by distance from
// a particular family.
function FamilyInfoByDistance($iFamily)
{
    // Handle the degenerate case of no family selected by just making the array without
    // distance and bearing data, and don't bother to sort it.
    if ($iFamily) {
        // Get info for the selected family
        $selectedFamily = FamilyQuery::create()
            ->findOneById($iFamily);
    }

    // Compute distance and bearing from the selected family to all other families
    $families = FamilyQuery::create()
        ->filterByDateDeactivated(null)
        ->find();

    foreach ($families as $family) {
        $familyID = $family->getId();
        if ($iFamily) {
            $results[$familyID]['Distance'] = GeoUtils::LatLonDistance($selectedFamily->getLatitude(), $selectedFamily->getLongitude(), $family->getLatitude(), $family->getLongitude());
            $results[$familyID]['Bearing'] = GeoUtils::LatLonBearing($selectedFamily->getLatitude(), $selectedFamily->getLongitude(), $family->getLatitude(), $family->getLongitude());
        }
        $results[$familyID]['fam_Name'] = $family->getName();
        $results[$familyID]['fam_Address'] = $family->getAddress();
        $results[$familyID]['fam_Latitude'] = $family->getLatitude();
        $results[$familyID]['fam_Longitude'] = $family->getLongitude();
        $results[$familyID]['fam_ID'] = $familyID;
    }

    if ($iFamily) {
        $resultsByDistance = SortByDistance($results);
    } else {
        $resultsByDistance = $results;
    }
    return $resultsByDistance;
}

/* End of functions ... code starts here */

//Set the page title
$sPageTitle = _('Family Geographic Utilities');

// Create array with Classification Information (lst_ID = 1)
$classifications = ListOptionQuery::create()
    ->filterById(1)
    ->orderByOptionSequence()
    ->find();

unset($aClassificationName);
$aClassificationName[0] = _('Unassigned');
foreach ($classifications as $classification) {
    $aClassificationName[intval($classification->getOptionId())] = $classification->getOptionName();
}

// Create array with Family Role Information (lst_ID = 2)
$familyRoles = ListOptionQuery::create()
    ->filterById(2)
    ->orderByOptionSequence()
    ->find();

unset($aFamilyRoleName);
$aFamilyRoleName[0] = _('Unassigned');
foreach ($familyRoles as $familyRole) {
    $aFamilyRoleName[intval($familyRole->getOptionId())] = $familyRole->getOptionName();
}

// Get the Family if specified in the query string
$iFamily = -1;
$iNumNeighbors = 15;
$nMaxDistance = 10;
if (array_key_exists('Family', $_GET)) {
    $iFamily = InputUtils::LegacyFilterInput($_GET['Family'], 'int');
}
if (array_key_exists('NumNeighbors', $_GET)) {
    $iNumNeighbors = InputUtils::LegacyFilterInput($_GET['NumNeighbors'], 'int');
}

$bClassificationPost = false;
$sClassificationList = [];
$sCoordFileFormat = '';
$sCoordFileFamilies = '';
$sCoordFileName = '';

//Is this the second pass?
if (isset($_POST['FindNeighbors']) || isset($_POST['DataFile']) || isset($_POST['PersonIDList'])) {
    //Get all the variables from the request object and assign them locally
    $iFamily = InputUtils::LegacyFilterInput($_POST['Family']);
    $iNumNeighbors = InputUtils::LegacyFilterInput($_POST['NumNeighbors']);
    $nMaxDistance = InputUtils::LegacyFilterInput($_POST['MaxDistance']);
    $sCoordFileName = InputUtils::LegacyFilterInput($_POST['CoordFileName']);
    if (array_key_exists('CoordFileFormat', $_POST)) {
        $sCoordFileFormat = InputUtils::LegacyFilterInput($_POST['CoordFileFormat']);
    }
    if (array_key_exists('CoordFileFamilies', $_POST)) {
        $sCoordFileFamilies = InputUtils::LegacyFilterInput($_POST['CoordFileFamilies']);
    }

    foreach ($aClassificationName as $key => $value) {
        $sClassNum = 'Classification' . $key;
        if (isset($_POST["$sClassNum"])) {
            $bClassificationPost = true;
            $sClassificationList[] = $key;
        }
    }
}

if (isset($_POST['DataFile'])) {
    $resultsByDistance = FamilyInfoByDistance($iFamily);

    if ($sCoordFileFormat == 'GPSVisualizer') {
        $filename = $sCoordFileName . '.csv';
    } elseif ($sCoordFileFormat == 'StreetAtlasUSA') {
        $filename = $sCoordFileName . '.txt';
    }

    header("Content-Disposition: attachment; filename=$filename");

    if ($sCoordFileFormat == 'GPSVisualizer') {
        echo "Name,Latitude,Longitude\n";
    }

    $counter = 0;

    foreach ($resultsByDistance as $oneResult) {
        if ($sCoordFileFamilies == 'NeighborFamilies') {
            if ($counter++ == $iNumNeighbors) {
                break;
            }
            if ($oneResult['Distance'] > $nMaxDistance) {
                break;
            }
        }

        // Skip over the ones with no data
        if ($oneResult['fam_Latitude'] == 0) {
            continue;
        }

        if ($sCoordFileFormat == 'GPSVisualizer') {
            echo $oneResult['fam_Name'] . ',' . $oneResult['fam_Latitude'] . ',' . $oneResult['fam_Longitude'] . "\n";
        } elseif ($sCoordFileFormat == 'StreetAtlasUSA') {
            echo "BEGIN SYMBOL\n";
            echo $oneResult['fam_Latitude'] . ',' . $oneResult['fam_Longitude'] . ',' . $oneResult['fam_Name'] . ',' . "Green Star\n";
            echo "END\n";
        }
    }

    exit;
}

require 'Include/Header.php';

//Get Families for the list
$families = FamilyQuery::create()
    ->filterByDateDeactivated(null)
    ->orderByName()
    ->find(); ?>
<form class="form-horizontal" method="POST" action="GeoPage.php" name="GeoPage">
    <div class="card card-primary">
        <div class="card-header  ">
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
            <div class="card-header border-0">
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
            $resultsByDistance = FamilyInfoByDistance($iFamily);

            $counter = 0; ?>
            <!-- Column Headings -->
            <div class="car card-success">
                <div class="card-header  border-0">
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
                                <td><b><a href="<?= SystemURLs::getRootPath()?>/FamilyView.php?FamilyID=<?= $oneResult['fam_ID'] ?>"><?= $oneResult['fam_Name'] ?> </a> </b></td>
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

<script nonce="<?= SystemURLs::getCSPNonce() ?>">
    var listPeople =<?= json_encode($aPersonIDs)?>;
</script>

<?php
require 'Include/Footer.php';
?>

<script src="<?= SystemURLs::getRootPath() ?>/skin/js/GeoPage.js"></script>
