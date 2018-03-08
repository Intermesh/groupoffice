<?php
// holidays with fixed date
$input_holidays['fix']['01-01'] = 'Neujahr';
$input_holidays['fix']['01-06'] = 'Hl. 3 Könige';
$input_holidays['fix']['05-01'] = 'Maifeiertag';
$input_holidays['fix']['08-15'] = 'Maria Himmelfahrt';
$input_holidays['fix']['10-03'] = 'Tag d. D. Einheit';
$input_holidays['fix']['10-31'] = 'Reformationstag';
$input_holidays['fix']['11-01'] = 'Allerheiligen';
$input_holidays['fix']['12-25'] = '1. Weihnachtstag';
$input_holidays['fix']['12-26'] = '2. Weihnachtstag';

// holidays with variable date (christian holidays computation is based on the date of easter day)
$input_holidays['var']['-48'] = 'Rosenmontag';
$input_holidays['var']['-47'] = 'Fastnacht';
$input_holidays['var']['-46'] = 'Aschermittwoch';
$input_holidays['var']['-2'] = 'Karfreitag';
$input_holidays['var']['0'] = 'Ostersonntag';
$input_holidays['var']['1'] = 'Ostermontag';
$input_holidays['var']['39'] = 'Christi Himmelfahrt';
$input_holidays['var']['49'] = 'Pfingstsonntag';
$input_holidays['var']['50'] = 'Pfingstmontag';
$input_holidays['var']['60'] = 'Fronleichnam';

// holidays with special computation based on date 24-12
$input_holidays['spc']['-32'] = 'Buss- und Bettag';
