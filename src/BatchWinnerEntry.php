<?php
/*******************************************************************************
 *
 *  filename    : BatchWinnerEntry.php
 *  last change : 2011-04-01
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2011 Michael Wilt
 *
 ******************************************************************************/

//Include the function library
require 'Include/Config.php';
require 'Include/Functions.php';

use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\Utils\RedirectUtils;
use EcclesiaCRM\dto\SystemURLs;

use EcclesiaCRM\DonatedItemQuery;
use EcclesiaCRM\PaddleNumQuery;

use EcclesiaCRM\Map\DonatedItemTableMap;
use EcclesiaCRM\Map\PaddleNumTableMap;
use EcclesiaCRM\Map\PersonTableMap;

use Propel\Runtime\ActiveQuery\Criteria;


$linkBack = InputUtils::LegacyFilterInput($_GET['linkBack']);
$iCurrentFundraiser = InputUtils::LegacyFilterInput($_GET['CurrentFundraiser']);

if ($iCurrentFundraiser) {
    $_SESSION['iCurrentFundraiser'] = $iCurrentFundraiser;
} else {
    $iCurrentFundraiser = $_SESSION['iCurrentFundraiser'];
}

//Set the page title
$sPageTitle = _('Batch Winner Entry');

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

require 'Include/Header.php';

?>
<form method="post"
      action="<?= SystemURLs::getRootPath() ?>/BatchWinnerEntry.php?<?= 'CurrentFundraiser=' . '&linkBack=' . $linkBack ?>"
      name="BatchWinnerEntry">
<div class="card">
    <div class="card-header  border-1">
        <div class="card-title"><?= _("Articles") ?></div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4"><label><?= _('Item') ?></label></div>
            <div class="col-md-4"><label><?= _('Winner') ?></label></div>
            <div class="col-md-4"><label><?= _('Price') ?></label></div>
        </div>
        <?php
        for ($row = 0; $row < 10; $row += 1) {
            ?>
            <div class="row">
                <div class="col-md-4">
                    <select name="Item<?= $row ?>" class="form-control form-control-sm">
                        <option value="0" selected><?= _('Unassigned') ?></option>
                        <?php
                        foreach ($ormDonatedItems as $ormDonatedItem) {
                            ?>
                            <option
                                value="<?= $ormDonatedItem->getId() ?>"><?= $ormDonatedItem->getItem() ?> <?= $ormDonatedItem->getTitle() ?></option>
                            <?php
                        }

                        ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <select name="Paddle<?= $row ?>" class="form-control form-control-sm">
                        <option value="0" selected><?= _('Unassigned') ?></option>

                        <?php
                        foreach ($ormPaddles as $paddle) {
                            ?>
                            <option
                                value="<?= $paddle->getPerId() ?>"><?= $paddle->getNum() ?> <?= $paddle->getFirstName() ?> <?= $paddle->getLastName() ?></option>
                            <?php
                        }
                        ?>
                    </select>
                </div>

                <div class="col-md-4"><input type="text" name="SellPrice<?= $row ?>" id="SellPrice" value=""
                                             class= "form-control form-control-sm"></div>
            </div>
            <br/>
            <?php
        }
        ?>
    </div>
    <div class="card-footer">
        <div class="row">
            <div class="col-md-2">
            </div>
            <div class="col-md-2">
                <input type="submit" class="btn btn-primary" value="<?= _('Enter Winners') ?>" name="EnterWinners">
            </div>
            <div class="col-md-2">
                <input type="button" class="btn btn-default" value="<?= _('Cancel') ?>" name="Cancel"
                       onclick="javascript:document.location='<?php if (strlen($linkBack) > 0) {
                           echo $linkBack;
                       } else {
                           echo 'v2/dashboard';
                       } ?>';">
            </div>
        </div>
    </div>
</div>
</form>

<?php require 'Include/Footer.php' ?>
