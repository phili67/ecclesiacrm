<?php

/*******************************************************************************
 *
 *  filename    : grouplist.php
 *  last change : 2019-06-23
 *  description : manage the group list
 *
 *  http://www.ecclesiacrm.com/
 *
 *  This code is under copyright not under MIT Licence
 *  copyright   : 2019 Philippe Logel all right reserved not MIT licence
 *                This code can't be incoprorated in another software without authorizaion
 *
 ******************************************************************************/

use EcclesiaCRM\SessionUser;
use EcclesiaCRM\GroupQuery;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\Join;

require $sRootDocument . '/Include/Header.php';

// Récupération des stats par type de groupe
$groupTypeCounts = [];

$groupTypeJoin1 = new Join();
$groupTypeJoin1->addCondition("person2group2role_p2g2r.PersonId", "person_per.per_ID", Join::EQUAL );
$groupTypeJoin1->setJoinType(Criteria::LEFT_JOIN);


$groupTypeJoin = new Join();
$groupTypeJoin->addCondition("GroupType.ListOptionId", "list_lst.lst_OptionId", Join::EQUAL );
$groupTypeJoin->addForeignValueCondition("list_lst", "lst_ID", '', 3, Join::EQUAL);
$groupTypeJoin->setJoinType(Criteria::LEFT_JOIN);

$groups = GroupQuery::create()
    ->leftJoinGroupType()
    ->withColumn('GroupType.ListOptionId','ListOptionId')
    ->leftJoinPerson2group2roleP2g2r()
    ->addJoinObject($groupTypeJoin1)
    ->where('person_per.per_datedeactivated is NULL')
    ->withColumn('COUNT(person_per.per_ID)', 'memberCount')
    ->groupBy('Group.Id')
    ->addJoinObject($groupTypeJoin)
    ->withColumn('list_lst.lst_OptionName', 'optionName')
    ->withColumn('list_lst.lst_Type', 'optionType')
    ->find();

foreach ($groups as $group) {
    $type = $group->getGroupType() ? $group->getOptionName() : _('Unassigned');
    if (!isset($groupTypeCounts[$type])) {
        $groupTypeCounts[$type] = 0;
    }
    $groupTypeCounts[$type]++;
}

// Liste déterministe de toutes les couleurs bg-gradient-* Bootstrap connues
$allColors = [
    'bg-gradient-orange',
    'bg-gradient-info',
    'bg-gradient-light',
    'bg-gradient-pink',
    'bg-gradient-lime',
    'bg-gradient-lightblue',
    'bg-gradient-primary',
    'bg-gradient-secondary',
    'bg-gradient-success',
    'bg-gradient-danger',
    'bg-gradient-warning',
    'bg-gradient-dark',
    'bg-gradient-indigo',
    'bg-gradient-purple',
    'bg-gradient-teal',
    'bg-gradient-cyan',
    'bg-gradient-fuchsia',
    'bg-gradient-blue',
    'bg-gradient-olive',
    'bg-gradient-maroon',
    'bg-gradient-navy',
    'bg-gradient-gray',
    'bg-gradient-gray-dark'
];



// Attribution déterministe d'une couleur à chaque type (ordre alphabétique)
$sortedTypes = array_keys($groupTypeCounts);
sort($sortedTypes);
$typeColors = [];

// Si le nombre de types dépasse le nombre de couleurs, les couleurs sont réutilisées cycliquement (modulo)
foreach ($sortedTypes as $i => $type) {
    $typeColors[$type] = $allColors[$i % count($allColors)];
}
// Trie effectif du tableau $groupTypeCounts par clé
uksort($groupTypeCounts, function($a, $b) {
    return strcasecmp($a, $b);
});

?>

<div class="mb-2 mt-3 text-center">
    <h4 class="mb-1">
        <i class="fas fa-layer-group text-primary mr-2"></i>
        <?= _('Group Types Overview') ?>
    </h4>
    <div class="text-muted small mb-2">
        <?= sprintf(_('There are <b>%d</b> different group types.'), count($groupTypeCounts)) ?>
    </div>
</div>

<div class="row mb-4">
    <?php foreach ($groupTypeCounts as $type => $count): ?>
        <?php $colorClass = isset($typeColors[$type]) ? $typeColors[$type] : 'bg-gradient-info'; ?>
        <div class="col-md-2 col-sm-6 col-xs-12">
            <div class="info-box <?= $colorClass ?> shadow-sm">
                <span class="info-box-icon">
                    <i class="fas fa-layer-group"></i>
                </span>
                <div class="info-box-content">
                    <span class="info-box-text"><?= htmlspecialchars($type) ?></span>
                    <span class="info-box-number"><?= $count ?></span>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="card card-outline card-warning shadow-sm">
    <div class="card-header border-0">
        <h3 class="card-title"><i class="fas fa-users mr-2"></i><?= _('Groups') ?></h3>
        <div class="card-tools d-flex align-items-center gap-2">
            <select id="table-filter" class="form-control form-control-sm" style="width:160px;">
                <option value=""><?= _("All") ?></option>
                <option><?= _("Unassigned") ?></option>
                <?php foreach ($rsGroupTypes as $groupType): ?>
                    <option><?= $groupType->getOptionName() ?></option>
                <?php endforeach; ?>
            </select>
            <small class="text-muted text-nowrap ml-2">
                <?= _("Count:") ?> <span id="numberOfGroups" class="font-weight-bold"></span>
            </small>
            <?php if (SessionUser::getUser()->isManageGroupsEnabled()): ?>
            <button type="button" class="btn btn-success btn-sm ml-2" id="addNewGroup">
                <i class="fas fa-plus mr-1"></i><?= _('Add New') ?>
            </button>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover table-sm data-table" id="groupsTable" style="width:100%">
        </table>
    </div>
</div>


<script src="<?= $sRootPath ?>/skin/js/group/GroupList.js"></script>
<script nonce="<?= $CSPNonce ?>">
    $(function() {
        var gS = localStorage.getItem("groupSelect");
        if (gS != null) {
            tf = document.getElementById("table-filter");
            tf.selectedIndex = gS;

            window.groupSelect = tf.value;
        }
    });
</script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>