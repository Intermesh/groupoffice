<?php

/*
 * Copyright Intermesh BV
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 */

/**
 * Keeper of the database transaction
 * Has a flag that check of transaction is already started
 * Queries like CREATE and TRUNCATE dont alway support tranactions but commit right away
 * 
 * Can be used as follows
 * <pre>
 * $transaction=$connection->beginTransaction();
 * try
 * {
 *    $connection->createCommand($sql1)->execute();
 *    $connection->createCommand($sql2)->execute();
 *    //.... other SQL executions
 *    $transaction->commit();
 * }
 * catch(\Exception $e)
 * {
 *    $transaction->rollBack();
 * }
 * </pre>
 *
 * @package GO.db
 * @copyright Copyright Intermesh
 * @version $Id Transaction.php 2012-06-14 10:22:16 mdhart $ 
 * @author Michael de Hart <mdehart@intermesh.nl> 
 */

namespace GO\Base\Db;


class Transaction
{

	private $_connection = null;
	private $_active;

	/**
	 * Constructor.
	 * @param CDbConnection $connection the connection associated with this transaction
	 * @see CDbConnection::beginTransaction
	 */
	public function __construct(Connection $connection)
	{
		$this->_connection = $connection;
		$this->_active = true;
	}

	/**
	 * Commits a transaction.
	 * @throws CException if the transaction or the DB connection is not active.
	 */
	public function commit()
	{
		if ($this->_active && $this->_connection->getActive())
		{
			$this->_connection->getPdoInstance()->commit();
			$this->_active = false;
		}
		else
			throw new \GO\Base\Exception\Database('Transaction is inactive and cannot perform commit operation.');
	}

	/**
	 * Rolls back a transaction.
	 * @throws CException if the transaction or the DB connection is not active.
	 */
	public function rollback()
	{
		if ($this->_active && $this->_connection->getActive())
		{
			$this->_connection->getPdoInstance()->rollBack();
			$this->_active = false;
		}
		else
			throw new \GO\Base\Exception\Database('Transaction is inactive and cannot perform roll back operation.');
	}

}

?>
