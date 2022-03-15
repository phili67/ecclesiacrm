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

<div class="card card-primary">
    <div class="card">
        <div class="card-header  border-1">
            <div class="card-title"><label><?= _("Infos") ?></label></div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 col-md-offset-2 col-xs-6">
                    <div class="form-group">
                        <label><?= _('Item') ?>:</label>
                        <input type="text" name="Item" id="Item" value="<?= $sItem ?>" class= "form-control form-control-sm">
                    </div>

                    <div class="checkbox">
                        <label>
                            <input type="checkbox" id="Multibuy" name="Multibuy"
                                   value="1" <?= $bMultibuy ? 'checked' : ''; ?>>
                            <?= _('Sell to everyone'); ?> (<?= _('Multiple items'); ?>)
                        </label>
                    </div>

                    <div class="form-group">
                        <label><?= _('Donor'); ?>:</label>
                        <select name="Donor" id="Donor" class="form-control select2">
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

                    <div class="form-group">
                        <label><?= _('Title') ?>:</label>
                        <input type="text" name="Title" id="Title" value="<?= htmlentities($sTitle) ?>"
                               class= "form-control form-control-sm"/>
                    </div>

                    <div class="form-group">
                        <label><?= _('Estimated Price') ?>:</label>
                        <input type="text" name="EstPrice" id="EstPrice"
                               value="<?= OutputUtils::number_localized($nEstPrice) ?>" class= "form-control form-control-sm">
                    </div>

                    <div class="form-group">
                        <label><?= _('Material Value') ?>:</label>
                        <input type="text" name="MaterialValue" id="MaterialValue"
                               value="<?= OutputUtils::number_localized($nMaterialValue) ?>" class= "form-control form-control-sm">
                    </div>

                    <div class="form-group">
                        <label><?= _('Minimum Price') ?>:</label>
                        <input type="text" name="MinimumPrice" id="MinimumPrice"
                               value="<?= OutputUtils::number_localized($nMinimumPrice) ?>" class= "form-control form-control-sm">
                    </div>

                </div>

                <div class="col-md-4 col-xs-6">
                    <div class="alert alert-info">
                      <i class="fas fa-info-circle"></i> <?= _("To add some buyers use the side bar and the menu Item") ?> : "<?= _("Edit Last Fundraiser") ?>"
                    </div>

                    <div class="form-group">
                        <label><?= _('Buyer') ?>:</label>
                        <?php if ($bMultibuy) {
                            echo _('Multiple');
                        } else {
                        ?>
                        <select name="Buyer" id="Buyer" class= "form-control form-control-sm">
                            <option value="0" selected><?= _('Unassigned') ?></option>
                            <?php
                            foreach ($ormPaddleNum as $buyer) {
                                echo '<option value="' . $buyer->getPerId() . '"';
                                if ($iBuyer == $buyer->getPerId()) {
                                    echo ' selected';
                                }
                                echo '>' . $buyer->getNum() . ': ' . $buyer->getBuyerFirstName() . ' ' . $buyer->getBuyerLastName();
                            }
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label><?= _('Final Price') ?>:</label>
                        <input type="text" name="SellPrice" id="SellPrice"
                               value="<?= OutputUtils::number_localized($nSellPrice) ?>" class= "form-control form-control-sm">
                    </div>

                    <div class="form-group">
                        <label><?= _('Replicate item') ?></label>
                        <div class="input-group mb-3">
                            <!-- /btn-group -->
                            <input type="text" name="NumberCopies" id="NumberCopies" value="0" class= "form-control form-control-sm"
                                   data-toggle="tooltip" data-placement="bottom" title="<?= _("Replicate this item as many times you want") ?>">
                            <div class="input-group-append">
                                <input type="button" class="btn btn-primary" id="donatedItemGo" value="<?= _('Go') ?>"
                                       name="DonatedItemReplicate" data-donateditemid="<?= $iDonatedItemID ?>"
                                       data-toggle="tooltip" data-placement="bottom" title="<?= _("Replicate it") ?>">
                            </div>
                        </div>
                    </div>

                </div>

                <div class="col-md-8 col-md-offset-2 col-xs-12">
                    <div class="form-group">
                        <label><?= _('Description') ?>:</label>
                        <textarea name="Description" id="Description" rows="5" cols="90"
                                  class= "form-control form-control-sm"
                                  data-toggle="tooltip" data-placement="bottom" title="<?= _("A small description to help us to sell this item") ?>"><?= htmlentities($sDescription) ?></textarea>
                    </div>

                    <div class="form-group">
                        <label><?= _('Picture URL') ?>:</label>

                        <div class="input-group mb-3">
                            <!-- /btn-group -->
                            <input type="text" name="PictureURL" id="PictureURL" class= "form-control form-control-sm"
                                   value="<?= htmlentities($sPictureURL) ?>"
                                   data-toggle="tooltip" data-placement="top" title="<?= _("Paste an URL") ?>">
                            <div class="input-group-append">
                            <span class="btn btn-primary" id="donatedItemPicture"
                                  data-donateditemid="<?= $iDonatedItemID ?>"
                                  data-toggle="tooltip" data-placement="top" title="<?= _("Use the EDrive to browse or upload a file") ?>"> <i
                                    class="fas fa-cloud-download-alt"></i></span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group"><img src="<?= (($sPictureURL != '')?htmlentities($sPictureURL):'') ?>" width="100%" id="image"/></div>
                </div>

            </div> <!-- row -->

        </div>
        <div class="card-footer">
            <div class="row">
                <div class="col-md-1"></div>
                <div class="col-md-1">
                    <input type="submit" class="btn btn-primary" value="<?= _('Save') ?>" name="DonatedItemSubmit"
                           id="DonatedItemSubmit">
                </div>

                <?php if (SessionUser::getUser()->isAddRecordsEnabled()): ?>
                    <div class="col-md-2">
                        <input type="submit" class="btn btn-success" value="<?= _('Save and Add'); ?>"
                               name="DonatedItemSubmitAndAdd" id="DonatedItemSubmitAndAdd">
                    </div>
                <?php endif; ?>
                <div class="col-md-3">
                    <input type="button" class="btn btn-default" value="<?= _('Cancel') ?>" name="DonatedItemCancel"
                           id="DonatedItemCancel">
                </div>
            </div>
        </div>
    </div>
</div>

<script nonce="<?= SystemURLs::getCSPNonce() ?>">
    $(document).ready(function () {
        window.CRM.currentFundraiser = <?= $iCurrentFundraiser ?>;
        window.CRM.currentDonatedItemID = <?= strlen($iDonatedItemID) ? $iDonatedItemID : -1 ?>;
        window.CRM.currentPicture = "<?= $sPictureURL ?>";
    });
</script>

<script src="<?= $sRootPath ?>/skin/js/fundraiser/donatedItemEditor.js"></script>
<script src="<?= $sRootPath ?>/skin/js/publicfolder.js"></script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>
