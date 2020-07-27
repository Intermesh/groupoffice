<?php
namespace go\modules\community\tasks\model;

class Recurrence {
    public $interval = 1;
    public $rscale = 'gregorian';
    public $skip = 'omit';
    public $firstDayOfWeek = 'mo';
    public $byMonthDay;
    public $byMonth;
    public $byYearDAy;
    public $byWeekNo;
    public $byHour;
    public $byMinute;
    public $bySecond;
    public $bySetPosition;
    public $count;
    public $until;
}