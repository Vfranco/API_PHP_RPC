<?php

namespace Core;

use Database\Database;

class Helper
{
    static function setExpirationDate($currentDate, $days)
    {
        if($days == 0)
            return '0';
        
        return date('Y-m-d', strtotime($currentDate . ' + ' . (int)$days . ' days'));
    }

    static function getDaysFromPlan($plan)
    {
        $getDays = Database::query([
            'fields'        => "tiempo_gracia as days",
            'table'         => "sg_mis_planes",
            'arguments'     => "id_sg_plan = '". $plan ."' LIMIT 1"
        ])->assoc('days');

        return (int)$getDays;
    }

    static function dontApply($data)
    {
        return (!isset($data) && empty($data)) ? 'N/A' : $data;
    }
}