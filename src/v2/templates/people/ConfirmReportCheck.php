<?php
/*******************************************************************************
 *
 *  filename    : templates/ConfirmReportCheck.php
 *  last change : 2026-06-21
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2026 Philippe Logel
 *
 ******************************************************************************/

require $sRootDocument . '/Include/Header.php';

use EcclesiaCRM\FamilyQuery;
use EcclesiaCRM\Map\PersonTableMap;

switch ($exportType) {
    case 'family':
        $reportTitle = _('Address Report Check Confirmation');
        $ormFamilies = FamilyQuery::create();
        
        if ( !is_null($families) ) {
             $ormFamilies->filterById($families);
        }    
        
        $ormFamilies->filterByDateDeactivated(NULL);
        $ormFamilies->addAsColumn('FirstLetter', 'UPPER(LEFT(family_fam.fam_Name, 1))');
        $ormFamilies->leftJoinPerson();
        $ormFamilies->usePersonQuery()
            ->filterByDateDeactivated(NULL)
            ->addAsColumn('PersonCount', 'count(DISTINCT ' . PersonTableMap::COL_PER_ID . ')')            
        ->endUse();

        // Get all the families
        $ormFamilies->groupById()
        ->leftJoinNote()
            ->useNoteQuery()
                ->addAsColumn('LastDateEdited', 'max(note_nte.nte_DateLastEdited)')
                ->addAsColumn('LastDateEntered', 'max(note_nte.nte_DateEntered)')
                
                // Sécurité : Si l'un est NULL, on prend l'autre. Si les deux sont NULL, on met une date par défaut.
                ->addAsColumn('PlusGrandeDate', 'GREATEST(
                    COALESCE(max(note_nte.nte_DateLastEdited), max(note_nte.nte_DateEntered), "1970-01-01"), 
                    COALESCE(max(note_nte.nte_DateEntered), max(note_nte.nte_DateLastEdited), "1970-01-01")
                )')
                
                ->addAsColumn('EstAncienneGlobale', 'GREATEST(
                    COALESCE(max(note_nte.nte_DateLastEdited), max(note_nte.nte_DateEntered), "1970-01-01"), 
                    COALESCE(max(note_nte.nte_DateEntered), max(note_nte.nte_DateLastEdited), "1970-01-01")
                ) < DATE_SUB(NOW(), INTERVAL 1 WEEK)') // Par exemple, on considère "ancienne" si la dernière note a été modifiée ou créée il y a plus d'une semaine
            ->endUse();
        $ormFamilies->orderByName();
        $ormFamilies->find();

        break;
    case 'person':
        $reportTitle = _('Person Report Check Confirmation');
        break;
}
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title"><?= htmlspecialchars($reportTitle) ?></h3>
    </div>
    <div class="card-body">
        <p><?= _('This is a confirmation page to check the data before sending the confirmation emails to the families. Please review the information below and click "Confirm" to proceed with sending the emails, or "Cancel" to return to the previous page.') ?></p>        
        <div class="table-responsive">
            <?php if ($exportType === 'family') : ?>                            
            <table width="100%" cellpadding="2" class="table table-striped table-bordered data-table dataTable no-footer dtr-inline" id="monTableau">
                <thead>
                    <tr>
                        <th><b><?= _('First Letter') ?></b></th>
                        <th><i class="fa-solid fa-users"></i> <?= _('Family Name') ?></th>
                        <th><i class="fa-solid fa-cogs"></i> <?= _('Action') ?></th>
                        <th><i class="fa-solid fa-user"></i> <?= _('Person Count') ?></th>
                        <th><i class="fa-solid fa-home"></i> <?= _('Address') ?></th>
                        <th><i class="fa-solid fa-envelope"></i> <?= _('Email') ?></th>
                        <th><i class="fa-solid fa-clock"></i> <?= _('Is too old') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ormFamilies as $fam) : ?>
                        <tr>
                            <td><?= htmlspecialchars($fam->getVirtualColumn('FirstLetter')) ?></td>
                            <td><a href="<?= $sRootPath ?>/v2/people/family/view/<?= $fam->getId() ?>"><?= htmlspecialchars($fam->getName()) ?></a></td>
                            <td>
                                <div class="custom-control custom-switch mb-1">
                                    <input class="custom-control-input" type="checkbox" name="bCustomPeople<?= $fam->getId() ?>" value="<?= $fam->getVirtualColumn('EstAncienneGlobale') ? '0' : '1' ?>" id="bCustomPeople<?= $fam->getId() ?>" <?= $fam->getVirtualColumn('EstAncienneGlobale') ? '' : 'checked' ?>>
                                    <label class="custom-control-label" for="bCustomPeople<?= $fam->getId() ?>"><?= htmlspecialchars($fam->getVirtualColumn('PlusGrandeDate')) ?></label>
                                </div>
                            </td> 
                            <td><span class="badge badge-pill badge-light border"><?= $fam->getVirtualColumn('PersonCount') > 1 ?'<i class="fa-solid fa-people-roof"></i> ' : '<i class="fas fa-user"></i>'?> <?= htmlspecialchars($fam->getVirtualColumn('PersonCount')) ?></span></td>
                            <td><?= htmlspecialchars($fam->getAddress()) ?></td>
                            <td><a href="mailto:<?= htmlspecialchars( is_array($fam->getEmails()) ? $fam->getEmails()[0] : $fam->getEmails() ) ?>"><?= htmlspecialchars( is_array($fam->getEmails()) ? $fam->getEmails()[0] : $fam->getEmails() ) ?></a></td>                              
                            <td><?= htmlspecialchars($fam->getVirtualColumn('EstAncienneGlobale') ? _('Yes') : _('No')) ?></td>                         
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php elseif ($exportType === 'person') : ?>
                <p><?= _('No data to display for this report type.') ?></p>
            <?php endif; ?>
        </div>
        <form method="post" action="<?= $sRootPath ?>/v2/people/confirmReportCheck">
            <input type="hidden" name="confirm" value="1">
            <button type="submit" class="btn btn-success"><?= _('Confirm') ?></button>
            <a href="<?= $sRootPath ?>/v2/people/LettersAndLabels" class="btn btn-secondary"><?= _('Cancel') ?></a>
        </form>
        </div>
