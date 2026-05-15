<?php
use EcclesiaCRM\PluginQuery;
use EcclesiaCRM\PastoralCareQuery;
use EcclesiaCRM\PastoralCareTypeQuery;
use EcclesiaCRM\Map\PluginUserRoleTableMap;

use EcclesiaCRM\dto\MenuEventsCount;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\Utils\OutputUtils;
use Propel\Runtime\ActiveQuery\Criteria;

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

    echo '<style>'
        . '.birthday-pastoral-dropdown-menu{z-index:2000 !important;}'
        . '</style>';

    $birthdays = [];
    $unclassified = [];
    $pastoralTypeCareOptions = [];
    $defaultPastoralTypeCare = null;
    $lastPastoralCareDates = [];
    $lastPastoralCareFamilyDates = [];
    $pastoralRecentCutoffDate = new DateTimeImmutable('-2 years');

    foreach ($peopleWithBirthDays as $person) {
        if ($person->getOnlyVisiblePersonView()) {
            $unclassified[] = $person;
        } else {
            $birthdays[] = $person;
        }
    }

    if (SessionUser::getUser()->isPastoralCareEnabled()) {
        $ormPastoralTypeCares = PastoralCareTypeQuery::create()->find();

        foreach ($ormPastoralTypeCares as $ormPastoralTypeCare) {
            $typeAndDesc = $ormPastoralTypeCare->getTitle() . ((!empty($ormPastoralTypeCare->getDesc())) ? ' (' . $ormPastoralTypeCare->getDesc() . ')' : '');
            $pastoralTypeCare = [
                'id' => (int)$ormPastoralTypeCare->getId(),
                'visible' => $ormPastoralTypeCare->getVisible() ? 1 : 0,
                'typeDesc' => $typeAndDesc,
            ];

            if (is_null($defaultPastoralTypeCare)) {
                $defaultPastoralTypeCare = $pastoralTypeCare;
            }

            $pastoralTypeCareOptions[] = $pastoralTypeCare;
        }

        $personIds = [];
        foreach (array_merge($birthdays, $unclassified) as $person) {
            $personIds[] = (int)$person->getId();
        }

        if (!empty($personIds)) {
            $pastoralCares = PastoralCareQuery::create()
                ->filterByPersonId(array_values(array_unique($personIds)))
                ->orderByDate(Criteria::DESC)
                ->find();

            foreach ($pastoralCares as $pastoralCare) {
                $personId = (int)$pastoralCare->getPersonId();
                if (!isset($lastPastoralCareDates[$personId])) {
                    $lastPastoralCareDates[$personId] = $pastoralCare->getDate();
                }
            }
        }

        $familyIds = [];
        foreach ($Anniversaries as $anniversary) {
            $familyIds[] = (int)$anniversary->getId();
        }

        if (!empty($familyIds)) {
            $familyPastoralCares = PastoralCareQuery::create()
                ->filterByFamilyId(array_values(array_unique($familyIds)))
                ->orderByDate(Criteria::DESC)
                ->find();

            foreach ($familyPastoralCares as $pastoralCare) {
                $familyId = (int)$pastoralCare->getFamilyId();
                if (!isset($lastPastoralCareFamilyDates[$familyId])) {
                    $lastPastoralCareFamilyDates[$familyId] = $pastoralCare->getDate();
                }
            }
        }
    }

    $renderCardTools = function () use ($Card_collapsed_button) {
        return '<div class="card-tools" style="display:flex; align-items:center; gap:0.3rem; margin-left:auto;">'
            . '<button type="button" class="btn btn-sm text-muted" data-card-widget="remove" title="'. dgettext("messages-BirthdayAnniversaryDashboard","Remove") . '"><i class="fa-solid fa-xmark"></i></button>'
            . '<button type="button" class="btn btn-sm text-muted" data-card-widget="collapse" title="'. dgettext("messages-BirthdayAnniversaryDashboard","Collapse") . '"><i class="fa-solid '. $Card_collapsed_button . '"></i></button>'
            . '</div>';
    };

    $hasRecentPastoralCare = function ($careDate) use ($pastoralRecentCutoffDate) {
        if ($careDate instanceof DateTimeInterface) {
            return $careDate >= $pastoralRecentCutoffDate;
        }

        if (empty($careDate)) {
            return false;
        }

        try {
            return new DateTimeImmutable((string)$careDate) >= $pastoralRecentCutoffDate;
        } catch (Exception $ex) {
            return false;
        }
    };

    $currentUser = SessionUser::getUser();
    $currentUserPerson = $currentUser->getPerson();
    $currentUserPersonId = $currentUser->getPersonId();
    $currentUserFamilyId = !is_null($currentUserPerson) ? (int)$currentUserPerson->getFamId() : 0;

    $formatPastoralCareDate = function ($careDate) {
        if ($careDate instanceof DateTimeInterface) {
            return OutputUtils::FormatDate($careDate->format('Y-m-d H:i:s'), true);
        }

        if (empty($careDate)) {
            return '';
        }

        return OutputUtils::FormatDate((string)$careDate, true);
    };

    $getCallablePhoneNumber = function ($person) {
        $phoneNumber = trim((string)$person->getCellPhone());
        if ($phoneNumber === '') {
            $phoneNumber = trim((string)$person->getHomePhone());
        }
        if ($phoneNumber === '') {
            $phoneNumber = trim((string)$person->getWorkPhone());
        }

        return $phoneNumber;
    };

    $formatPhoneHref = function ($phoneNumber) {
        if (empty($phoneNumber)) {
            return '';
        }

        return preg_replace('/[^0-9+]/', '', (string)$phoneNumber);
    };

    $renderPersonCard = function ($person) use ($sRootPath, $defaultPastoralTypeCare, $pastoralTypeCareOptions, $lastPastoralCareDates, $hasRecentPastoralCare, $formatPastoralCareDate, $getCallablePhoneNumber, $formatPhoneHref, $currentUser, $currentUserPersonId, $currentUserFamilyId) {
        $personId = (int)$person->getId();
        $personName = $person->getFullNameWithAge();
        $familyId = (int)$person->getFamId();
        $personFamily = $person->getFamily();
        $familyName = !is_null($personFamily) ? htmlspecialchars($personFamily->getName(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') : '';
        $url = SessionUser::getUser()->isPastoralCareEnabled()
            ? $sRootPath . '/v2/pastoralcare/person/' . $personId
            : $person->getViewURI();
        $callablePhoneNumber = $getCallablePhoneNumber($person);
        $callablePhoneHref = $formatPhoneHref($callablePhoneNumber);
        $canEditPerson = $currentUser->isEditRecordsEnabled()
            || ($currentUser->isEditSelfEnabled() && $personId === (int)$currentUserPersonId)
            || ($currentUser->isEditSelfEnabled() && $familyId > 0 && $familyId === $currentUserFamilyId);
        $canToggleActivation = $canEditPerson && $currentUser->isDeleteRecordsEnabled() && $personId !== 1;
        $isCurrentlyActive = !$person->isDeactivated();

        $icon = '<i class="fa-solid fa-user text-primary me-1"></i>';
        if ($person->getUrlIcon()) {
            $icon = '<img src="'. $sRootPath . '/skin/icons/markers/' . $person->getUrlIcon() . '" alt="" width="18" height="18" >';
        }

        $lastPastoralCareDateValue = $lastPastoralCareDates[$personId] ?? null;
        $isPastoralCareRecorded = $hasRecentPastoralCare($lastPastoralCareDateValue);
        $statusClass = $isPastoralCareRecorded ? 'badge-success' : 'badge-light border';
        $statusIcon = $isPastoralCareRecorded ? 'fas fa-check-circle' : 'far fa-circle';
        $statusText = '';

        if (SessionUser::getUser()->isPastoralCareEnabled()) {
            $statusText = $isPastoralCareRecorded
                ? dgettext('messages-BirthdayAnniversaryDashboard', 'Pastoral follow-up recorded')
                : dgettext('messages-BirthdayAnniversaryDashboard', 'No pastoral follow-up yet');

            $statusText = '<i class="'. $statusIcon . ' mr-1"></i>'. $statusText;
        }

        $lastPastoralCareDate = '';
        if (!is_null($lastPastoralCareDateValue)) {
            $lastPastoralCareDate = $formatPastoralCareDate($lastPastoralCareDateValue);
        }

        $pastoralActions = '';
        if (!is_null($defaultPastoralTypeCare) || $canToggleActivation) {
            $pastoralActions .= '<div class="btn-group btn-group-sm mt-2 w-100" role="group">';

            if (!is_null($defaultPastoralTypeCare)) {
                if ($callablePhoneHref !== '') {
                    $pastoralActions .= '<a class="btn btn-outline-primary" href="tel:'. htmlspecialchars($callablePhoneHref, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '" title="'. htmlspecialchars($callablePhoneNumber, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '">'
                        . '<i class="fas fa-phone-alt mr-1"></i>' . dgettext("messages-BirthdayAnniversaryDashboard",'Call') . '</a>';
                } else {
                    $pastoralActions .= '<button type="button" class="btn btn-outline-secondary" disabled title="'. dgettext("messages-BirthdayAnniversaryDashboard",'No phone number available') . '">'
                        . '<i class="fas fa-phone-alt mr-1"></i>' . dgettext("messages-BirthdayAnniversaryDashboard",'Call') . '</button>';
                }

                if (count($pastoralTypeCareOptions) > 1) {
                    $pastoralActions .= '<button type="button" class="btn btn-outline-primary dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="'. dgettext("messages-BirthdayAnniversaryDashboard",'Pastoral follow-up') . '" aria-label="'. dgettext("messages-BirthdayAnniversaryDashboard",'Pastoral follow-up') . '">'
                        . '<span class="sr-only">'. dgettext("messages-BirthdayAnniversaryDashboard",'Pastoral follow-up') . '</span>'
                        . '</button>'
                        . '<div class="dropdown-menu dropdown-menu-right birthday-pastoral-dropdown-menu">'
                        . '<span class="dropdown-header">'. dgettext("messages-BirthdayAnniversaryDashboard",'Pastoral follow-up') . '</span>';

                    foreach ($pastoralTypeCareOptions as $pastoralTypeCareOption) {
                        $pastoralActions .= '<a class="dropdown-item birthday-pastoral-care" href="#"'
                            . ' data-person-id="'. $personId . '"'
                            . ' data-person-name="'. htmlspecialchars($personName, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '"'
                            . ' data-family-id="'. $familyId . '"'
                            . ' data-family-name="'. $familyName . '"'
                            . ' data-typeid="'. $pastoralTypeCareOption['id'] . '"'
                            . ' data-visible="'. $pastoralTypeCareOption['visible'] . '"'
                            . ' data-typedesc="'. htmlspecialchars($pastoralTypeCareOption['typeDesc'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '">'
                            . '<i class="fas fa-check mr-2"></i>' . htmlspecialchars($pastoralTypeCareOption['typeDesc'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</a>';
                    }

                    $pastoralActions .= '</div>';
                }
            }

            if ($canToggleActivation) {
                $pastoralActions .= '<button type="button" class="btn btn-outline-warning birthday-toggle-activation"'
                    . ' data-entity-type="person"'
                    . ' data-person-id="'. $personId . '"'
                    . ' data-entity-name="'. htmlspecialchars($personName, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '"'
                    . ' data-current-active="'. ($isCurrentlyActive ? '1' : '0') . '"'
                    . ' title="'. ($isCurrentlyActive ? dgettext("messages-BirthdayAnniversaryDashboard",'Deactivate this Person') : dgettext("messages-BirthdayAnniversaryDashboard",'Activate this Person')) . '">'
                    . '<i class="fa '. ($isCurrentlyActive ? 'fa-times-circle' : 'fa-check-circle') . ' mr-1"></i>'
                    . ($isCurrentlyActive ? dgettext("messages-BirthdayAnniversaryDashboard",'Deactivate') : dgettext("messages-BirthdayAnniversaryDashboard",'Activate'))
                    . '</button>';
            }

            $pastoralActions .= '</div>';
        }

        return '<div class="card h-110 shadow-sm birthday-person-card '. ($isPastoralCareRecorded ? 'border-success' : 'border-info') . '" data-person-id="'. $personId . '" data-family-id="'. $familyId . '">'
            . '<div class="card-body py-2 px-2 bg-light">'
            . '<div class="d-flex justify-content-between align-items-start flex-wrap" style="gap:.5rem;">'
            . '<a href="'. $url . '" class="d-flex align-items-center text-decoration-none flex-grow-1" style="min-width:0;" title="'. $personName .'">'
            . $icon . '<span class="text-dark" style="white-space:normal;word-break:break-word;">'. $personName . '</span>'
            . '</a>'
            . '<span class="badge '. $statusClass . ' birthday-pastoral-status text-nowrap" style="white-space:nowrap;" data-state="'. ($isPastoralCareRecorded ? 'recorded' : 'pending') . '">'. $statusText . '</span>'
            . '</div>'
            . '<div class="small text-muted mt-2 birthday-pastoral-date'. ($lastPastoralCareDate === '' ? ' d-none' : '') . '"><i class="far fa-clock mr-1"></i><span>' . dgettext("messages-BirthdayAnniversaryDashboard",'No calls since') . ' ' . htmlspecialchars($lastPastoralCareDate, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</span></div>'
            . $pastoralActions
            . '</div>'
            . '</div>';
    };

            $renderFamilyCard = function ($anniversary) use ($sRootPath, $defaultPastoralTypeCare, $pastoralTypeCareOptions, $lastPastoralCareFamilyDates, $hasRecentPastoralCare, $formatPastoralCareDate, $getCallablePhoneNumber, $formatPhoneHref, $currentUserFamilyId, $currentUser) {
        $familyId = (int)$anniversary->getId();
        $familyName = htmlspecialchars($anniversary->getFamilyString(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $url = SessionUser::getUser()->isPastoralCareEnabled()
            ? $sRootPath . '/v2/pastoralcare/family/' . $familyId
            : $anniversary->getViewURI();
        $callablePhoneNumber = $getCallablePhoneNumber($anniversary);
        $callablePhoneHref = $formatPhoneHref($callablePhoneNumber);
        $isCurrentlyActive = is_null($anniversary->getDateDeactivated());
        $canToggleActivation = $currentUser->isEditRecordsEnabled() && $currentUser->isDeleteRecordsEnabled();

        $lastPastoralCareDateValue = $lastPastoralCareFamilyDates[$familyId] ?? null;
        $isPastoralCareRecorded = $hasRecentPastoralCare($lastPastoralCareDateValue);
        $statusClass = $isPastoralCareRecorded ? 'badge-success' : 'badge-light border';
        $statusIcon = $isPastoralCareRecorded ? 'fas fa-check-circle' : 'far fa-circle';

        $statusText = '';
        if (SessionUser::getUser()->isPastoralCareEnabled()) {
            $statusText = $isPastoralCareRecorded
                ? dgettext('messages-BirthdayAnniversaryDashboard', 'Pastoral follow-up recorded')
                : dgettext('messages-BirthdayAnniversaryDashboard', 'No pastoral follow-up yet');
                
            $statusText = '<i class="'. $statusIcon . ' mr-1"></i>'. $statusText;
        }

        $lastPastoralCareDate = '';
        if (!is_null($lastPastoralCareDateValue)) {
            $lastPastoralCareDate = $formatPastoralCareDate($lastPastoralCareDateValue);
        }

        $pastoralActions = '';
        if (!is_null($defaultPastoralTypeCare) || $canToggleActivation) {
            $pastoralActions .= '<div class="btn-group btn-group-sm mt-2 w-100" role="group">';
            if (!is_null($defaultPastoralTypeCare)) {
                if ($callablePhoneHref !== '') {
                    $pastoralActions .= '<a class="btn btn-outline-primary" href="tel:'. htmlspecialchars($callablePhoneHref, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '" title="'. htmlspecialchars($callablePhoneNumber, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '">'
                        . '<i class="fas fa-phone-alt mr-1"></i>' . dgettext("messages-BirthdayAnniversaryDashboard",'Call') . '</a>';
                } else {
                    $pastoralActions .= '<button type="button" class="btn btn-outline-secondary" disabled title="'. dgettext("messages-BirthdayAnniversaryDashboard",'No phone number available') . '">'
                        . '<i class="fas fa-phone-alt mr-1"></i>' . dgettext("messages-BirthdayAnniversaryDashboard",'Call') . '</button>';
                }

                if (count($pastoralTypeCareOptions) > 1) {
                    $pastoralActions .= '<button type="button" class="btn btn-outline-primary dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="'. dgettext("messages-BirthdayAnniversaryDashboard",'Pastoral follow-up') . '" aria-label="'. dgettext("messages-BirthdayAnniversaryDashboard",'Pastoral follow-up') . '">'
                        . '<span class="sr-only">'. dgettext("messages-BirthdayAnniversaryDashboard",'Pastoral follow-up') . '</span>'
                        . '</button>'
                        . '<div class="dropdown-menu dropdown-menu-right birthday-pastoral-dropdown-menu">'
                        . '<span class="dropdown-header">'. dgettext("messages-BirthdayAnniversaryDashboard",'Pastoral follow-up') . '</span>';

                    foreach ($pastoralTypeCareOptions as $pastoralTypeCareOption) {
                        $pastoralActions .= '<a class="dropdown-item birthday-pastoral-care" href="#"'
                            . ' data-entity-type="family"'
                            . ' data-family-id="'. $familyId . '"'
                            . ' data-person-name="'. $familyName . '"'
                            . ' data-typeid="'. $pastoralTypeCareOption['id'] . '"'
                            . ' data-visible="'. $pastoralTypeCareOption['visible'] . '"'
                            . ' data-typedesc="'. htmlspecialchars($pastoralTypeCareOption['typeDesc'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '"'
                            . '><i class="fas fa-check mr-2"></i>' . htmlspecialchars($pastoralTypeCareOption['typeDesc'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</a>';
                    }

                    $pastoralActions .= '</div>';
                }
            }

            if ($canToggleActivation) {
                $pastoralActions .= '<button type="button" class="btn btn-outline-warning birthday-toggle-activation"'
                    . ' data-entity-type="family"'
                    . ' data-family-id="'. $familyId . '"'
                    . ' data-entity-name="'. $familyName . '"'
                    . ' data-current-active="'. ($isCurrentlyActive ? '1' : '0') . '"'
                    . ' title="'. ($isCurrentlyActive ? dgettext("messages-BirthdayAnniversaryDashboard",'Deactivate this Family') : dgettext("messages-BirthdayAnniversaryDashboard",'Activate this Family')) . '">'
                    . '<i class="fa '. ($isCurrentlyActive ? 'fa-times-circle' : 'fa-check-circle') . ' mr-1"></i>'
                    . ($isCurrentlyActive ? dgettext("messages-BirthdayAnniversaryDashboard",'Deactivate') : dgettext("messages-BirthdayAnniversaryDashboard",'Activate'))
                    . '</button>';
            }

            $pastoralActions .= '</div>';

            $pastoralActions .= '</div>';
        }

        return '<div class="card h-110 shadow-sm birthday-family-card '. ($isPastoralCareRecorded ? 'border-success' : 'border-secondary') . '" data-family-id="'. $familyId . '">'
            . '<div class="card-body bg-light py-2 px-2">'
            . '<div class="d-flex justify-content-between align-items-start" style="gap:.5rem;">'
            . '<a href="'. $url .'" class="d-flex align-items-center text-decoration-none text-truncate flex-grow-1" title="'. $familyName .'">'
            . '<i class="fa-solid fa-heart text-danger me-1"></i><span class="text-dark">'. $familyName .'</span>'
            . '</a>'
            . '<span class="badge '. $statusClass . ' birthday-pastoral-status text-nowrap" style="white-space:nowrap;" data-state="'. ($isPastoralCareRecorded ? 'recorded' : 'pending') . '">'. $statusText . '</span>'
            . '</div>'
            . '<div class="small text-muted mt-2 birthday-pastoral-date'. ($lastPastoralCareDate === '' ? ' d-none' : '') . '"><i class="far fa-clock mr-1"></i><span>' . dgettext("messages-BirthdayAnniversaryDashboard","Last call") . ' ' . htmlspecialchars($lastPastoralCareDate, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</span></div>'
            . $pastoralActions
            . '</div>'
            . '</div>';
    };

    echo '<div class="card card-outline card-primary shadow-sm '. $plugin->getName() .' '. $Card_collapsed .'" id="Menu_Banner1" data-name="'. $plugin->getName() .'">';

    if (count($birthdays) > 0) {
        echo '<div class="card-header d-flex justify-content-between">';
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
            echo '<div class="col">' . $renderFamilyCard($Anniversary) . '</div>';
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

if ($showBanner && ($peopleWithBirthDaysCount > 0 || $AnniversariesCount > 0) && SessionUser::getUser()->isSeePrivacyDataEnabled() && SessionUser::getUser()->isPastoralCareEnabled() && !empty($pastoralTypeCareOptions)) {
    ?>
    <script src="<?= $sRootPath ?>/skin/js/pastoralcare/PastoralCareBootboxContent.js"></script>
    <script>
        $(function () {
            window.CRM = window.CRM || {};
            window.CRM.BirthdayAnniversaryDashboard = window.CRM.BirthdayAnniversaryDashboard || {};
            window.CRM.BirthdayAnniversaryDashboard.currentPastorId = <?= (int) SessionUser::getUser()->getPerson()->getId() ?>;

            if (!window.CRM.BirthdayAnniversaryDashboard.editorAssetsPromise) {
                var loadScript = function (src) {
                    return new window.Promise(function (resolve, reject) {
                        var existingScript = document.querySelector('script[src="' + src + '"]');

                        if (existingScript) {
                            if (existingScript.getAttribute('data-loaded') === 'true') {
                                resolve();
                                return;
                            }

                            existingScript.addEventListener('load', function handleLoad() {
                                existingScript.setAttribute('data-loaded', 'true');
                                resolve();
                            }, {once: true});
                            existingScript.addEventListener('error', function handleError() {
                                reject(new Error('Unable to load ' + src));
                            }, {once: true});
                            return;
                        }

                        var script = document.createElement('script');
                        script.src = src;
                        script.async = false;
                        script.addEventListener('load', function () {
                            script.setAttribute('data-loaded', 'true');
                            resolve();
                        }, {once: true});
                        script.addEventListener('error', function () {
                            reject(new Error('Unable to load ' + src));
                        }, {once: true});
                        document.head.appendChild(script);
                    });
                };

                var ckeditorSrc = '<?= $sRootPath ?>/skin/external/ckeditor/ckeditor.js';
                var ckeditorExtensionSrc = '<?= $sRootPath ?>/skin/js/ckeditor/ckeditorextension.js';

                window.CRM.BirthdayAnniversaryDashboard.editorAssetsPromise = (window.CKEDITOR
                    ? window.Promise.resolve()
                    : loadScript(ckeditorSrc)
                ).then(function () {
                    return typeof window.add_ckeditor_buttons === 'function'
                        ? window.Promise.resolve()
                        : loadScript(ckeditorExtensionSrc);
                });
            }
        });
    </script>
    <?php
}

