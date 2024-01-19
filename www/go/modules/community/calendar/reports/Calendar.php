<?php

namespace go\modules\community\calendar\reports;


use go\modules\community\calendar\model\CalendarEvent;

/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @copyright Copyright Intermesh
 * @version $Id: Calendar.php 22381 2018-02-16 10:02:56Z mschering $
 * @author Michael de Hart <mdhart@intermesh.nl>
 */
abstract class Calendar extends \go\core\util\PdfRenderer {
	
	protected $fSizeLarge = 24;
	protected $fSizeMedium = 9;
	protected $fSizeSmall = 8;
	protected $rowHeight = 8;
	protected $leftCol = 24;
	protected $headerHeight = 41;
	protected $timeCol = 13; //Column width where time is rendered in day and week
	protected $footerY = 17;
	
	protected $leftMargin = 7;
	protected $eventLineColor = array(10,100,190);
	protected $lineStyle = array('width'=>0.1, 'color'=>array(120));
	protected $thickBorder = array('all'=>array('color'=>array(0),'width'=>0.3));
	protected $greyFill = array(235);
	
	protected $days_short = array();
	protected $days_long= array();
	protected $months_short= array();
	protected $months_long= array();
	
	public $calendarName;
	
	/**
	 *
	 * @var integer A unixtimestamp of the day to display
	 */
	public $day;
	protected $currentDay;
	
	protected function init() {
		parent::init();
		$this->font_size--;
		$this->setCellPaddings(1,1,0,1);
		$this->days_long = \GO::t("full_days");
		$this->months_short = \GO::t("short_months");
		$this->months_long = \GO::t("full_months");
		$this->days_short = \GO::t("short_days");
		if(\GO::user()->first_weekday==1) { // place sunday at the end
			$elm = array_shift($this->days_long);
			array_push($this->days_long, $elm);
			$elm = array_shift($this->days_short);
			array_push($this->days_short, $elm);
		}
	
		$this->SetMargins($this->leftMargin, 41);
		$this->SetFillColorArray($this->greyFill);
		$this->setPageUnit('mm');
		$this->SetDrawColor(0,0,0);
		$this->SetLineStyle($this->lineStyle);
		$this->SetAutoPageBreak(false, 10);
	}

	public function Footer() {
		$width = $this->getPageWidth()-$this->leftMargin*2;
		$this->SetFont(null,'',$this->fSizeMedium);
		$this->SetY($this->getPageHeight()-$this->footerY);
		$x=$this->GetX();
		$this->Cell($width, 5, $this->calendarName, 0, 0, 'L');
		$this->SetX($x);
		$this->Cell($width, 5, $this->GetPage(), 0,0,'C');
		$this->SetX($x);
		$this->Cell($width, 5, \GO\Base\Util\Date::format(date('Y-m-d H:i')), 0,0,'R');
	}
	
	/**
	 * Place events in a subarray orderd by date
	 * Renames private event to Private
	 * @param CalendarEvent[] $events
	 * @return array
	 */
	protected function orderEvents($events) {
		$result=array();
		foreach($events as $evento) {
			// Duplicate the event because it may not be "BY REFERENCE"
			$event = $evento->getEvent()->duplicate(array(), false);
			$event->id = $evento->getEvent()->id.':'.$evento->getAlternateStartTime();
			
			$participant = $event->getParticipantOfCalendar();
			if($participant && $participant->status == \GO\Calendar\Model\Participant::STATUS_DECLINED) {
					continue;
			}
                        
			if($event->isPrivate()){
				$event->name = \GO::t("Private", "calendar");
				$event->description = '';
				$event->location = '';
			}
			
			
			$event->start_time = $evento->getAlternateStartTime();
			$event->end_time = $evento->getAlternateEndTime();
			$day = \GO\Base\Util\Date::clear_time($event->start_time);
			$end = $this->day + (6*7*24*60*60); // 6 weeks
			\GO::debug($event->name);
			do {
				\GO::debug(date('Y-m-d',$day));
				$type = $event->isFullDay()?'fd':'part';
				if($type=='part' && date('G',$event->start_time) < 7)
					$type='early';
				if($type=='part' && date('Gi',$event->end_time) > 1900)
					$type='late';
				$result[$day][$type][] = $event; 
				$day = \GO\Base\Util\Date::date_add($day, 1); //\GO\Base\Util\Date::clear_time((new \Datetime('@'.$day))->add(new \DateInterval('P1D'))->getTimestamp());
			} while($event->end_time >= $day && $day < $end);
		}
		return $result;
	}
	
	protected $eventOptions = array(); // max, col, start, end, span
	
