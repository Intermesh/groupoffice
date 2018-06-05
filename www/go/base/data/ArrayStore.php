<?php

/*
 * Copyright Intermesh BV.
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 */

/**
 * The ArrayStore provider is useful to generate arrays for output to the view.
 * 
 * @version $Id: ArrayStore.php 7607 2011-08-04 13:41:42Z wsmits $
 * @copyright Copyright Intermesh BV.
 * @author Wesley Smits <wsmits@intermesh.nl>
 * @author Merijn Schering <mschering@intermesh.nl>
 * @package GO.base.data
 */

namespace GO\Base\Data;


class ArrayStore extends AbstractStore {

	public function __construct($columnModel = false, $data = array()) {
		parent::__construct($columnModel);
		$this->response['results'] = $data;
	}

	public function getData() {
		$this->response['success'] = true;
		$this->response['results'] = $this->getRecords();
		$this->response['total'] = $this->getTotal();
		return $this->response;
	}

	public function getRecords() {
		$records = array();
		foreach ($this->response['results'] as $record) {
			if ($record && is_a($record, 'GO\Base\Model'))
				$records[] = $this->_columnModel->formatModel($record);
			elseif (is_array($record))
				$records[] = $record;
		}
		return $records;
	}

	/**
	 * Inserts an array of models to be used by the store.
	 * This will overwrite all added models by addRecord
	 * @param array $model an array of \GO\Base\Model dirived objects
	 */
	public function setRecords($models) {
		$this->response['results'] = $models;
	}

	/**
	 * Add models to the result response.
	 * @param \GO\Base\Model[] $models array of model objects
	 */
	public function addRecords($models) {
		if (!isset($this->response['results']))
			$this->response['results'] = array();
		array_merge($this->response['results'], $models);
	}

	public function getTotal() {
		return count($this->response['results']);
	}

	public function nextRecord() {
		$record = next($this->response['results']);

		if ($record && is_a($record, 'GO\Base\Model'))
			$record = $this->_columnModel->formatModel($record);
		return $record;
	}

}
