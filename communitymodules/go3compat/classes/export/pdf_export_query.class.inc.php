<?php
/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: pdf_export_query.class.inc.php 17809 2014-07-22 11:23:28Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */

require_once($GLOBALS['GO_CONFIG']->class_path.'tcpdf/tcpdf.php');


class pdf_export_query extends base_export_query{
	var $extension='pdf';

	var $row_body_column=false;

	var $html = '';
	
	function init_columns(){
		parent::init_columns();

		if(!empty($_SESSION['GO_SESSION']['export_queries'][$_REQUEST['query']]['pdf_row_body_column'])){
			$this->row_body_column = $_SESSION['GO_SESSION']['export_queries'][$_REQUEST['query']]['pdf_row_body_column'];

			$key = array_search($this->row_body_column, $this->columns);
			unset($this->columns[$key]);
			unset($this->headers[$key]);
		}
	}

	function download_headers()
	{
		$browser = detect_browser();
		header('Expires: '.gmdate('D, d M Y H:i:s') . ' GMT');
		if ($browser['name'] == 'MSIE')
		{
			header('Content-Type: application/download');
			header('Content-Disposition: attachment; filename="'.rawurlencode($this->title).'.pdf";');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');
		}else
		{
			header('Content-Type: application/pdf');
			header('Pragma: no-cache');
			header('Content-Disposition: attachment; filename="'.$this->title.'.pdf"');
		}
		header('Content-Transfer-Encoding: binary');
	}


	function print_column_headers(){

		$this->html .= '<thead><tr>';

		//$this->pdf->SetFillColor(241,241,241);
		//$this->pdf->SetTextColor(255,255,255);
		$this->pdf->cellWidth = $this->pdf->pageWidth/count($this->columns);
		if(count($this->headers)){
			for($i=0;$i<count($this->headers);$i++)
			{
				$align = in_array($this->columns[$i], $this->q['totalize_columns']) ? 'right' : '';
				$this->html .= '<td style="background-color:rgb(248, 248, 248);" align="'.$align.'">'.$this->headers[$i].'</td>';

				//$align = in_array($this->columns[$i], $this->q['totalize_columns']) ? 'R' : 'L';
				//$this->pdf->Cell($this->pdf->cellWidth, 20, $this->headers[$i], 1,0,$align, 1);
				//$this->pdf->MultiCell($this->pdf->cellWidth, 20, $this->headers[$i], 1,$align, 1,0);
			}
			//$this->pdf->Ln();
		}
		$this->html .= '</tr></thead><tbody>';
	}

	/*function format_record(&$record){
		if(is_array($this->q) && isset($this->q['method']))
		{
			call_user_func_array(array($this->q['class'], $this->q['method']),array(&$record, $this->cf));
		}
	}*/

	function init_pdf(){
		global $GO_CONFIG;
		$this->pdf = new export_pdf();
		//green border
		$this->pdf->SetDrawColor(125,165, 65);
		$this->pdf->SetFillColor(248, 248, 248);
		$this->pdf->SetTextColor(0,0,0);

		$this->pdf->SetTitle($_REQUEST['title']);
		$this->pdf->SetSubject($_REQUEST['title']);
		$this->pdf->SetAuthor($_SESSION['GO_SESSION']['name']);
		$this->pdf->SetCreator($GLOBALS['GO_CONFIG']->product_name.' '.$GLOBALS['GO_CONFIG']->version);
		$this->pdf->SetKeywords($_REQUEST['title']);
	}

