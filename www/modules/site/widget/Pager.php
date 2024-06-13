<?php


namespace GO\Site\Widget;


class Pager extends \GO\Site\Components\Widget {

	/**
	 * A prefix for the pager parameter
	 * @var StringHelper 
	 */
	protected $requestPrefix = '';

	/**
	 * A store object that is responsable for fetchin results
	 * 
	 * @var \GO\Base\Data\DbStore 
	 */
	public $store;
	
	/**
	 * The classname for the previous link
	 * @var StringHelper 
	 */
	public $previousPageClass = 'previous';
	
	/**
	 * The classname for the previous link
	 * @var StringHelper 
	 */
	public $nextPageClass = 'next';
	
	/**
	 * The number of item on 1 page
	 * If not specified it will take the number from the GO config.php
	 * @var int 
	 */
	protected $pageSize;
	
	/**
	 * the current page number
	 * @var int 
	 */
	public $currentPage=1;
	
	
	protected $currentPageClass = 'current';
	
	protected $pageParam = 'p';
	
	private $_models;
	
	public function init() {
		if(isset($_GET[$this->pageParam]))
			$this->currentPage = $_GET[$this->pageParam];
		
		if(empty($this->pageSize))
			$this->pageSize = \GO::user() ? \GO::user()->max_rows_list : \GO::config()->nav_page_size;
			
		if(!($this->store instanceof \GO\Base\Data\DbStore))
			throw new \Exception('store needs to be an instance of \GO\Base\Data\Store');
		
		$this->store->start = $this->pageSize * ($this->currentPage-1);
		$this->store->limit = $this->pageSize;
	}
	
	/**
	 * The current active page
	 * @return integer
	 */
	public function getCurrentPage() {
		return $this->store->start / $this->store->limit;
	}
	
	/**
	 * Total item count of all pages
	 * @return The total items found in the database
	 */
	public function getTotalItems() {
		return $this->store->getTotal();
	}
	
	/**
	 * Get the total number of pages
	 * @return int
	 */
	public function getPageCount() {
		return (int)(($this->store->getTotal()+$this->pageSize-1)/$this->pageSize);
	}

	/**
	 * The link for the page with the given number
	 * @param int $pageNum number of page
	 * @return string URL to page
	 */
	private function getPageUrl($pageNum){
		$params = array_merge($_GET,array($this->requestPrefix.$this->pageParam=>$pageNum));
		return \Site::urlManager()->createUrl(\Site::router()->getRoute(), $params);
	}
	
	/**
	 * Render the pagination.
	 */
	public function render(){

		$result = '';
		if($this->currentPage != 1)
			$result.= '<a class="'.$this->nextPageClass.'" href="'.$this->getPageUrl($this->currentPage-1).'"><</a>';

		for($page=1;$page<=$this->pageCount;$page++)
			$result.= ($page == $this->currentPage) ? '<span class="'.$this->currentPageClass.'">'.$page.'</span>' : '<a href="'.$this->getPageUrl($page).'">'.$page.'</a>';

		if($this->currentPage < $this->pageCount)
			$result.= '<a class="'.$this->previousPageClass.'" href="'.$this->getPageUrl($this->currentPage+1).'">></a>';
		return $result;
	}
	
	/**
	 * get an array of models with item on the current page
	 * @return array with active records
	 */
	public function getItems() {
		if(empty($this->_models))
			$this->_models = $this->store->getModels();
		return $this->_models;
	}
	
	public function getRecords() {
		return $this->store->getRecords();
	}
	
}
