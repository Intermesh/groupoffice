<?php
/*
 * 
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 */

/**
 * An extended version of the PDOStatement PHP class that provides extra
 * functionality.
 * 
 * @version $Id: File.class.inc.php 7607 2011-06-15 09:17:42Z mschering $
 * @copyright Copyright Intermesh BV.
 * @author Merijn Schering <mschering@intermesh.nl> 
 * @package GO.base.db
 */


namespace GO\Base\Db;


class ActiveStatement implements \IteratorAggregate {
	
  /**
   * The model type this statement result returns.
   * 
   * @var ActiveRecord 
   */
  public $model;
	
	/**
	 *
	 * @var PDOStatement 
	 */
	public $stmt;
  
  /**
   * Parameters  that were passed to \GO\BaseDb\activeRecord::find()
   * 
   * @var array 
   */
  public $findParams;
  
  /**
   * If the statement was returned by a relational query eg. $model->relationName() then this
   * is set to the relation name.
   * 
   * @var String 
   */
  public $relation;
  
  /**
   * Calculate the number of found rows when using a limit and calcFoundRows.
	 *
	 * Note: for simply counting the number of records in a statement use rowCount();
	 *  
	 * The total number of found rows. Even when specifying a limit it will return 
	 * the number of rows as if you wouldn't have specified a limit.
	 * 
	 * It is only set when calcFoundRows was passed to the ActiveRecord::find() function parameters.
   * 
   * @var int 
   */
  public $foundRows;
	
//	public $id;
//	public static $idCount=0;
//	public static $aliveStmts=array();

  public function __construct(\PDOStatement $stmt, ActiveRecord $model) {
    $this->stmt=$stmt;
		
		$this->model=$model;
		
		$stmt->setFetchMode(\PDO::FETCH_CLASS, $model->className(),array(false));
		
//		$this->id=self::$idCount++;
//		
//		ZLog::Write(LOGLEVEL_DEBUG, $stmt->queryString);
//		self::$aliveStmts[$this->id]=$stmt->queryString;
  }
	
//	public function __destruct() {
//		unset(self::$aliveStmts[$this->id]);
//	}
	
	
	/**
	 * Calls the specified function on each model that's in the result set of 
	 * the statement object.
	 * 
	 * @param String $function 
	 */
	public function callOnEach($function, $verbose = false) {
		while ($m = $this->fetch()) {
			if (method_exists($m, $function))
				try {
					if ($verbose) {
						$function . " " . $m->className() . "\n";
						flush();
					}
					$m->$function();
				} catch (\Throwable $e) {
					if($verbose) {
						echo "ERROR: " . (string) $e;
					} else{
						throw $e;
					}
				}
		}
	}
	
	/**
	 * Fetch a model from the statement
	 * 
	 * @return ActiveRecord
	 */
	public function fetch($fetch_style=null){
		return $this->stmt->fetch($fetch_style);
	}
	
	/**
	 * Get all models from the find result
	 * 
	 * @return ActiveRecord[]
	 */
	public function fetchAll($fetch_style=null){
		return $this->stmt->fetchAll($fetch_style);
	}
	
	/**
	 * Count the number of rows in the result
	 * 
	 * @return int
	 */
	public function rowCount() {
		return $this->stmt->rowCount();
	}
	
//	public function foundRows(){
//		//Total numbers are cached in session when browsing through pages.
//		
//		$queryUid = $this->queryString;
//		if(empty($this->findParams['start'])){
//			
//			$distinct = stripos($this->queryString, 'distinct');
//			$fromPos = stripos($this->queryString, 'from');
//			
//			$sql = "SELECT ";
//			if($distinct)
//				$sql .= 'DISTINCT ';
//			
//			$sql .= 'count(*) as found ';
//			$sql .= substr($this->queryString, $fromPos);
//			
//			$sql = preg_replace('/^LIMIT .*$/mi','', $sql);
//			$sql = preg_replace('/^ORDER BY .*$/mi','', $sql);
//			$sql = preg_replace('/^LEFT JOIN .*$/mi','', $sql);			
//			\GO::debug($sql);
//		
//			$r = \GO::getDbConnection()->query($sql);
//			$foundRows = \GO::session()->values[$queryUid]=intval($r->fetchColumn(0));	
//		}else
//		{
//			$foundRows=\GO::session()->values[$queryUid];
//		}
//		
//		return $foundRows;
//	}
	
	/**
	 * Get the result as a key->value array.
	 * 
	 * You need to specify which column needs to be used as key column and which 
	 * culumn needs to be used as value column
	 * 
	 * @param StringHelper $keyColumn
	 * @param StringHelper $valueColumn
	 * @return array 
	 */
	public function fetchKeyValueArray($keyColumn, $valueColumn){
		$array = array();
		
		while($m = $this->fetch()){	
			$array[$m->$keyColumn] = $m->$valueColumn;
		}
		
		return $array;
	}
	
	/**
	 * Making the activefinder iterable
	 * @return \IteratorIterator 
	 */
	public function getIterator()
	{
		return $this->stmt;
	}
	

}