</div>


<?php require $sRootDocument . '/Include/Footer.php'; ?>
<script>
    window.CRM.exportType = '<?= $exportType ?>';
    window.CRM.personIDs = <?= json_encode($personIDs) ?>;
    window.CRM.familyIDs = <?= json_encode($familyIDs) ?>;
</script>

<script>
$(document).ready(function() {
    $('#monTableau').DataTable({
        paging: false,
        responsive: true,
        // On trie par la première colonne (la lettre) pour que le groupement fonctionne
        order: [[0, 'asc']], 
        "language": {
            "url": window.CRM.plugin.dataTable.language.url
        },
        // On configure le groupement de lignes
        rowGroup: {
            dataSrc: 0 // Index de la colonne contenant la première lettre
        },
        
        // Optionnel : masquer la première colonne puisqu'elle sert d'intertitre
        columnDefs: [
            { targets: [0], visible: false }
        ]
    });

    $('.custom-control-input, .custom-control-label').on('change', function() {
        const isChecked = $(this).is(':checked');
        const Id = $(this).attr('id').replace('bCustomPeople', '');
        const newValue = isChecked ? '1' : '0';

        // Mettre à jour la valeur du champ caché correspondant
        $(this).val(newValue);        

        window.CRM.APIRequest({
            method: "POST",
            path: "people/" + window.CRM.exportType + "/updateStatus",
            data: JSON.stringify({
                "ID": Id,
                "Status": newValue
            })
        }, function (data) {
            location.reload(); // Recharger la page pour voir les changements
        });
    })
});
</script>


