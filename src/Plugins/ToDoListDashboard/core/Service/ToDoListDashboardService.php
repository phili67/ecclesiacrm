<?php

namespace Plugins\Service;

use EcclesiaCRM\dto\SystemConfig;

class ToDoListDashboardService
{
    public static function getColorPeriod($date)
    {
        $now = new \DateTime('now', new \DateTimeZone(SystemConfig::getValue('sTimeZone')) );

        $interval = $date->diff($now);

        $days = $interval->format('%d');
        $hours   = $interval->format('%H');
        $minutes = $interval->format('%i');

        $time = $days * 1440 + $hours * 60 + $minutes;

        $color = '';

        if ($time < 60) {
            $color = 'danger';
        } elseif ($time < 60*60) {
            $color = 'warning';
        } elseif ($time < 60*60*24) {
            $color = 'info';
        } elseif ($time < 60*60*24*7) {
            $color = 'primary';
        } elseif ($time < 60*60*24*30) {
            $color = 'success';
        } else {
            $color = 'secondary';
        }

        $period = '';

        if ($time < 60*60) {
            $period = $minutes . ' ' . dgettext("messages-ToDoListDashboard","mins");
        } else if ($time < 60*60*24) {
            $period = $hours . ' ' . dgettext("messages-ToDoListDashboard","hours");
        } else if ($time < 60*60*24*30) {
            $period = $days . ' ' . dgettext("messages-ToDoListDashboard","days");
        } else {
            $period = (int)($days/30) . ' ' . dgettext("messages-ToDoListDashboard","months");
        }

        return [
            'time' => $time,
            'period' => $period,
            'color' => $color
        ];
    }
}
