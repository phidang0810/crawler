<?php

namespace App\Libraries;

class BusinessDay
{
    static $holidays = [
        '03/17/*',
        '05/28/*',
        '07/04/*',
        '09/03/*',
        '10/08/*',
        '10/08/*',
        '11/12/*',
        '11/22/*',
        '12/25/*',
        '12/31/*'
    ];
    static $workingDay = [1, 2, 3, 4, 5];

    static function getNumberBetween($from, $to)
    {

        $from = \DateTime::createFromFormat('m/d/Y', $from);
        $to = \DateTime::createFromFormat('m/d/Y', $to);
        $interval = new \DateInterval('P1D');
        $periods = new \DatePeriod($from, $interval, $to);

        $days = 0;
        foreach ($periods as $period) {
            if (!in_array($period->format('N'), self::$workingDay)) continue;
            if (in_array($period->format('m/d/Y'), self::$holidays)) continue;
            if (in_array($period->format('m/d/*'), self::$holidays)) continue;
            $days++;
        }
        return $days;
    }

    static function add($startDate, $businessDays)
    {
        $startDate = \DateTime::createFromFormat('m/d/Y', $startDate);
        while ($businessDays >= 1) {
            $startDate = $startDate->modify(' +1 day');
            $day = $startDate->format('N');
            if (in_array($day, self::$workingDay) && !in_array($startDate->format('m/d/*'), self::$holidays)) $businessDays--;
        }
        return $startDate->format('m/d/Y');
    }
}