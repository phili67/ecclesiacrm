<?php

namespace Plugins\Service;

use EcclesiaCRM\dto\SystemConfig;

class ToDoListDashboardService
{
    public static function getColorPeriod($date)
    {
        $now = new \DateTime('now', new \DateTimeZone(SystemConfig::getValue('sTimeZone')) );

        $interval = $now->diff($date);

        $days = $interval->format('%a');
        $hours   = $interval->format('%H');
        $minutes = $interval->format('%i');

        $after = $interval->format('%R');

        $time = $days * 1440 + $hours * 60 + $minutes;


        $period = '';

        if ($time < 60) {
            $period = $minutes . ' ' . dgettext("messages-ToDoListDashboard","min(s)");
        } else if ($time < 60*24) {
            $period = $hours . ' ' . dgettext("messages-ToDoListDashboard","hour(s)");
        } else if ($time < 60*24*7) {
            $period = $days . ' ' . dgettext("messages-ToDoListDashboard","day(s)");
        } elseif ($time < 60*24*30) {
            $period = (int)($days/7) . ' ' . dgettext("messages-ToDoListDashboard","week(s)");
        } else {
            $period = (int)($days/30) . ' ' . dgettext("messages-ToDoListDashboard","month(s)");
        }

        $period = $after. ' ' .$period;

        $after = ($after == "+")?true:false;

        $color = '';

        if ($time < 60) {
            $color = 'danger';
        } elseif ($time < 60*24) {
            $color = 'warning';
        } elseif ($time < 60*24*7) {
            $color = 'info';
        } elseif ($time < 60*24*30) {
            $color = 'primary';
        } elseif ($time < 60*24*30) {
            $color = 'success';
        } else {
            $color = 'secondary';
        }

        if ($after == false) {
            $color = 'secondary';
        }

        return [
            'time' => $time,
            'period' => $period,
            'color' => $color,
            'date' => $date->format('Y-m-d H:i:s'),
            'after' => $after
        ];
    }
}
