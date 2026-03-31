<?php
use EcclesiaCRM\PluginQuery;
use EcclesiaCRM\Map\PluginUserRoleTableMap;

use EcclesiaCRM\dto\MenuEventsCount;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\SessionUser;

$plugin = PluginQuery::create()
    ->usePluginUserRoleQuery()
        ->addAsColumn('PlgnColor', PluginUserRoleTableMap::COL_PLGN_USR_RL_COLOR)
    ->endUse()
    ->findOneById($PluginId);

$peopleWithBirthDays = MenuEventsCount::getBirthDates();
$Anniversaries = MenuEventsCount::getAnniversaries();
$peopleWithBirthDaysCount = MenuEventsCount::getNumberBirthDates();
$AnniversariesCount = MenuEventsCount::getNumberAnniversaries();

$showBanner = SystemConfig::getBooleanValue("bEventsOnDashboardPresence");

?>

<!-- birthday + anniversary -->
<?php

if ($showBanner && ($peopleWithBirthDaysCount > 0 || $AnniversariesCount > 0) && SessionUser::getUser()->isSeePrivacyDataEnabled()) {

    $birthdays = [];
    $unclassified = [];

    foreach ($peopleWithBirthDays as $person) {
        if ($person->getOnlyVisiblePersonView()) {
            $unclassified[] = $person;
        } else {
            $birthdays[] = $person;
        }
    }

    $renderCardTools = function () use ($Card_collapsed_button) {
        return '<div class="card-tools" style="display:flex; align-items:center; gap:0.3rem; margin-left:auto;">'
            . '<button type="button" class="btn btn-sm text-white" data-card-widget="remove" title="'. dgettext("messages-BirthdayAnniversaryDashboard","Remove") . '"><i class="fa-solid fa-xmark"></i></button>'
            . '<button type="button" class="btn btn-sm text-white" data-card-widget="collapse" title="'. dgettext("messages-BirthdayAnniversaryDashboard","Collapse") . '"><i class="fa-solid '. $Card_collapsed_button . '"></i></button>'
            . '</div>';
    };

    $renderPersonCard = function($person) use ($sRootPath) {
        $url = SessionUser::getUser()->isPastoralCareEnabled()
            ? $sRootPath . '/v2/pastoralcare/person/' . $person->getId()
            : $person->getViewURI();

        $icon = '<i class="fa-solid fa-user text-primary me-1"></i>';
        if ($person->getUrlIcon()) {
            $icon = '<img src="'. $sRootPath . '/skin/icons/markers/' . $person->getUrlIcon() . '" alt="" width="18" height="18" >';
        }

        echo '<div class="card border-info h-100 shadow-sm">'
            . '<div class="card-body py-2 px-2 bg-light">'
            . '<a href="'. $url . '" class="d-flex align-items-center text-decoration-none text-truncate" title="'. $person->getFullNameWithAge() .'">'
            . $icon . '<span class="text-dark">'. $person->getFullNameWithAge() .'</span>'
            . '</a>'
            . '</div>'
            . '</div>';
    };

    echo '<div class="card border-primary shadow-sm '. $plugin->getName() .' '. $Card_collapsed .'" id="Menu_Banner1" data-name="'. $plugin->getName() .'">';

    if (count($birthdays) > 0) {
        echo '<div class="card-header d-flex justify-content-between '. $plugin->getPlgnColor() . '">';
        echo '<h5 class="card-title mb-0"><i class="fa-solid fa-calendar-day"></i> '. dgettext("messages-BirthdayAnniversaryDashboard","Birthdates of the day") . '</h5>';
        echo $renderCardTools();
        echo '</div>';
        echo '<div class="card-body" style="'. $Card_body .'">';
        echo '<div class="row row-cols-1 row-cols-sm-2 row-cols-md-4 g-2">';
        foreach ($birthdays as $item) {
            echo '<div class="col">' . $renderPersonCard($item) . '</div>';
        }
        echo '</div>';
        echo '</div>';
    }

    if ($AnniversariesCount > 0) {
        echo '<div class="card-header d-flex justify-content-between border-top">';
        echo '<h5 class="card-title mb-0"><i class="fa-solid fa-ring"></i> ' . dgettext("messages-BirthdayAnniversaryDashboard","Anniversaries of the day") . '</h5>';
        echo '</div>';
        echo '<div class="card-body" style="'. $Card_body .'">';
        echo '<div class="row row-cols-1 row-cols-sm-2 row-cols-md-4 g-2">';

        foreach ($Anniversaries as $Anniversary) {
            $url = SessionUser::getUser()->isPastoralCareEnabled()
                ? $sRootPath . '/v2/pastoralcare/family/' . $Anniversary->getId()
                : $Anniversary->getViewURI();

            $label = htmlspecialchars($Anniversary->getFamilyString(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

            echo '<div class="col">'
                . '<div class="card border-secondary h-100"><div class="card-body bg-light py-2 px-2">'
                . '<a href="'. $url .'" class="d-flex align-items-center text-decoration-none text-truncate" title="'. $label .'">'
                . '<i class="fa-solid fa-heart text-danger me-1"></i>  '. $label
                . '</a></div></div></div>';
        }

        echo '</div></div>';
    }

    if (!empty($unclassified)) {
        echo '<div class="card-header d-flex justify-content-between border-top">';
        echo '<h5 class="card-title mb-0"><i class="fa-solid fa-circle-question"></i> ' . dgettext("messages-BirthdayAnniversaryDashboard","Unclassified birthdates") . '</h5>';
        echo '</div>';
        echo '<div class="card-body" style="'. $Card_body .'">';
        echo '<div class="row row-cols-1 row-cols-sm-2 row-cols-md-4 g-2">';

        foreach ($unclassified as $person) {
            echo '<div class="col">'. $renderPersonCard($person) .'</div>';
        }

        echo '</div></div>';
    }

    echo '</div>';
}

