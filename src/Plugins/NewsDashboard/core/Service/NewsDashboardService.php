<?php

namespace Plugins\Service;

class NewsDashboardService
{
    public static function getImage($type)
    {
        $res = '';
        switch ($type) {
            case 'infos':
                $res = 'infos.png';
                break;
            case 'to_plan':
                $res = 'to_plan.png';
                break;
            case 'to_note':
                $res = 'note.png';
                break;
            case 'important':
                $res = 'important.png';
                break;
            case 'very_important':
                $res = 'very_important.png';
                break;
        }

        return $res;
    }
}
