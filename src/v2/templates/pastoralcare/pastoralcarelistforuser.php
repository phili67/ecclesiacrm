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

<div class="card card-outline card-primary shadow-sm">
    <div class="card-header py-2 d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0"><i class="fas fa-list mr-1"></i><?= _("Members")." / "._("Families") ?></h3>
    </div>
    <div class="card-body py-3">
        <div class="table-responsive">
            <table id="MemberTable" class="table table-striped table-hover table-sm dataTable" width="100%">
                <thead class="thead-light">
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
                                   class="user-link">
                                    <i class="fas fa-user mr-1"></i><?= _("Person") ?> : <?= $member['FollowedPersonFirstName']." ".$member['FollowedPersonLastName'] ?>
                                </a>
                            <?php } else { ?>
                                <?= $person->getJPGPhotoDatas() ?>
                                <a href="<?= SystemURLs::getRootPath() ?>/v2/pastoralcare/family/<?= $member['FollowedFamID'] ?>"
                                   class="user-link">
                                    <i class="fas fa-home mr-1"></i><?= _("Family") ?> : <?= $member['FollowedFamName'] ?>
                                </a>
                            <?php } ?>
                        </td>
                        <td class="text-nowrap">
                            <i class="fas fa-calendar-alt mr-1"></i><?= (new DateTime($member['Date']))->format(SystemConfig::getValue('sDateFormatLong').' H:i:s') ?>
                        </td>
                    </tr>

                    <?php
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>
</div>



<div class="text-center mt-4 pt-3 border-top">
    <a class="btn btn-success" href="<?= $sRootPath ?>/v2/pastoralcare/dashboard">
        <i class="fas fa-home mr-1"></i><?= _('Dashboard') ?>
    </a>
    <a class="btn btn-outline-secondary" href="<?= $sRootPath ?>/v2/pastoralcare/membersList">
        <i class="fas fa-list mr-1"></i><?= _('Members List') ?>
    </a>
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