	function export($fp){
		parent::export($fp);
		global $GO_CONFIG, $lang, $GO_MODULES;

		require_once($GLOBALS['GO_CONFIG']->class_path.'base/users.class.inc.php');
		$GO_USERS = new GO_USERS();

		$this->init_pdf();

		$this->pdf->AddPage();


		$this->html = '<table border="1" cellpadding="2" cellspacing="0">';

		if(count($this->columns))
		{
			$this->print_column_headers();
		}
		

		$fill=false;
		while($record = $this->db->next_record())
		{
			$this->increase_totals($record);

			if(!count($this->columns))
			{
				foreach($record as $key=>$value)
				{
					$this->columns[]=$key;
					$this->headers[]=$key;
				}
				$this->print_column_headers();
			}

			$this->format_record($record);

			if(isset($record['user_id']) && isset($this->columns['user_id']))
			{
				$record['user_id']=$GO_USERS->get_user_realname($record['user_id']);
			}

			/*$lines=1;
			foreach($this->columns as $index)
			{
				$new_lines = $this->pdf->getNumLines($record[$index],$this->pdf->cellWidth);
				if($new_lines>$lines)
				{
					$lines = $new_lines;
				}
			}

			if($lines*($this->pdf->font_size+2)+8+$this->pdf->getY()>$this->pdf->getPageHeight()-$this->pdf->getBreakMargin())
			{
				$this->pdf->AddPage();
				$this->print_column_headers();
			}

			foreach($this->columns as $index)
			{
				$align = in_array($index, $this->q['totalize_columns']) ? 'R' : 'L';
				$this->pdf->MultiCell($this->pdf->cellWidth,$lines*($this->pdf->font_size+2)+8, $record[$index],1,$align,$fill,0);
			}
			$this->pdf->Ln();

			if($this->row_body_column){
				$this->pdf->MultiCell($this->pdf->cellWidth*count($this->columns),$lines*($this->pdf->font_size+2)+8, $record[$this->row_body_column],1,'L',$fill,1);
				$this->pdf->Ln(5);
			}*/

			$this->html .= '<tr nobr="true">';
			foreach($this->columns as $index)
			{
				$align = in_array($index, $this->q['totalize_columns']) ? 'right' : '';
				$this->html .= '<td align="'.$align.'">'.htmlspecialchars($record[$index], ENT_COMPAT, 'UTF-8').'</td>';
			}
			$this->html .= '</tr>';



			//$fill=!$fill;
		}


		if(isset($this->totals) && count($this->totals))
		{
			/*$this->pdf->Ln();
			$this->pdf->Cell($this->pdf->getPageWidth(),20,$lang['common']['totals'].':');
			$this->pdf->Ln();
			foreach($this->columns as $index)
			{
				$value = isset($this->totals[$index]) ? Number::format($this->totals[$index]) : '';
				$this->pdf->Cell($this->pdf->cellWidth, 20, $value, 'T',0,'R');
			}*/

			$this->html .= '<tr><td colspan="'.count($this->columns).'"><br /><b>'.$lang['common']['totals'].':</b></td></tr>';
			$this->html .= '<tr>';
			foreach($this->columns as $index)
			{
				$value = isset($this->totals[$index]) ? Number::format($this->totals[$index]) : '';

				$this->html .= '<td align="right">'.htmlspecialchars($value, ENT_COMPAT, 'UTF-8').'</td>';
			}
			$this->html .= '</tr>';

		}

		$this->html .= '</thead></table>';
		//exit();

		$this->pdf->writeHTML($this->html);

		fwrite($fp, $this->pdf->Output('export.pdf', 'S'));
		//fclose($fp);
	}
}


class export_pdf extends TCPDF
{
	var $font = 'helvetica';
	var $pageWidth;
	var $font_size=9;
	var $cell_height=12;


	function __construct()
	{
		global $GO_CONFIG;

		if(!empty($GLOBALS['GO_CONFIG']->tcpdf_font))
		{
			$this->font = $GLOBALS['GO_CONFIG']->tcpdf_font;
		}

		$orientation = isset($_REQUEST['orientation']) ? $_REQUEST['orientation'] : 'L';

		parent::__construct($orientation, 'pt', 'A4', true, 'UTF-8');

		$this->AliasNbPages();

		$this->setJPEGQuality(100);
		$this->SetMargins(30,60,30);

		$this->SetFont($this->font,'',$this->font_size);

		$this->pageWidth =$this->getPageWidth()-$this->lMargin-$this->rMargin;

		$this->SetAutoPageBreak(true, 30);

	}

