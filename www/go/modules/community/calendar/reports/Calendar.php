<?php
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
namespace go\modules\community\calendar\reports;


use go\modules\community\calendar\model\CalendarEvent;
use go\modules\community\calendar\model\RecurrenceRule;


abstract class Calendar extends \go\core\util\PdfRenderer {
	const IMG_PATH = '../go/modules/community/calendar/reports/assets/';
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
	 * @var \DateTime A unixtimestamp of the day to display
	 */
	public $day;
	public $end;
	protected $currentDay;

	/**
	 * @var CalendarEvent[]
	 */
	protected $events = [];
	protected $early = [];
	protected $late = [];

	public function __construct($orientation = 'P', $unit = 'mm', $size = 'A4') {
		$this->defaultFont = 'helvetica';
		parent::__construct($orientation, $unit, $size);

		$this->setCellPaddings(1,1,0,1);
		$this->days_long = go()->t("full_days");
		$this->months_short = go()->t("short_months");
		$this->months_long = go()->t("full_months");
		$this->days_short = go()->t("short_days");
		if(\GO::user()->first_weekday==1) { // place sunday at the end
			$this->days_long[] = array_shift($this->days_long);
			$this->days_short[] = array_shift($this->days_short);
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

	public function setEvents($events){
		$start = $this->day;
		$this->early = [];
		$this->late = [];
		$this->events = [];
		foreach($events as $event) {
			if($event->isRecurring()) {
				foreach(RecurrenceRule::expand($event, $start->format('Y-m-d'), $this->end->format('Y-m-d')) as $rId => $instance) {
					$this->add($rId.'-'.$event->id, $instance);
				}
			} else {
				$event->utcStart = $event->start();
				$event->utcEnd = $event->end();
				$this->add($event->start()->format('Y-m-d\TH:i:s').'-'.$event->id, $event);
			}
		}

		ksort($this->events);

		//$this->events = $this->orderEvents($events);
	}

	protected function add($key, $instance) {
		$this->events[$key] = $instance;
	}

	public function drawCalendar($date=null, $x=100, $y=null, $w=35, $h=28) {
		if($date===null)
			$date=new \DateTime();
		if($y)
			$this->SetY($y);
		
		$firstDay = strtotime($date->format('Y-m-01'));
		$lastDay = strtotime($date->format('Y-m-t'));
		
		$this->SetFont(null,'',7);
		$this->SetLeftMargin($x);
		$this->Cell($w,$h/8, $this->months_long[$date->format('n')].$date->format(' Y'),0,1, 'C');

		for($d=0;$d<7;$d++) {
			$this->Cell($w/7,$h/8,$this->days_short[$d], 0,0,'R');
		}
		
		$this->Ln();
		$this->Line($this->GetX(), $this->GetY(), $this->GetX()+$w, $this->GetY(),['width'=>0.1]);
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

		$this->SetFont(null, 'B', $this->defaultFontSize+5);
		$this->SetX($this->leftMargin-6);
		$this->MultiCell($this->timeCol+2, 10, $hour, 0, 'R',false,0);
		$this->SetFont(null, '', $this->defaultFontSize);
		$this->SetX($left+$this->leftMargin-5);
		$this->MultiCell($this->leftMargin, 10,'00',0,'L',false,0);

		$this->SetX($x);
	}
	
	public function drawEvent($startx, $colWidth, CalendarEvent $event, $topPadding=-1) {
		$o = $this->eventOptions[$event->id.'-'.$this->currentDay];

		$length = ($o['endM'] - $o['startM']) / 15;
		$start = $o['startM']  / 15 - 12; //padding
		$x = $o['pos'] * $colWidth / $o['lanes'];
		$x+=$startx;
		$width = $colWidth / $o['lanes'];

		//$this->SetXY(25 +$x,($this->rowHeight / 2) * $start);
		//$this->Cell($width - 0.3, ($this->rowHeight / 2) * $length, '', 1, 1, 'L', true);
		$mx =  $this->leftMargin+$this->timeCol+$x;
		$my = ($this->rowHeight / 2) * $start + $topPadding;
		$this->SetXY($mx,$my);
		$this->EventCell('<b>'.$event->title."</b><br>".$event->location, $width, ($this->rowHeight / 2) * $length);
		
		$iconY = $this->GetY()+($this->rowHeight / 2) * $length-4;
		//Draw the icons
		$icons=4;
		if($event->isRecurring()) {
			$this->Image(self::IMG_PATH.'recuring.png',$this->GetX()-$icons,$iconY , 3,3, 'PNG');// refresh;
			$icons+=4;
		}
//		if($event->isException()) {
//			$this->Image('modules/calendar/themes/Default/images/pdf/exception.png',$this->GetX()-$icons, $iconY, 3,3, 'PNG'); //exception
//			$icons+=4;
//		}
		if(!empty($event->links)) {
			$this->Image(self::IMG_PATH.'paperclip.png',$this->GetX()-$icons, $iconY, 3,3, 'PNG'); //paperclip
			$icons+=4;
		}
		if(!$event->useDefaultAlerts) {
			$this->Image(self::IMG_PATH.'reminder.png',$this->GetX()-$icons, $iconY, 3,3, 'PNG');//bell
		}
		if($event->isPrivate()) {
			$this->Image(self::IMG_PATH.'private.png',$this->GetX()-$icons, $iconY, 3,3, 'PNG'); //lock
		}
		
	}
	
	protected function EventCell($text, $width, $h, $x=null, $y=null) {
		if($x===null)
			$x=$this->GetX();
		if($y===null)
			$y=$this->GetY();
		$width = min($width,135-13);
		$this->Rect($x+0.3, $y, $width - 0.6, $h, 'DF',[],['color'=>255]);
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
