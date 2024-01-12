<?php

namespace go\modules\community\calendar\reports;


/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @copyright Copyright Intermesh
 * @version $Id: Day.php 22115 2018-01-12 10:41:26Z mschering $
 * @author Michael de Hart <mdhart@intermesh.nl>
 */
class Day extends Calendar {
	
	/**
	 * @var \GO\Calender\Model\Event[]
	 */
	protected $events = array();
	protected $notes = array(); //unused (no notes attache to calendar in groupoffice)
	public $tasks = array();
	
	protected $leftCol = 135;
	protected $rightCol = 60;
	
	public function Header() {
		//A4 = 21 x 29.7
		$this->setXY($this->leftMargin+2,12);
		$pageWidth = $this->getPageWidth()-$this->leftMargin*2;
		
		$this->Rect($this->leftMargin, 10, $pageWidth, 30,'DF', array(), $this->greyFill);
		$this->SetFont(null, 'B',$this->fSizeLarge);
		$this->Cell(100, 12, date('d. ',$this->day).$this->months_long[date('n',$this->day)].date(' Y',$this->day), 0, 1);
		
		$this->setX($this->leftMargin+2);
		$this->SetFont(null, '', $this->fSizeMedium+3);
		$this->Cell(100, 5, $this->days_long[date('N',$this->day)-1], 0, 1);
		
		$this->drawCalendar($this->day, 110, 12);
		$this->drawCalendar($this->day+(32*24*3600), 160, 12);
		
		$this->setXY(10,41);
	}
	
	public function Footer() {
		$bottom = 238;
		$width = $this->leftCol-$this->timeCol;
		
		$this->Rect($this->leftMargin, 41, $this->timeCol, $bottom-(3*$this->rowHeight),'D', $this->thickBorder);
		$this->Rect($this->leftMargin+$this->timeCol, 41, $width, $bottom-(3*$this->rowHeight),'D', $this->thickBorder);
		$this->Rect($this->leftMargin, $bottom+$this->headerHeight-(3*$this->rowHeight), $this->leftCol, 3*$this->rowHeight,'D', $this->thickBorder);
		parent::Footer();
	}
	
	public function setEvents($value){
		$this->events = $this->orderEvents($value);
	}
	
	/**
	 * Renders a page in the PDF with the given day parmeter
	 * @param integer $day unixtimestamp of day you like to render
	 */
	public function render() { //$day
		$this->day = \GO\Base\Util\Date::clear_time($day);
		$this->currentDay = $this->day;
		//$this->events = $this->orderEvents($events);
		$this->AddPage();
		$this->calculateOverlap();
		
		$this->drawEventsBackground();
		$this->drawEvents();
		
		$this->drawTasks();
		$this->drawNotes();
	}
	
	public function drawEventsBackground() {
		
		$left = $this->timeCol; // width of cell with time
		$minus = 2;
		
		$this->Cell($left, ($this->rowHeight*3)-$minus, '', 1, 0, '', true);

		$x=$this->GetX();
		$this->Rect($this->GetX(), $this->GetY(),$this->leftCol-$left, $this->rowHeight-$minus, '',$this->thickBorder);
		$this->SetLineStyle($this->lineStyle);
		
		$this->SetFont('','B');
		$this->Cell($this->leftCol-$left, $this->rowHeight-$minus, date('d.',$this->day), 0, 0);
		$this->SetFont('','');
		$this->SetX($x);
		$this->Cell($this->leftCol-$left, $this->rowHeight-$minus, $this->days_long[date('N',$this->day)-1], 0,1,'C');
		$this->SetX($x);
		$this->Cell($this->leftCol-$left, $this->rowHeight*2, '', 1, 1);
		
		
		for($i=0;$i<24;$i++){
			if($i%2==0) {
				$hour = ($i/2+7);
				$this->drawTime($hour);
				
				//$this->Cell($left, $this->rowHeight * 2, $i < 24 ? str_pad($i/2+7, 2, '0', STR_PAD_LEFT).':00':'', 1, 0);
			} else
				$this->setX($this->leftMargin+$this->timeCol);
			$this->Cell($this->leftCol-$left, $this->rowHeight, '', 1, 1);
		}
		$this->Cell($left, $this->rowHeight, '', 1, 0, '', true);
		$this->Cell($this->leftCol-$left, $this->rowHeight, '', 1, 1);
		$this->Cell($left, $this->rowHeight, '', 1, 0, '', true);
		$this->Cell($this->leftCol-$left, $this->rowHeight, '', 1, 1);
		$this->Cell($left, $this->rowHeight, '', 1, 0, '', true);
		$this->Cell($this->leftCol-$left, $this->rowHeight, '', 1, 1);
	}
	