	function Footer(){
		global $GO_CONFIG, $lang;

		$this->setDefaultTextColor();
		$this->SetFont($this->font,'',$this->font_size);
		$this->SetY(-20);
		$pW=$this->getPageWidth();
		$this->Cell($pW/2, 10, $GLOBALS['GO_CONFIG']->product_name.' '.$GLOBALS['GO_CONFIG']->version, 0, 0, 'L');
		$this->Cell(($pW/2)-$this->rMargin, 10, sprintf($lang['common']['printPage'], $this->getAliasNumPage(), $this->getAliasNbPages()), 0, 0, 'R');
	}

	function Header(){

		global $lang;

		$this->SetY(30);

		$this->SetTextColor(50,135,172);
		$this->SetFont($this->font,'B',16);
		$this->Write(16, $_REQUEST['title']);

		if(!empty($_REQUEST['subtitle']))
		{
			$this->SetTextColor(125,162,180);
			$this->SetFont($this->font,'',12);
			$this->setXY($this->getX()+5, $this->getY()+3.5);
			$this->Write(12, $_REQUEST['subtitle']);
		}


		$this->setY($this->getY()+2.5, false);

		$this->SetFont($this->font,'B',$this->font_size);
		$this->setDefaultTextColor();

		$this->Cell($this->getPageWidth()-$this->getX()-$this->rMargin,12,Date::get_timestamp(time()),0,0,'R');

		if(!empty($_REQUEST['text']))
		{
			$this->SetFont($this->font,'',$this->font_size);
			$this->Ln(20);
			$this->MultiCell($this->getPageWidth(), 12, $_REQUEST['text']);
		}
		
		if(!empty($_REQUEST['html']))
		{
			$this->SetFont($this->font,'',$this->font_size);
			$this->Ln(20);
			
			$this->writeHTML($_REQUEST['html']);
		}
		
		if(empty($_REQUEST['text']) && empty($_REQUEST['html']))
		{
			$this->Ln();
		}

		$this->SetTopMargin($this->getY()+10);

	}

	function calcMultiCellHeight($w, $h, $text)
	{
		$text = str_replace("\r",'', $text);
		$lines = explode("\n",$text);
		$height = count($lines)*$h;

		foreach($lines as $line)
		{
			$width = $this->GetStringWidth($line);

			$extra_lines = ceil($width/$w)-1;
			$height += $extra_lines*$h;
		}
		return $height;
	}

	function H1($title)
	{
		$this->SetFont($this->font,'B',16);
		$this->SetTextColor(50,135,172);
		$this->Cell($this->getPageWidth()-$this->lMargin-$this->rMargin,20, $title,0,1);
		$this->setDefaultTextColor();
		$this->SetFont($this->font,'',$this->font_size);
	}

	function H2($title)
	{

		$this->SetFont($this->font,'',14);
		$this->Cell($this->getPageWidth()-$this->lMargin-$this->rMargin,24, $title,0,1);
		$this->SetFont($this->font,'',$this->font_size);
	}

	function H3($title)
	{
		$this->SetTextColor(125,165, 65);
		$this->SetFont($this->font,'B',11);
		$this->Cell($this->getPageWidth()-$this->lMargin-$this->rMargin,14, $title,'',1);
		$this->SetFont($this->font,'',$this->font_size);
		$this->setDefaultTextColor();
		$this->ln(4);
	}

	function H4($title)
	{
		$this->SetFont($this->font,'B',$this->font_size);
		//	$this->SetDrawColor(90, 90, 90);
		//$this->SetDrawColor(128, 128, 128);
		$this->Cell($this->getPageWidth()-$this->lMargin-$this->rMargin,14, $title,'',1);
		//$this->SetDrawColor(0,0,0);
		$this->SetFont($this->font,'',$this->font_size);


	}

	function setDefaultTextColor()
	{
		$this->SetTextColor(40,40,40);
	}
}