	/**
	 * Will calculate the event overlap parameter for displaying
	 * multiple events at the same time
	 * 
	 * Took me 3 days to write so don't touch it!
	 * Used by Week and Day reports
	 */
	protected function calculateOverlap($w=0) {
		
		$day = $this->day+($w*24*3600);
		$this->currentDay = $day;
		
		if(!isset($this->events[$day]['part']))
			return;
		
		$rows = array();

		// place in rows per quarter
		foreach($this->events[$day]['part'] as $key => $event) {
			list($start, $end) = $this->_getStartEndRow($event);
			$this->eventOptions[$event->id.'-'.$this->currentDay] = array('start'=>$start, 'end'=>$end, 'span'=>1);
			for($it=$start; $it<$end; $it++) {
				$rows[$it][$event->id.'-'.$this->currentDay]=$event;
			}
		}
		
		// located connections (events at the same time)
		foreach($this->events[$day]['part'] as $key => $event) {
			$max = 1;
			list($start, $end) = array_values($this->eventOptions[$event->id.'-'.$this->currentDay]);
			for($it=$start; $it<$end; $it++) {
				$max = max($max,count($rows[$it]));
			}
			$this->eventOptions[$event->id.'-'.$this->currentDay]['max'] = $max;	
		}
		
		$position=0;
		$prevMax=1;
		$previousCols = array();
		foreach($this->events[$day]['part'] as $key => $event) {
			list($start, $end, $span, $max) = array_values($this->eventOptions[$event->id.'-'.$this->currentDay]);
			\GO::debug($event->id);
			$col = $position % $prevMax;
			
			if($col+1 == $prevMax)
				$position=0; //\GO::debug(' --> next row ');
			
			$pcol = $col = $position % $prevMax;
			$ppos = $position;
			
			while ($pcol != $col || $ppos==$position/* && $ppos<6*/) {
				$ppos++;
				if(!isset($previousCols[$pcol])) {
					$pcol = $ppos % $prevMax;
					continue;
				}
				$previous = $this->eventOptions[$previousCols[$pcol]->id.'-'.$this->currentDay];

				//collision detection
				if($previous['end'] > $start && $pcol == $col) {
					\GO::debug('  --> push by '.$previousCols[$pcol]->name);
					$position++;
					$col = $position % $prevMax;
					$max = max($max,$previous['max']);
					$this->eventOptions[$previousCols[$pcol]->id]['max'] = max($max,$previous['max']);
					
				}
				else if($previous['end'] > $start) {
					//\GO::debug('  -v- shrinking '.substr($previousCols[$pcol]->name, 0,4));
					$max = max($max,$previous['max']);
				}
				
				$pcol = $ppos % $prevMax;
			}
			
			$col = $position % $max;

			$this->eventOptions[$event->id.'-'.$this->currentDay]['max']=$max;
			$this->eventOptions[$event->id.'-'.$this->currentDay]['col']=$col;
			//$this->eventOptions[$event->id.'-'.$this->currentDay]['span']=$span;

			$previousCols[$col] = $event;
			$prevMax = $max;
		}
	}
	
	/**
	 * Start is the quester of the day this event should be rendered
	 * @param type $event
	 * @return type
	 */
	protected function _getStartEndRow($event) {
		$day = \GO\Base\Util\Date::clear_time($event->start_time);
		$startedToday = true;
		if($day < $this->currentDay) {
			$day = $this->currentDay;
			$startedToday = false;
		}
		
		$start = round(($event->start_time - $day) / 60 / 15); //= seconds in quarter
		$end = round(($event->end_time - $day) / 60 / 15); //= seconds in quarter
		if(!$startedToday) {
			$start = 28; // 07:00
		}
		if($this->currentDay < \GO\Base\Util\Date::clear_time($event->end_time)) {
			$end = 76; // 19:00
		}
		return array($start, $end);
	}
	
	/**
	 * Draw the tiny little calendar in the top right corner
	 * @param type $date
	 * @param type $x
	 * @param type $y
	 * @param type $w
	 * @param type $h
	 */
	public function drawCalendar($date=null, $x=100, $y=null, $w=35, $h=28) {
		if($date===null)
			$date=time();
		if($y)
			$this->SetY($y);
		
		$firstDay = strtotime(date('Y-m-01', $date));
		$lastDay = strtotime(date('Y-m-t', $date));
		
		$this->SetFont(null,'',7);
		$this->SetLeftMargin($x);
		$this->Cell($w,$h/8, $this->months_long[date('n',$date)].date(' Y', $date),0,1, 'C');

		for($d=0;$d<7;$d++) {
			$this->Cell($w/7,$h/8,$this->days_short[$d], 0,0,'R');
		}
		
		$this->Ln();
		$this->Line($this->GetX(), $this->GetY(), $this->GetX()+$w, $this->GetY(),array('width'=>0.1));
		$day='';
		for($r=0;$r<6;$r++){
			for($c=0;$c<7;$c++){ //toggle weekday
				if($this->wd(date('N',$firstDay))==$c && $day==='')
					$day=1;
				$this->Cell($w/7,$h/8,$day, 0,0,'R');
				if(!empty($day))
					$day++;
				if($day>date('d',$lastDay))
					$day=null;
			}
			$this->Ln();
		}
		$this->SetLeftMargin($this->leftMargin);
	}
	
