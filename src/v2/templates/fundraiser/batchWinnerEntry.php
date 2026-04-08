<?php
/*******************************************************************************
 *
 *  filename    : templates/batchWinnerEntry.php
 *  last change : 2011-04-01
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2011 Michael Wilt
 *
 ******************************************************************************/

use EcclesiaCRM\Utils\RedirectUtils;

use EcclesiaCRM\DonatedItemQuery;
use EcclesiaCRM\PaddleNumQuery;

use EcclesiaCRM\Map\DonatedItemTableMap;
use EcclesiaCRM\Map\PaddleNumTableMap;
use EcclesiaCRM\Map\PersonTableMap;

use Propel\Runtime\ActiveQuery\Criteria;


if ($iCurrentFundraiser) {
    $_SESSION['iCurrentFundraiser'] = $iCurrentFundraiser;
} else {
    $iCurrentFundraiser = $_SESSION['iCurrentFundraiser'];
}

//Is this the second pass?
if (isset($_POST['EnterWinners'])) {
    for ($row = 0; $row < 10; $row += 1) {
        $buyer = $_POST["Paddle$row"];
        $di = $_POST["Item$row"];
        $price = $_POST["SellPrice$row"];
        if ($buyer > 0 && $di > 0 && $price > 0) {
            $donIt = DonatedItemQuery::create()
                ->findOneById($di);

            $donIt->setBuyerId($buyer);
            $donIt->setSellprice($price);

            $donIt->save();
        }
    }
    RedirectUtils::Redirect($linkBack);
}

// Get Items for the drop-down
$ormDonatedItems = DonatedItemQuery::create()
    ->addAsColumn('cri1', 'SUBSTR(' . DonatedItemTableMap::COL_DI_ITEM . ',1,1)')
    ->addAsColumn('cri2', 'CONVERT(SUBSTR(' . DonatedItemTableMap::COL_DI_ITEM . ',2,3), SIGNED)')
    ->orderBy('cri1')
    ->orderBy('cri2')
    ->findByFrId($iCurrentFundraiser);

//Get Paddles for the drop-down
$ormPaddles = PaddleNumQuery::create()
    ->addJoin(PaddleNumTableMap::COL_PN_PER_ID, PersonTableMap::COL_PER_ID, Criteria::LEFT_JOIN)
    ->addAsColumn('FirstName', PersonTableMap::COL_PER_FIRSTNAME)
    ->addAsColumn('LastName', PersonTableMap::COL_PER_LASTNAME)
    ->orderByNum()
    ->findByFrId($iCurrentFundraiser);

require $sRootDocument . '/Include/Header.php';
?>

<div class="card card-outline card-primary shadow-sm">
    <div class="card-header py-2 d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0"><i class="fas fa-layer-group mr-1"></i><?= _('Batch Winner Entry') ?></h3>
        <span class="badge badge-light border"><?= $ormDonatedItems->count() ?> <?= _('items') ?></span>
    </div>
    <div class="card-body py-2">
        <form id="batchWinnerForm" method="post"
              action="<?= $sRootPath ?>/v2/fundraiser/batch/winner/entry/<?= $iCurrentFundraiser ?>/<?= $origLinkBack ?>"
              name="BatchWinnerEntry">
            <div class="table-responsive">
                <table class="table table-striped table-hover table-sm mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th class="align-middle" style="width: 35%;"><?= _('Item') ?></th>
                            <th class="align-middle" style="width: 35%;"><?= _('Winner') ?></th>
                            <th class="align-middle text-right" style="width: 30%;"><?= _('Price') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        for ($row = 0; $row < 10; $row += 1) {
                            ?>
                            <tr>
                                <td class="align-middle">
                                    <select name="Item<?= $row ?>" class="form-control form-control-sm item-select">
                                        <option value="0" selected><?= _('Unassigned') ?></option>
                                        <?php
                                        foreach ($ormDonatedItems as $ormDonatedItem) {
                                            ?>
                                            <option value="<?= $ormDonatedItem->getId() ?>"><?= $ormDonatedItem->getItem() ?> - <?= $ormDonatedItem->getTitle() ?></option>
                                            <?php
                                        }
                                        ?>
                                    </select>
                                </td>
                                <td class="align-middle">
                                    <select name="Paddle<?= $row ?>" class="form-control form-control-sm paddle-select">
                                        <option value="0" selected><?= _('Unassigned') ?></option>
                                        <?php
                                        foreach ($ormPaddles as $paddle) {
                                            ?>
                                            <option value="<?= $paddle->getPerId() ?>"><?= $paddle->getNum() ?>: <?= $paddle->getFirstName() ?> <?= $paddle->getLastName() ?></option>
                                            <?php
                                        }
                                        ?>
                                    </select>
                                </td>
                                <td class="align-middle">
                                    <input type="text" name="SellPrice<?= $row ?>" class="form-control form-control-sm price-input text-right" value="" inputmode="decimal" placeholder="0.00">
                                </td>
                            </tr>
                            <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </form>
    </div>

    <div class="card-footer py-2 border-top">
        <div class="d-flex flex-wrap align-items-center">
            <button type="submit" form="batchWinnerForm" class="btn btn-sm btn-primary mr-2 mb-1" name="EnterWinners">
                <i class="fas fa-check mr-1"></i><?= _('Enter Winners') ?>
            </button>
            <button type="button" class="btn btn-sm btn-secondary mb-1" name="Cancel"
                    onclick="javascript:document.location='<?= $sRootPath ?>/<?= (strlen($linkBack) > 0)?$linkBack:'v2/dashboard' ?>';">
                <i class="fas fa-times mr-1"></i><?= _('Cancel') ?>
            </button>
        </div>
    </div>
</div>

<script src="<?= $sRootPath ?>/skin/js/fundraiser/batchWinnerEntry.js"></script>


<?php require $sRootDocument . '/Include/Footer.php'; ?>
