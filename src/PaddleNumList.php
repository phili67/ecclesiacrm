<?php
/*******************************************************************************
 *
 *  filename    : PaddleNumList.php
 *  last change : 2009-04-15
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2009 Michael Wilt
 *
 ******************************************************************************/

//Include the function library
require 'Include/Config.php';
require 'Include/Functions.php';

use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\PaddleNumQuery;

use EcclesiaCRM\Map\PersonTableMap;


$linkBack = InputUtils::LegacyFilterInputArr($_GET, 'linkBack');

if (isset ($_GET['FundRaiserID'])) {
    $iFundRaiserID = InputUtils::LegacyFilterInputArr($_GET, 'FundRaiserID');
} else {
    $iFundRaiserID = $_SESSION['iCurrentFundraiser'];
}

if ($iFundRaiserID > 0) {
    //Get the paddlenum records for this fundraiser
    $ormPaddleNumes = PaddleNumQuery::create()
        ->usePersonQuery()
        ->addAsColumn('BuyerFirstName', PersonTableMap::COL_PER_FIRSTNAME)
        ->addAsColumn('BuyerLastName', PersonTableMap::COL_PER_LASTNAME)
        ->endUse()
        ->orderByNum()
        ->findByFrId($iFundRaiserID);
} else {
    $ormPaddleNumes = null;
}

$sPageTitle = _('Buyers for this fundraiser:') . $iFundRaiserID;
require 'Include/Header.php';
?>
<form method="post"
      action="<?= SystemURLs::getRootPath() ?>/Reports/FundRaiserStatement.php?CurrentFundraiser=<?= $iFundRaiserID ?>&linkBack=FundRaiserEditor.php?FundRaiserID=<?= $iFundRaiserID ?> &CurrentFundraiser=<?= $iFundRaiserID ?>\">

    <div class="card card-body">
        <div class="row">
            <?php
            if ($iFundRaiserID > 0) {
                ?>
                <input type=button class="btn btn-default btn-sm" value="<?= _('Select all') ?>" name=SelectAll
                       onclick="javascript:document.location='PaddleNumList.php?CurrentFundraiser=<?= $iFundRaiserID ?>&SelectAll=1&linkBack=PaddleNumList.php?FundRaiserID=<?= $iFundRaiserID ?>&CurrentFundraiser=<?= $iFundRaiserID ?>'">
                <?php
            }
            ?>
            <input type=button class="btn btn-default btn-sm" value="<?= _('Select none') ?>" name=SelectNone
                   onclick="javascript:document.location='PaddleNumList.php?CurrentFundraiser=<?= $iFundRaiserID ?>&linkBack=PaddleNumList.php?FundRaiserID=<?= $iFundRaiserID ?>&CurrentFundraiser=<?= $iFundRaiserID ?>'">
            <input type=button class="btn btn-primary btn-sm" value="<?= _('Add Buyer') ?> " name=AddBuyer
                   onclick="javascript:document.location='PaddleNumEditor.php?CurrentFundraiser=<?= $iFundRaiserID ?>&linkBack=PaddleNumList.php?FundRaiserID=<?= $iFundRaiserID ?>&CurrentFundraiser=<?= $iFundRaiserID ?>'">
            <input type=button class="btn btn-primary btn-sm" value="<?= _('Add Donors to Buyer List') ?> "
                   name=AddBuyer
                   onclick="javascript:document.location='AddDonors.php?FundRaiserID=<?= $iFundRaiserID ?>'">

            <input type=submit class="btn btn-info btn-sm" value="<?= _('Generate Statements for Selected') ?>"
                   name=GenerateStatements>
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><?= _("Buyers") ?></h3>
        </div>
        <div class="card-body">
            <table cellpadding="5" cellspacing="5"
                   class="table table-striped table-bordered dataTable no-footer dtr-inline  paddleNumeList-table"
                   width="100%">

                <thead>
                <th><?= _('Select') ?></th>
                <th><?= _('Number') ?></th>
                <th><?= _('Buyer') ?></th>
                <th><?= _('Delete') ?></th>
                </thead>

                <?php
                $tog = 0;

                //Loop through all buyers
                if (!is_null($ormPaddleNumes)) {
                    foreach ($ormPaddleNumes as $num) {
                        $sRowClass = 'RowColorA'; ?>
                        <tr>
                            <td>
                                <input type="checkbox"
                                       name="Chk<?= $num->getId() ?>" <?= (isset($_GET['SelectAll'])) ? ' checked="yes"' : '' ?>></input>
                            </td>
                            <td>
                                <a href="<?= SystemURLs::getRootPath() ?>/PaddleNumEditor.php?PaddleNumID=<?= $num->getId() ?>&linkBack=PaddleNumList.php"> <?= $num->getNum() ?></a>
                            </td>

                            <td>
                                <?= $num->getBuyerFirstName() . ' ' . $num->getBuyerLastName() ?>&nbsp;
                            </td>
                            <td>
                                <a href="<?= SystemURLs::getRootPath() ?>/PaddleNumDelete.php?PaddleNumID=<?= $num->getId() ?>&linkBack=PaddleNumList.php?FundRaiserID=<?= $iFundRaiserID ?>">
                                    <i class="fa fa-trash-o" aria-hidden="true" style="color:red"></i></a>
                            </td>
                        </tr>
                        <?php
                    } // while
                } // if
                ?>

            </table>
        </div>
    </div>
</form>

<script nonce="<?= SystemURLs::getCSPNonce() ?>">
    $(document).ready(function () {
        $("#Buyers").select2();

        $('.paddleNumeList-table').DataTable({
            responsive: true,
            "language": {
                "url": window.CRM.plugin.dataTable.language.url
            },
        });
    });
</script>

<?php require 'Include/Footer.php' ?>
