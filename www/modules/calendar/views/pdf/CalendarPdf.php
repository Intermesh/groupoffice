<?php

/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: File.class.inc.php 7607 2011-06-15 09:17:42Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */


namespace GO\Calendar\Views\Pdf;

use GO;
use GO\Base\Util\Date;
use GO\Base\Util\Pdf;


final class CalendarPdf extends Pdf {
	private $pageWidth;
	private $_start_time = '';
	private $_end_time = '';
	private $_title = '';
	private $_days = '';
	private $_date_range_text = '';	
	private $_calendar;
	private $_results;
	private $_headerTitle = '';
	
	private $_view=false;
	
	public $cell_height = 12;
	private string|int $_daysDone;

	public function setParams($response, $view=false) {
		
		if(!$view){
			$responses = array($response);
		} else {
			$responses = $response['results'];
		}
		
		
		$headers=true;
		foreach($responses as $r){
			$this->_view=$view;
			$this->_start_time = $r['start_time'];
			$this->_end_time = $r['end_time'];
			$this->_title = !empty($this->_view) ? \GO\Base\Fs\File::stripInvalidChars($this->_view->name).': '.\GO\Base\Fs\File::stripInvalidChars($r['title']) : \GO\Base\Fs\File::stripInvalidChars($r['title']);
			$this->_days = ceil(($this->_end_time - $this->_start_time) / 86400);
			$this->_date_range_text = $this->_days > 1 ? date(\GO::user()->completeDateFormat,$this->_start_time) . ' - ' . date(\GO::user()->completeDateFormat,$this->_end_time) : date(\GO::user()->completeDateFormat,$this->_start_time);

			$this->_results = $r['results'];
			$this->_loadCurrentCalendar($r['calendar_id']);
			$this->_processEvents(!$view, $headers, $view ? $r['view_calendar_name'] : '');
			$headers=false;
		}
		
	}
	
	public function Header() {
		$this->SetY(30);

		$this->SetTextColor(50, 135, 172);
		$this->SetFont($this->font, 'B', 16);
		
		$this->SetTextColor(125, 162, 180);
		$this->SetFont($this->font, '', 12);
		$this->setY($this->getY() + 3.5, false);
		$this->Write(12, $this->_title);									// Print the name of the calendars that are printed in the view.

		$this->setY($this->getY() + 2.5, false);
		$this->SetFont($this->font, 'B', $this->font_size);
		$this->setDefaultTextColor();

		$this->Cell($this->getPageWidth() - $this->getX() - $this->rMargin, 12, $this->_date_range_text, 0, 0, 'R');
	}
	
