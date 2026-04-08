<?php
/*******************************************************************************
 *
 *  filename    : DonatedItemEditor.php
 *  last change : 2020-09-09
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2020 Philippe Logel
 *
 ******************************************************************************/

use EcclesiaCRM\Utils\MiscUtils;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\Utils\OutputUtils;

require $sRootDocument . '/Include/Header.php';
?>

<div class="card card-outline card-primary shadow-sm">
    <div class="card-header py-2 d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0"><i class="fas fa-tag mr-1"></i><?= _('Donated Item') ?></h3>
        <span class="badge badge-light border"><?= $iDonatedItemID > 0 ? _('Edit') : _('New') ?></span>
    </div>
    <div class="card-body py-3">
        <div class="row">
            <div class="col-lg-6 col-md-12">
                <h5 class="font-weight-semibold mb-3 border-bottom pb-2"><i class="fas fa-info-circle mr-1"></i><?= _('Basic Information') ?></h5>

                <div class="form-group mb-3">
                    <label class="font-weight-semibold" for="Item"><?= _('Item') ?></label>
                    <input type="text" name="Item" id="Item" value="<?= $sItem ?>" class="form-control form-control-sm">
                </div>

                <div class="form-group mb-3">
                    <label class="font-weight-semibold" for="Title"><?= _('Title') ?></label>
                    <input type="text" name="Title" id="Title" value="<?= htmlentities($sTitle) ?>"
                           class="form-control form-control-sm"/>
                </div>

                <div class="form-group mb-3">
                    <label class="font-weight-semibold" for="Donor"><?= _('Donor') ?></label>
                    <select name="Donor" id="Donor" class="form-control form-control-sm select2">
                        <option value="0" selected><?= _('Unassigned') ?></option>
                        <?php
                        foreach ($ormPeople as $per) {
                            echo '<option value="' . $per->getId() . '"';
                            if ($iDonor == $per->getId()) {
                                echo ' selected';
                            }
                            echo '>' . $per->getLastName() . ', ' . $per->getFirstName();
                            if (!is_null($per->getFamily())) {
                                echo ' ' . MiscUtils::FormatAddressLine($per->getFamily()->getAddress1(), $per->getFamily()->getCity(), $per->getFamily()->getState());
                            }
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group mb-0">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="Multibuy" name="Multibuy"
                               value="1" <?= $bMultibuy ? 'checked' : ''; ?>>
                        <label class="custom-control-label" for="Multibuy">
                            <?= _('Sell to everyone') ?> <span class="small text-muted">(<?= _('Multiple items') ?>)</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 col-md-12 mt-4 mt-lg-0">
                <h5 class="font-weight-semibold mb-3 border-bottom pb-2"><i class="fas fa-dollar-sign mr-1"></i><?= _('Pricing & Buyer') ?></h5>

                <div class="form-group mb-3">
                    <label class="font-weight-semibold" for="EstPrice"><?= _('Estimated Price') ?></label>
                    <input type="text" name="EstPrice" id="EstPrice"
                           value="<?= OutputUtils::number_localized($nEstPrice) ?>" class="form-control form-control-sm">
                </div>

                <div class="form-group mb-3">
                    <label class="font-weight-semibold" for="MaterialValue"><?= _('Material Value') ?></label>
                    <input type="text" name="MaterialValue" id="MaterialValue"
                           value="<?= OutputUtils::number_localized($nMaterialValue) ?>" class="form-control form-control-sm">
                </div>

                <div class="form-group mb-3">
                    <label class="font-weight-semibold" for="MinimumPrice"><?= _('Minimum Price') ?></label>
                    <input type="text" name="MinimumPrice" id="MinimumPrice"
                           value="<?= OutputUtils::number_localized($nMinimumPrice) ?>" class="form-control form-control-sm">
                </div>

                <div class="form-group mb-3">
                    <label class="font-weight-semibold" for="SellPrice"><?= _('Final Price') ?></label>
                    <input type="text" name="SellPrice" id="SellPrice"
                           value="<?= OutputUtils::number_localized($nSellPrice) ?>" class="form-control form-control-sm">
                </div>

                <div class="form-group mb-0">
                    <label class="font-weight-semibold" for="Buyer"><?= _('Buyer') ?></label>
                    <?php if ($bMultibuy) { ?>
                        <span class="badge badge-info"><?= _('Multiple') ?></span>
                    <?php } else { ?>
                        <select name="Buyer" id="Buyer" class="form-control form-control-sm select2">
                            <option value="0" selected><?= _('Unassigned') ?></option>
                            <?php
                            foreach ($ormPaddleNum as $buyer) {
                                echo '<option value="' . $buyer->getPerId() . '"';
                                if ($iBuyer == $buyer->getPerId()) {
                                    echo ' selected';
                                }
                                echo '>' . $buyer->getNum() . ': ' . $buyer->getBuyerFirstName() . ' ' . $buyer->getBuyerLastName();
                            }
                            ?>
                        </select>
                    <?php } ?>
                </div>
            </div>
        </div>

        <hr class="my-4">

        <div class="row">
            <div class="col-12">
                <h5 class="font-weight-semibold mb-3 border-bottom pb-2"><i class="fas fa-file-alt mr-1"></i><?= _('Description & Picture') ?></h5>
            </div>
            <div class="col-lg-8 col-md-12">
                <div class="form-group mb-3">
                    <label class="font-weight-semibold" for="Description"><?= _('Description') ?></label>
                    <textarea name="Description" id="Description" rows="4"
                              class="form-control form-control-sm"
                              data-toggle="tooltip" data-placement="bottom" title="<?= _("A small description to help us to sell this item") ?>"><?= htmlentities($sDescription) ?></textarea>
                </div>

                <div class="form-group mb-0">
                    <label class="font-weight-semibold" for="PictureURL"><?= _('Picture URL') ?></label>
                    <div class="input-group input-group-sm">
                        <input type="text" name="PictureURL" id="PictureURL" class="form-control"
                               value="<?= htmlentities($sPictureURL) ?>"
                               data-toggle="tooltip" data-placement="top" title="<?= _("Paste an URL or upload via EDrive") ?>">
                        <div class="input-group-append">
                            <button type="button" class="btn btn-outline-primary" id="donatedItemPicture"
                                  data-donateditemid="<?= $iDonatedItemID ?>"
                                  data-toggle="tooltip" data-placement="top" title="<?= _("Use EDrive to browse or upload") ?>">
                                <i class="fas fa-cloud-upload-alt mr-1"></i><?= _('Browse') ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-12 mt-4 mt-lg-0">
                <div class="form-group">
                    <label class="font-weight-semibold"><?= _('Preview') ?></label>
                    <div class="border rounded p-2 bg-light text-center" style="min-height: 200px;">
                        <img src="<?= (($sPictureURL != '')?htmlentities($sPictureURL):'') ?>" id="image" style="max-width: 100%; max-height: 200px; object-fit: contain;"/>
                    </div>
                </div>

                <div class="form-group mb-0">
                    <label class="font-weight-semibold d-block mb-2"><?= _('Replicate Item') ?></label>
                    <div class="input-group input-group-sm">
                        <input type="number" name="NumberCopies" id="NumberCopies" value="0" min="0" class="form-control"
                               data-toggle="tooltip" data-placement="bottom" title="<?= _("How many times to replicate") ?>">
                        <div class="input-group-append">
                            <button type="button" class="btn btn-outline-success" id="donatedItemGo" name="DonatedItemReplicate"
                                   data-donateditemid="<?= $iDonatedItemID ?>"
                                   data-toggle="tooltip" data-placement="bottom" title="<?= _("Replicate this item") ?>">
                                <i class="fas fa-copy mr-1"></i><?= _('Replicate') ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card-footer py-2 border-top">
        <div class="d-flex flex-wrap align-items-center">
            <button type="button" class="btn btn-sm btn-primary mr-2 mb-1" name="DonatedItemSubmit" id="DonatedItemSubmit">
                <i class="fas fa-save mr-1"></i><?= _('Save') ?>
            </button>
            <?php if (SessionUser::getUser()->isAddRecordsEnabled()): ?>
                <button type="button" class="btn btn-sm btn-success mr-2 mb-1" name="DonatedItemSubmitAndAdd" id="DonatedItemSubmitAndAdd">
                    <i class="fas fa-plus mr-1"></i><?= _('Save and Add') ?>
                </button>
            <?php endif; ?>
            <button type="button" class="btn btn-sm btn-secondary mb-1" name="DonatedItemCancel" id="DonatedItemCancel">
                <i class="fas fa-times mr-1"></i><?= _('Cancel') ?>
            </button>
        </div>
    </div>
</div>

<script nonce="<?= SystemURLs::getCSPNonce() ?>">
    $(function() {
        window.CRM.currentFundraiser = <?= $iCurrentFundraiser ?>;
        window.CRM.currentDonatedItemID = <?= strlen($iDonatedItemID) ? $iDonatedItemID : -1 ?>;
        window.CRM.currentPicture = "<?= $sPictureURL ?>";
    });
</script>

<script src="<?= $sRootPath ?>/skin/js/fundraiser/donatedItemEditor.js"></script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>
