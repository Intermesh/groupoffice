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
 * @version $Id: Week.php 21220 2017-06-12 09:43:55Z mschering $
 * @author Michael de Hart <mdhart@intermesh.nl>
 */
class Week extends Calendar {
	
	/**
	 * @var CalendarEvent[]
	 */
	protected $events = array();
	protected $notes = array('Note1', 'Note2', 'Note3');
	protected $tasks = array('Task1', 'Task2', 'Task3');
	
	protected $dayCount = 7; //the amount of days to render in week view
	
	public function Header() {
		//A4 = 21 x 29.7
		$this->setXY(12,12);
		
		$width = $this->getPageWidth()-$this->leftMargin*2;
		
		$this->Rect($this->leftMargin, 10, $width, 30,'DF', $this->thickBorder, $this->greyFill);
		$this->SetFont(null, 'B',$this->fSizeLarge);
		$this->Cell(100, 12, date('d. ',$this->day).$this->months_long[date('n',$this->day)].date(' Y',$this->day).' -', 0, 1);
		
		//$this->SetLin
		
		$this->setX(12);
		$this->SetFont(null, 'B', $this->fSizeLarge);
		$end = $this->day+($this->dayCount-1)*24*3600;
		$this->Cell(100, 12, date('d. ',$end).$this->months_long[date('n',$end)].date(' Y',$end), 0, 1);
		
		$this->drawCalendar($this->day, 110, 12);
		$this->drawCalendar($this->day+(32*24*3600), 160, 12);
		
		$this->setXY($this->leftMargin,41);
		
	}
	
	public function Footer() {
		$bottom = 238;
		$page = $this->getPageWidth()-(2*$this->leftMargin);
		
		$this->Rect($this->leftMargin, 41, $this->timeCol, $bottom-(3*$this->rowHeight),'D', $this->thickBorder);
		$this->Rect($this->leftMargin+$this->timeCol, 41, $page-$this->timeCol, $bottom-(3*$this->rowHeight),'D', $this->thickBorder);
		$this->Rect($this->leftMargin, $bottom+$this->headerHeight-(3*$this->rowHeight), $page, 3*$this->rowHeight,'D', $this->thickBorder);
		parent::Footer();
	}
	
	public function setEvents($value){
		$this->events = $this->orderEvents($value);
	}
	
	/**
	 * Will render a page with the given week
	 * @param unix timestamp $week somewhere in the week you like to render
	 */
	public function render() { // $week
		//$this->events = $events;
		$this->AddPage();
		$this->SetFont(null,'',$this->fSizeSmall);
		
		for($w=0;$w<$this->dayCount;$w++) {
			$this->calculateOverlap($w);
		}
		
		$this->drawEventsBackground();
		$this->drawEvents();
	}
	
	public function drawEventsBackground() {

		$left = $this->timeCol; // width of cell with time
		$width = $this->getPageWidth()-(2*$this->leftMargin)-$this->timeCol;
		$width = $width/$this->dayCount;
		$minus = 2;
		
		//Render top items
		$this->Cell($left, ($this->rowHeight*3)-$minus, '', 1, 0, '', true);
		for($w=0;$w<$this->dayCount;$w++) {
			$x=$this->GetX();
			$this->SetFont(null, 'B');
			$this->Cell($width, $this->rowHeight-$minus, date('d',$this->day+3600*24*$w), 1, 0);
			$this->SetFont(null, '', $this->font_size-0.5);
			$this->SetX($x+1);
			$this->Cell($width, $this->rowHeight-$minus, $this->days_long[date('N',$this->day+3600*24*$w)-1], 0,0,'C');
			$this->SetFont(null, '', $this->font_size);
			$this->SetX($x);
			$this->Cell($width, ($this->rowHeight*3)-$minus, '', 1, 0);
		}
		$this->Ln();

		//Render rows
		for($i=0;$i<24;$i++){
			if($i%2==0) {
				$hour = ($i/2+7);
				$this->drawTime($hour);
			} else
				$this->SetX($this->leftMargin+$this->timeCol);
			
			for($w=0;$w<$this->dayCount;$w++) {
				$this->Cell($width, $this->rowHeight, '', 1, 0);
			}
			
			$this->Ln();
		}
		
		//Render bottom 3 rows
		for($i=0;$i<3;$i++) {
			$this->Cell($left, $this->rowHeight, '', 1, 0, '', true);
			for($w=0;$w<$this->dayCount;$w++)
				$this->Cell($width, $this->rowHeight, '', 1, 0);
			$this->Ln();
		}
		
	}
	
	public function drawEvents() {

		$offsetLeft = $this->leftMargin+$this->timeCol;
		$width = $this->getPageWidth()-(2*$this->leftMargin)-$this->timeCol;
		$width = $width/$this->dayCount;
		$colWidth = $width;
		for($w=0;$w<$this->dayCount;$w++) {
			
			$this->SetDrawColorArray($this->eventLineColor);
			if(isset($this->events[$this->day]['fd'])) {
				$x=$w*$colWidth+20; 
				$i=0;
				foreach($this->events[$this->day]['fd'] as $event) {
					if($i>2) { //stop after 3 full day events (todo draw a triangle)
						$this->Image('modules/calendar/themes/Default/images/pdf/arrow_down.png',$offsetLeft+($w+1)*$colWidth-4, 48+5*$i, 3,3, 'PNG');
						break;
					}
					$this->SetXY($x, 48+5*$i);
					$this->EventCell($event->name, $colWidth, 5);
					//$this->Cell($colWidth, 5 , $event->name, 1, 1, 'L', true);
					$i++;
					
				}
			}
			if(isset($this->events[$this->day]['part'])) {
				
				$this->SetLineWidth(0.3);
				foreach($this->events[$this->day]['part'] as $event) {
					$x=$w*$colWidth;
					$this->drawEvent($x, $colWidth, $event);
				}
				$this->SetLineWidth(0.1);
			}
			$this->SetDrawColorArray($this->lineStyle['color']);
			if(isset($this->events[$this->day]['late'])) {
				$x = $this->headerHeight+214;$i=0;
				$x = $offsetLeft+($w+1)*$colWidth- 4;
				$this->SetXY($x ,$this->headerHeight+209);

				foreach($this->events[$this->day]['late'] as $event) {
					if($i>2) { //stop after 3 full day events (todo draw a triangle)
						$this->Image('modules/calendar/themes/Default/images/pdf/arrow_down.png',$offsetLeft+($w+1)*$colWidth-4, 252+$this->rowHeight*$i, 3,3, 'PNG');
						break;
					}
					$this->SetXY($offsetLeft+$w*$colWidth ,255+$this->rowHeight*$i);
					$this->EventCell(date('G:i',$event->start_time) .' - '. date('G:i',$event->end_time) .' '. $event->name, $colWidth, $this->rowHeight);
					//$this->Write(5,date('G:i ', $event->start_time).' - '.date('G:i ', $event->end_time).' '. $event->name);
					$i++;
				}
			}
			
			$this->day += 24*3600;
		}
	}
	
}
