<?php

/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * Usage: instantiate a subclass of this class to write to external pdf class,
 * given the parameters.
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id:
 * @copyright Copyright Intermesh
 * @author Wilmar van Beusekom <wilmar@intermesh.nl>
 */

class pdf_custom_tag_handler
{
	function __construct($pdf_class,$params=array())
	{
		$this->pdf = $pdf_class;
		$this->params = $params;
	}

	public function print2pdf() {
		// use $this->pdf to write to the pdf here
	}
}