	/**
	 * Draws the left column with hours
	 */
	public function drawTime($hour) {
		$left = $this->timeCol;
		
		$x = $this->GetX();
				///$this->Cell($left, $this->rowHeight*2, '00', 1, 0, 'R', true, '',0,false,'T','T');
		$this->Cell($left, $this->rowHeight*2, '', 1, 0, '', true);
		$x = $this->GetX();
		//$x2 = $this->GetX();
		//$this->SetX($x);

		$this->SetFont(null, 'B', $this->font_size+5);
		$this->SetX($this->leftMargin-6);
		$this->MultiCell($this->timeCol+2, 10, $hour, 0, 'R',false,0);
		$this->SetFont(null, '', $this->font_size);
		$this->SetX($left+$this->leftMargin-5);
		$this->MultiCell($this->leftMargin, 10,'00',0,'L',false,0);

		$this->SetX($x);
	}
	
	public function drawEvent($startx, $colWidth, $event, $topPadding=-1) {
		$o = $this->eventOptions[$event->id.'-'.$this->day];

		$length = $o['end'] - $o['start'];
		$start = $o['start']-12; //padding
		$x = $o['col'] * $colWidth / $o['max'];
		$x+=$startx;
		$width = $colWidth / $o['max'] * $o['span'];
		
		//$this->SetXY(25 +$x,($this->rowHeight / 2) * $start);
		//$this->Cell($width - 0.3, ($this->rowHeight / 2) * $length, '', 1, 1, 'L', true);
		$this->SetXY($this->leftMargin+$this->timeCol+$x,($this->rowHeight / 2) * $start + $topPadding);
		$this->EventCell('<b>'.$event->name."</b><br>".$event->location, $width, ($this->rowHeight / 2) * $length);
		
		$iconY = $this->GetY()+($this->rowHeight / 2) * $length-4;
		//Draw the icons
		$icons=4;
		if($event->isRecurring()) {
			$this->Image('modules/calendar/themes/Default/images/pdf/recuring.png',$this->GetX()-$icons,$iconY , 3,3, 'PNG');// refresh;
			$icons+=4;
		}
		if($event->isException()) {
			$this->Image('modules/calendar/themes/Default/images/pdf/exception.png',$this->GetX()-$icons, $iconY, 3,3, 'PNG'); //exception
			$icons+=4;
		}
		if($event->countLinks()>0) {
			$this->Image('modules/calendar/themes/Default/images/pdf/paperclip.png',$this->GetX()-$icons, $iconY, 3,3, 'PNG'); //paperclip
			$icons+=4;
		}
		if($event->hasReminders()) {
			$this->Image('modules/calendar/themes/Default/images/pdf/reminder.png',$this->GetX()-$icons, $iconY, 3,3, 'PNG');//bell
		}
		if($event->isPrivate()) {
			$this->Image('modules/calendar/themes/Default/images/16x16/private.png',$this->GetX()-$icons, $iconY, 3,3, 'PNG'); //lock
		}
		
	}
	
	protected function EventCell($text, $width, $h, $x=null, $y=null) {
		if($x===null)
			$x=$this->GetX();
		if($y===null)
			$y=$this->GetY();
		$this->Rect($x+0.3, $y, $width - 0.6, $h, 'DF',array(),array('color'=>255));
		$this->StartTransform(); //will clip text in the Rectangle on the next line
		$this->Rect($x, $y, $width - 0.3, $h, 'CEO');
		$this->WriteHtmlCell($width - 0.3, $h,$x, $y, $text, 0,0,false,true,'L');
		$this->StopTransform();
	}
	
	/**
	 * Convert sundays to mondays
	 * @param int $wd 0 for sunday
	 * @return int 0 for monday
	 */
	protected function wd($wd) {
		if(\GO::user()->first_weekday==0)
			return $wd;
		if ($wd==0) 
			return 6;
		else 
			return --$wd;
	}
}