	private function _processEvents($list=true, $headers=true, $calendar_name=''){
		
		switch((int)$this->_days){
			case 1:
				$this->_headerTitle = \GO::t("1 Day", "calendar");
				break;
			case 5:
				$this->_headerTitle = \GO::t("5 Days", "calendar");
				break;
			case 7:
				$this->_headerTitle = \GO::t("7 Days", "calendar");
				break;
			case 35:
				$this->_headerTitle = \GO::t("Month", "calendar");
				break;
			default:
				$this->_headerTitle = \GO::t("List", "calendar");
				break;
		}
	
		$fullDays = \GO::t("full_days");

		for ($i = 0; $i < $this->_days; $i++) {
			$cellEvents[$i] = array();
		}
		
		foreach($this->_results as $event)
			$this->_insertEvent($event,$cellEvents);
					
		if (($this->_days > 1 && $this->_days<60) || !$list) {

			if($headers)
				$this->AddPage();

			//green border
			$this->SetDrawColor(125, 165, 65);

			$maxCells = $this->_days > 7 ? 7 : $this->_days;
			$minHeight = $this->_days > $maxCells ? 70 : $this->cell_height;

			$nameColWidth = 100;
			$cellWidth = !empty($calendar_name) ? ($this->pageWidth - $nameColWidth) / $maxCells : $this->pageWidth / $maxCells;

			$time_format = str_replace('G', 'H', \GO::user()->time_format);
			$time_format = str_replace('g', 'h', $time_format);

			$this->SetFillColor(248, 248, 248);
			$time = $this->_start_time;

			// print headers
			if ($headers) {
				if (!empty($calendar_name)) {
					$this->Cell($nameColWidth, 20, '', 1, 0, 'L', 1);
				}
				for ($i = 0; $i < $maxCells; $i++) {
 					$label = $this->_days > $maxCells ? $fullDays[date('w', $time)] : $fullDays[date('w', $time)] . ', ' . date(\GO::user()->completeDateFormat, $time);
					$this->Cell($cellWidth, 20, $label, 1, 0, 'L', 1);
		
					// Add the day we are printing to the events array
					foreach ($cellEvents[$i] as $key=>$event) {
						$event['day_for_printing'] = $time;
						$cellEvents[$i][$key] = $event;
					}
					
					$time = Date::date_add($time, 1);
				}
				$this->Ln();
			}

			$this->SetFont($this->font, '', $this->font_size);

			// set these variables right after the header
			
			$cellStartY = $maxY = $this->getY();
			$pageStart = $this->PageNo();

			$this->_daysDone = 0;
			$weekCounter = 0;
			$yBefore = $this->getY();
			$tableLeftMargin = $this->lMargin;
			if (!empty($calendar_name)) {
				$this->SetTextColor(0, 0, 0);
				$this->SetX($this->lMargin);
				$this->MultiCell($nameColWidth, $this->cell_height, $calendar_name, 0, 'L');
				$tableLeftMargin+=$nameColWidth;
				$this->setDefaultTextColor();
				$maxY = $this->getY();
			}


			$biggestPageNo = $pageStart;
			$nCellsOfLongestColumn = 0;
			$sizeOfLongestColumn = $this->getY()-$yBefore;
			
			for ($i = 0; $i < $this->_days; $i++) {
				$pos = $i - $this->_daysDone;
				$this->setPage($pageStart);
				$this->setXY($tableLeftMargin + ($pos * $cellWidth), $cellStartY);

				// If we are using the month view
				if ($this->_days > 7) {
					
					$time = Date::date_add($this->_start_time, $i);
					
					// Add the day we are printing to the events array
					foreach ($cellEvents[$i] as $key=>$event) {
						$event['day_for_printing'] = $time;
						$cellEvents[$i][$key] = $event;
					}
					
					$this->Cell($cellWidth, $this->cell_height, date('d', $time), 0, 1, 'R');
					$this->setX($tableLeftMargin + ($pos * $cellWidth));
				}

				$nCellsOfColumn = 0;
				
				foreach ($cellEvents[$i] as $event) {

					if(isset($event['day_for_printing']) && empty($event['all_day_event']) && $event['day_for_printing'] > strtotime($event['start_time'])){
						// Don't change the name
					} else if(empty($event['all_day_event'])){
						// If it's not a full day event and the start_time is the same time as the day we print
						$event['name']=date($time_format, strtotime($event['start_time'])).': '.$event['name'];
					}
					
					$event['name']=  html_entity_decode($event['name']);
					$event['description']= !empty($event['description']) ? html_entity_decode($event['description']) : '';
					$event['location']= !empty($event['location']) ? html_entity_decode($event['location']) : '';

					$this->SetFillColor(hexdec(substr($event['background'], 0, 2)), hexdec(substr($event['background'], 2, 2)), hexdec(substr($event['background'], 4, 2)));
					
					if (!empty($event['status_color'])) {
						$event_background_color = array(hexdec(substr($event['background'], 0, 2)), hexdec(substr($event['background'], 2, 2)), hexdec(substr($event['background'], 4, 2)));
					} else {
						$event_background_color = array(125, 165, 65);
					}
					
					$event_name	= $event['name'];
					
					if(!empty($event['status_color'])){
						$event_status_color = array(hexdec(substr($event['status_color'], 0, 2)), hexdec(substr($event['status_color'], 2, 2)), hexdec(substr($event['status_color'], 4, 2)));

						$circleLine = array('width'=>0.5,'color'=>$event_status_color);
						$circleFill = $event_status_color;
						$circleX = $this->getX()+5;
						$circleY = $this->getY()+6;
						$circleRadius = 2.5;

						$this->Circle($circleX,$circleY,$circleRadius,0,360,'FD',$circleLine,$circleFill);
						
						$event_name = '   '.$event['name'];
					}

					$this->SetFillColorArray($event_background_color);
					
					$nCells = $this->MultiCell($cellWidth, $this->cell_height,$event_name, array('B'=>array('width' => 2,'color' => array(255, 255, 255))), 1, 1, 1, '', '', true, 0, false, false, 0);
					$nCellsOfColumn += $nCells;
					
					$this->SetDrawColor(125,165, 65);
					$this->SetLineWidth(1); //similiar to cellspacing
					
					$this->setX($tableLeftMargin + ($pos * $cellWidth));
				}

				if ($this->pageNo() > $biggestPageNo)
					$biggestPageNo = $this->pageNo();

				if ($nCellsOfColumn > $nCellsOfLongestColumn) {
					$nCellsOfLongestColumn = $nCellsOfColumn;
					$sizeOfLongestColumn = $nCellsOfLongestColumn*($this->cell_height+0.7);
				}
				
				$y = $this->getY();
				if ($y < $cellStartY) {
					//went to next page so we must add the page height.
					$y+=$this->h;
				}
				if ($y > $maxY)
					$maxY = $y;


				$weekCounter++;
				if ($weekCounter == $maxCells) { // maxCells is the max number of columns, which can be at most 7.
					$this->setPage($pageStart);

					$weekCounter = 0;
					$this->_daysDone+=$maxCells;

					//minimum cell height
					$cellHeight = $sizeOfLongestColumn;// $maxY - $cellStartY;
					$sizeOfLongestColumn = 0;
					$nCellsOfLongestColumn = 0;
					if ($cellHeight < $minHeight) {
						$cellHeight = $minHeight;
					}

					if ($cellHeight + $this->getY() > $this->h - $this->bMargin) { // If cell height would exceed page's current writable space.
						
						do {

							// Set position to upper left corner.
							if ($this->pageNo()==$pageStart)
								$this->setXY($this->lMargin, $cellStartY);
							else
								$this->setXY($this->lMargin, $this->tMargin);
								
							$cellHeightFirstPart = $this->h - $this->getY() - $this->bMargin; // This is the height in the page's writable space that remains from the current position.
							$cellHeightRemaining = $cellHeight - $cellHeightFirstPart; // The surplus cell height.

							if (!empty($calendar_name)) {
								$this->Cell($nameColWidth, $cellHeightFirstPart, '', 'LTR', 0); // Draw cell with left-top-right border for the remaining writable space on the page.
							}
							for ($n = 0; $n < $maxCells; $n++) { // For at most 7 times...
								$this->Cell($cellWidth, $cellHeightFirstPart, '', 'LTR', 0); // ...Draw a cell with left-top-right border for the remaining writable space on the page.
							}
							$this->ln(); // Draw horizontal line.

							$this->addPage();
							
							$cellHeight -= $cellHeightFirstPart;
							
						} while ($cellHeightRemaining + $this->getY() > $this->h - $this->bMargin);
						
						if (!empty($calendar_name)) {
							$this->Cell($nameColWidth, $cellHeightRemaining, '', 'LBR', 0); // 
						}
						for ($n = 0; $n < $maxCells; $n++) {
							$this->Cell($cellWidth, $cellHeightRemaining, '', 'LBR', 0);
						}
						$this->ln();
						
					} else { // If the cell height would not exceed the page height:
						
						$this->setXY($this->lMargin, $cellStartY); // Set position to top left.
						if (!empty($calendar_name)) {
							$this->Cell($nameColWidth, $cellHeight, '', 1, 0); // Draw a cell for the calendar name.
							$this->setPage($pageStart);
						}
						for ($n = 0; $n < $maxCells; $n++) {
							$this->Cell($cellWidth, $cellHeight, '', 1, 0); // Draw the remaining cells of the row.
							$this->setPage($pageStart);
						}
						$this->ln();
					}

					$cellStartY = $maxY = $this->getY();
					$pageStart = $this->PageNo();
				}
			}
			
			for ($i=$pageStart; $i<$biggestPageNo; $i++)
				$this->addPage();
			
		}
		
		if ($list) {

			$this->CurOrientation = 'P';

			$this->AddPage();

			$this->H1(\GO::t("List of appointments", "calendar"));

			$time = $this->_start_time;
			for ($i = 0; $i < $this->_days; $i++) {

				if (count($cellEvents[$i])) {
					
					$this->ln(10);
					
					$this->setCellPaddings(0,0,0,0);
					
					$this->H3($fullDays[date('w', $time)] . ', ' . date(\GO::user()->completeDateFormat, $time));
					
					$this->setCellPaddings(13,0,0,0);
					
					$this->SetFont($this->font, '', $this->font_size);
					
					while ($event = array_shift($cellEvents[$i])) {
						
						if(!empty($event['background'])){
							$event_background_color = array(hexdec(substr($event['background'], 0, 2)), hexdec(substr($event['background'], 2, 2)), hexdec(substr($event['background'], 4, 2)));
							
							$rectLine = array('width'=>0.5,'color'=>$event_background_color);
							$rectFill = $event_background_color;
							$rectX = $this->getX()+1;
							$rectY = $this->getY()+1;

							$this->Rect($rectX, $rectY, 8, 24, 'F',$rectLine,$rectFill);
						}
						else
							$event_background_color = array(0,0,0);
						
						if(!empty($event['status_color'])){
							
							$event_status_color = array(hexdec(substr($event['status_color'], 0, 2)), hexdec(substr($event['status_color'], 2, 2)), hexdec(substr($event['status_color'], 4, 2)));

							$circleLine = array('width'=>0.5,'color'=>$event_status_color);
							$circleFill = $event_status_color;
							$circleX = $this->getX()+5;
							$circleY = $this->getY()+6;
							$circleRadius = 2.5;

							$this->Circle($circleX,$circleY,$circleRadius,0,360,'F',$circleLine,$circleFill);
						}
						
						$this->H4($event['name']);
						
						if (empty($event['all_day_event'])) {
							$text = sprintf(\GO::t("From %s till %s", "calendar"), $event['start_time'], $event['end_time']);
						} else {
							$start_date = date(\GO::user()->date_format, strtotime($event['start_time']));
							$end_date = date(\GO::user()->date_format, strtotime($event['end_time']));

							if ($start_date == $end_date) {
								$text = sprintf(\GO::t("All day", "calendar"));
							} else {
								$text = sprintf(\GO::t("All day from %s till %s", "calendar"), $start_date, $end_date);
							}
						}

						if (!empty($event['location']))
							$text .= sprintf(\GO::t(" at location \"%s\"", "calendar"), $event['location']);

						$pW = $this->getPageWidth() - $this->lMargin - $this->rMargin;
						
						
						$this->Cell($pW, 10, $text, 0, 1);
						if (!empty($event['description'])) {
							$event['description'] = str_replace("<br />\n<br />","\n",$event['description']);
							$event['description'] = str_replace("<br />\n","\n",$event['description']);
							$this->ln(4);
							$this->MultiCell($pW, 10, html_entity_decode($event['description']), 0, 'L', 0, 1);
						}

						$this->ln(10);
						$lineStyle = array(
							'color' => array(40, 40, 40),
							'width' => .5
						);
						$this->Line($this->lMargin, $this->getY(), $this->getPageWidth() - $this->rMargin, $this->getY(), $lineStyle);
						$this->ln(10);
					}
				}
				$time = Date::date_add($time, 1);
			}
		}
	}

	private function _loadCurrentCalendar($calendarId) {
		
		if(empty($calendarId)) {
			throw new GO\Base\Exception\NotFound();
		}
		
		$this->_calendar = \GO\Calendar\Model\Calendar::model()->findByPk($calendarId);
	}

	private function _insertEvent($event,&$cellEvents) {
		$startTime = strtotime($event['start_time']);
		$endTime = strtotime($event['end_time']);
		
		$startDate = getdate($startTime);
		
		$index_time = mktime(0, 0, 0, $startDate['mon'], $startDate['mday'], $startDate['year']);
		while ($index_time <= $endTime && $index_time < $this->_end_time) {
			if ($this->_calendar->user_id != \GO::user()->id && !empty($event['private'])) {
				$event['name'] = \GO::t("Private", "calendar");
				$event['description'] = '';
				$event['location'] = '';
			}

			$cellIndex = Date::date_diff_days($this->_start_time, $index_time);
			$index_time = Date::date_add($index_time, 1);
			$cellEvents[$cellIndex][] = $event;
		}
	}
}