	protected function drawEvents() {
		
		$colWidth = $this->leftCol - $this->timeCol;
		if(isset($this->events[$this->day]['early'])) {
			$x=$this->headerHeight+6; $i=0;
			foreach($this->events[$this->day]['early'] as $event) {
				if($i>1) {
					$this->Image('modules/calendar/themes/Default/images/pdf/arrow_down.png',$this->leftMargin+$this->leftCol-4, $x+$this->rowHeight*$i-5, 3,3, 'PNG');
					break;
				}
				$this->SetXY($this->leftMargin+$this->timeCol ,$x+$this->rowHeight*$i);
				$this->Cell($colWidth, $this->rowHeight , date('G:i ', $event->start_time).' - '.date('G:i ', $event->end_time).' '. $event->name, 1, 1, 'L', false);
				$i++;
			}
		}
		if(isset($this->events[$this->day]['part'])) {
			$this->SetDrawColorArray($this->eventLineColor);
			$this->SetLineWidth(0.3);
			foreach($this->events[$this->day]['part'] as $event) {
				$this->drawEvent(0, $colWidth, $event);
			}
			$this->SetLineWidth(0.1);
			$this->SetDrawColorArray($this->lineStyle['color']);

		}
		if(isset($this->events[$this->day]['late'])) {
			
			$x = $this->headerHeight+214;$i=0;
			$this->SetXY($this->leftMargin+$this->leftCol-4 ,$x+$this->rowHeight-14);
			foreach($this->events[$this->day]['late'] as $event) {
				if($i>2) {
					$this->Image('modules/calendar/themes/Default/images/pdf/arrow_down.png',$this->leftMargin+$this->leftCol-4, $x+$this->rowHeight*$i-5, 3,3, 'PNG');
					break;
				}
				$this->SetXY($this->leftMargin+$this->timeCol ,$x+$this->rowHeight*$i);
				$this->EventCell(date('G:i',$event->start_time) .' - '. date('G:i',$event->end_time) .' '. $event->name, $colWidth, $this->rowHeight);
				//$this->Cell($colWidth, $this->rowHeight , date('G:i ', $event->start_time).' - '.date('G:i ', $event->end_time).' '. $event->name, 1, 1, 'L', false);
				$i++;
				
			}
		}
	}

	public function drawTasks() {
		$margin = $this->getMargins();
		$x = $this->getPageWidth()-$this->rightCol-$this->rMargin; // $this->leftCol+$margin['left']+2;
		$w= $this->rightCol;
				
		$this->SetXY($x, $this->headerHeight);
		
		$this->SetFillColor(240);
		$this->Cell($w, $this->rowHeight-3, \GO::t("Tasklist", "tasks") .' '. \GO::t("Today"), 1,1,'C', true);
		
		foreach($this->tasks as $task) {
			$this->SetX($x);
			$this->MultiCell($w, $this->rowHeight - 3, $task->name, 1,'L',false,1);
		}
		
		$this->Rect($x, $this->headerHeight, $w, 29*$this->rowHeight/2, '',$this->thickBorder);
		$this->SetLineStyle($this->lineStyle);
	}
	
	public function drawNotes() {
		
		$margin = $this->getMargins();
		$x = $this->getPageWidth()-$this->rightCol-$this->rMargin;
		$y = $this->headerHeight+14.5*$this->rowHeight+2;
		$w= $this->rightCol; //200-$this->leftCol-$margin['left']-2;

		$this->SetXY($x, $y);
		$this->Cell($w, $this->rowHeight-3, \GO::t("All day", "calendar"), 1,1,'C', true);
		
		$this->Rect($x, $this->headerHeight+14.5*$this->rowHeight+2, $w, 30*$this->rowHeight/2, '',$this->thickBorder);
		if(!isset($this->events[$this->day]['fd']))
			return;
		
		foreach($this->events[$this->day]['fd'] as $event) {
			$this->SetX($x);
			$this->MultiCell($w, $this->rowHeight - 3, $event->name, 1,'L',false,1);
		}
		
		
	}
}
