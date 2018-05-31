<?php

// holidays with fixed date
$input_holidays['fix']['12-31'] = 'New Years Eve';
$input_holidays['fix']['01-01'] = 'New Years Day';
$input_holidays['fix']['12-24'] = 'Christmas Eve';
$input_holidays['fix']['12-25'] = 'Christmas Day';
$input_holidays['fix']['11-11'] = 'Verterans Day';
$input_holidays['fix']['07-04'] = 'Independence Day';

// holidays with variable date (christian holidays computation is based on the date of easter day)
$input_holidays['var']['-2'] = 'Good Friday';
$input_holidays['var']['0'] = 'Easter Sunday';
$input_holidays['var']['1'] = 'Easter Monday';

$input_holidays['fn'][] = array('Martin Luther King Day', array('GOHolidaysUS', 'mlkDay'));
$input_holidays['fn'][] = array('Memorial Day', array('GOHolidaysUS', 'memorialDay'));
$input_holidays['fn'][] = array('Labor Day', array('GOHolidaysUS', 'laborDay'));
$input_holidays['fn'][] = array('Columbus Day', array('GOHolidaysUS', 'columbusDay'));
$input_holidays['fn'][] = array('Thanksgiving Day', array('GOHolidaysUS', 'thanksgivingDay'));
$input_holidays['fn'][] = array('Presidents Day', array('GOHolidaysUS', 'presidentsDay'));
$input_holidays['fn'][] = array('Mothers Day', array('GOHolidaysUS', 'mothersDay'));
$input_holidays['fn'][] = array('Fathers Day', array('GOHolidaysUS', 'fathersDay'));

if (!class_exists('GOHolidaysUS')) {

  class GOHolidaysUS {

    public static function mlkDay($year) {
      return (new \DateTime('third mon of January ' . $year))->format('Y-m-d');
    }

    public static function memorialDay($year) {
      return (new \DateTime('last mon of May ' . $year))->format('Y-m-d');
    }

    public static function laborDay($year) {
      return (new \DateTime('first mon of September ' . $year))->format('Y-m-d');
    }

    public static function columbusDay($year) {
      return (new \DateTime('second mon of October ' . $year))->format('Y-m-d');
    }

    public static function thanksgivingDay($year) {
      return (new \DateTime('fourth thu of November ' . $year))->format('Y-m-d');
    }

    public static function presidentsDay($year) {
      return (new \DateTime('third mon of February ' . $year))->format('Y-m-d');
    }

    public static function mothersDay($year) {
      return (new \DateTime('second sun of May ' . $year))->format('Y-m-d');
    }

    public static function fathersDay($year) {
      return (new \DateTime('third sun of June ' . $year))->format('Y-m-d');
    }

  }

}
