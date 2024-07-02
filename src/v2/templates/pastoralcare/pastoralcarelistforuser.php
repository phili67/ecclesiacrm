<?php
/*******************************************************************************
 *
 *  filename    : pastoralcarefamily.php
 *  last change : 2020-01-03
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : 2018 Philippe Logel all right reserved not MIT licence
 *                This code can't be incorporated in another software without authorization
 *
 ******************************************************************************/

use EcclesiaCRM\Base\PersonQuery;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\dto\SystemConfig;

require $sRootDocument . '/Include/Header.php';
?>

<div class="card card-default">
    <div class="card-header border-1">
        <h3 class="card-title">
            <?= _("Members")." / "._("Families") ?>
        </h3>
        <div class="card-tools pull-right">
            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
        </div>
    </div>
    <div class="card-body">
        <table id="MemberTable" class="table table-striped table-bordered data-table dataTable no-footer dtr-inline" width="100%">
            <thead>
                <tr>
                    <th><span><?= _("Members") ?></span></th>
                    <th><span><?= _("Pastoral Care")." : "._("Date") ?></span></th>
                </tr>
            </thead>
            <tbody>
            <?php
            foreach ($members as $member) {
                $person = PersonQuery::create()->findOneById($member['FollowedPersonPerId']);
                if (is_null($person)) continue;
                ?>
                <tr>
                    <td>
                        <?php if ($member['FollowedPersonPerId'] != NULL) { ?>
                            <?= $person->getJPGPhotoDatas() ?>
                            <a href="<?= SystemURLs::getRootPath() ?>/v2/pastoralcare/person/<?= $member['FollowedPersonPerId'] ?>"
                               class="user-link"><?= _("Person") ?> : <?= $member['FollowedPersonFirstName']." ".$member['FollowedPersonLastName'] ?> </a>
                        <?php } else { ?>
                            <?= $person->getJPGPhotoDatas() ?>
                            <a href="<?= SystemURLs::getRootPath() ?>/v2/pastoralcare/family/<?= $member['FollowedFamID'] ?>"
                               class="user-link"><?= _("Family") ?> : <?= $member['FollowedFamName'] ?> </a>
                        <?php } ?>
                    </td>
                    <td>
                        <?= (new DateTime($member['Date']))->format(SystemConfig::getValue('sDateFormatLong').' H:i:s') ?>
                    </td>
                </tr>

                <?php
            }
            ?>
            </tbody>
        </table>
    </div>
</div>



<div class="text-center">
    <input type="button" class="btn btn-success" value="<?= _('Return To PastoralCare Dashboard') ?>" name="Cancel"
           onclick="javascript:document.location='<?= $sRootPath ?>/v2/pastoralcare/dashboard';">

    <input type="button" class="btn btn-primary" value="<?= _('Return To PastoralCare Members List') ?>" name="Cancel"
           onclick="javascript:document.location='<?= $sRootPath ?>/v2/pastoralcare/membersList';">
</div>

<script nonce="<?= $sCSPNonce ?>">
    $(function() {
        window.CRM.fmt = "";

        if (window.CRM.timeEnglish == true) {
            window.CRM.fmt = window.CRM.datePickerformat.toUpperCase() + ' hh:mm:ss a';
        } else {
            window.CRM.fmt = window.CRM.datePickerformat.toUpperCase() + ' HH:mm:ss';
        }

        $.fn.dataTable.moment( window.CRM.fmt  );

        $('#MemberTable').DataTable({
            "language": {
                "url": window.CRM.plugin.dataTable.language.url
            },
            "order": [[ 1, 'desc' ]],
        });
    });
</script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>