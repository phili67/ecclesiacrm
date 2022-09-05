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
    $new_unclassified_row = false;
    $cout_unclassified_people = 0;
    $unclassified = "";

    $new_row = false;
    $count_people = 0;
    $classified = "";

    $new_row = false;
    $count_people = 0;

    $global_body = '    <div class="card '. $plugin->getPlgnColor() .' '. $plugin->getName() .' '.$Card_collapsed.'" id="Menu_Banner1" style="position: relative; left: 0px; top: 0px;" data-name="'. $plugin->getName() .'">
        <div class="card-header border-0 ui-sortable-handle">
';

    foreach ($peopleWithBirthDays as $peopleWithBirthDay) {
        if ($peopleWithBirthDay->getOnlyVisiblePersonView()) {
            if ($new_unclassified_row == false) {
                $unclassified .= '<div class="row">';
                $new_unclassified_row = true;
                $unclassified .= '<div class="col-sm-3">';
                $unclassified .= '<label class="checkbox-inline">';

                if ($peopleWithBirthDay->getUrlIcon() != '') {
                    $unclassified .= '<img src="' . $sRootPath . "/skin/icons/markers/" . $peopleWithBirthDay->getUrlIcon() . '">';
                }

                $unclassified .= '<a href="' . $peopleWithBirthDay->getViewURI() . '" class="btn btn-link-menu" style="text-decoration: none">' . $peopleWithBirthDay->getFullNameWithAge() . '</a>';

                $unclassified .= '</label>';
                $unclassified .= '</div>';

                $cout_unclassified_people += 1;
                $cout_unclassified_people %= 4;
                if ($cout_unclassified_people == 0) {
                    $unclassified .= '</div>';
                    $new_unclassified_row = false;
                }
            }

            if ($new_unclassified_row == true) {
                $unclassified .= '</div>';
            }
            continue;
        }

        // we now work with the classified date
        if ($new_row == false) {
            $classified .= '<div class="row">';
            $new_row = true;
        }

        $classified .= '<div class="col-sm-3">';
        $classified .= '<label class="checkbox-inline">';

        if ($peopleWithBirthDay->getUrlIcon() != '') {
            $classified .= '<img src="' . $sRootPath . '/skin/icons/markers/' . $peopleWithBirthDay->getUrlIcon() . '">';
        }
        $classified .= '<a href="' . $peopleWithBirthDay->getViewURI() . '" class="btn btn-link-menu" style="text-decoration: none">' . $peopleWithBirthDay->getFullNameWithAge() . '</a>';
        $classified .= '</label>';
        $classified .= '</div>';

        $count_people += 1;
        $count_people %= 4;
        if ($count_people == 0) {
            $classified .= '</div>';
            $new_row = false;
        }
    }

    if ($new_row == true) {
        $classified .= '</div>';
    }

    if ( !empty($classified) ) {
        $global_body .= '<h5 class="card-title"><i class="fas fa-birthday-cake"></i> '. dgettext("messages-BirthdayAnniversaryDashboard","Birthdates of the day") . '</h5>';
        $global_body .= '<div class="card-tools">
            <button type="button" class="btn bg-primary btn-sm" data-card-widget="remove">
                            <i class="fas fa-times"></i>
                        </button>
            <button type="button" class="btn btn-primary btn-sm" data-card-widget="collapse" title="Collapse">
                <i class="fas '. $Card_collapsed_button .'"></i>
            </button>
        </div>

    </div>';
        $global_body .= '<div class="card-body" style="'. $Card_body .'">';
        $global_body .= $classified;
    } ?>

    <?php if ($AnniversariesCount > 0) {
        if ($peopleWithBirthDaysCount > 0) {
            $global_body .= '<hr style="background-color: green; height: 1px; border: 0;">';
        }

        $global_body .= '<h5 class="' .(($peopleWithBirthDaysCount > 0)?'alert-heading':'card-title') .'"><i class="fas fa-birthday-cake"></i> '. dgettext("messages-BirthdayAnniversaryDashboard","Anniversaries of the day") . '</h5>';

        if ($peopleWithBirthDaysCount == 0) {
            $global_body .= '<div class="card-tools">
            <button type="button" class="btn bg-primary btn-sm" data-card-widget="remove">
                            <i class="fas fa-times"></i>
                        </button>
            <button type="button" class="btn btn-primary btn-sm" data-card-widget="collapse" title="Collapse">
                <i class="fas '.$Card_collapsed_button.'"></i>
            </button>
        </div>

    </div>
    <div class="card-body" style="' . $Card_body . '">';
        }

        $new_row = false;
        $count_people = 0;

        foreach ($Anniversaries as $Anniversary) {
            if ($new_row == false) {
                $global_body .= '<div class="row">';

                $new_row = true;
            }
            $global_body .= '<div class="col-md-3">
                <label class="checkbox-inline">
                    <a href="'. $Anniversary->getViewURI() .'" class="btn btn-link-menu"
                       style="text-decoration: none">'.  $Anniversary->getFamilyString() .'</a>
                </label>
            </div>';

            $count_people += 1;
            $count_people %= 4;
            if ($count_people == 0) {
                $global_body .= '</div>';
                $new_row = false;
            }
        }

        if ($new_row == true) {
            $global_body .= '</div>';
        }
    }

    if ($unclassified) {
        if ($peopleWithBirthDaysCount > 0) {
            $global_body .= '<hr style="background-color: green; height: 1px; border: 0;">';
        }

        $global_body .= '<h5 class="' .(($peopleWithBirthDaysCount > 0)?'alert-heading':'card-title') .'"><?= dgettext("messages-BirthdayAnniversaryDashboard","Unclassified birthdates") ?></h5>';

        if ($peopleWithBirthDaysCount == 0) {
            $global_body .= '<div class="card-tools">
            <button type="button" class="btn btn-primary btn-sm" data-card-widget="remove">
                            <i class="fas fa-times"></i>
                        </button>
            <button type="button" class="btn btn-primary btn-sm" data-card-widget="collapse" title="Collapse">
                <i class="fas '. $Card_collapsed_button .'"></i>
            </button>
        </div>

    </div>
    <div class="card-body" style="' . $Card_body . '">';
        }

        $global_body .= '<div class="row">';

        $global_body .= $unclassified;

        $global_body .= '</div>';

    }
    $global_body .= '</div></div>';

    echo $global_body;
}
