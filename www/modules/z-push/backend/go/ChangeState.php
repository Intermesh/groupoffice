<?php

class ChangeState
{
	/**
	 * @var Store for the data
	 */
	protected $store;

	/**
	 * The mailboxId, adressbookId, calendarId, noteBookId or taskListId
	 * when syncing the folder itself it is set to false
	 * @var false|string
	 */
	protected $folderId;
	/**
	 * @var array State for the current folderId
	 */
	protected $state;

	protected $flags;
	protected $cutoffdate;
	private $contentClass;
	protected $contentParams;

	// used for move MoveStates
	private $srcState;
	private $dstState;

	public function __construct($store, $folderId = false)
	{
		$this->store = $store;
		$this->folderId = $folderId;
	}

	/**
	 * Initializes the state and flags
	 *
	 * @param string $state
	 * @param int $flags
	 * @return boolean      status flag
	 */
	public function Config($state, $flags = 0)
	{
		if ($state == "")
			$state = []; // FFS!

		$this->flags = $flags;
		$this->state = $state;
		return true;
	}

	/**
	 * Configures additional parameters used for content synchronization
	 * @param ContentParameters $contentparameters
	 */
	public function ConfigContentParameters($contentparameters)
	{
		$this->contentParams = $contentparameters;

		$filtertype = $contentparameters->GetFilterType();
		switch ($contentparameters->GetContentClass()) {
			case "Email":
				$this->cutoffdate = ($filtertype) ? Utils::GetCutOffDate($filtertype) : false;
				break;
			case "Calendar":
				$this->cutoffdate = ($filtertype) ? Utils::GetCutOffDate($filtertype) : false;
				break;
			default:
			case "Contacts":
			case "Tasks":
				$this->cutoffdate = false;
				break;
		}
		$this->contentClass = $contentparameters->GetContentClass();
	}

	/**
	 * Reads and returns the current state
	 * This is what is saved after data is synchonized
	 * @return string
	 */
	public function GetState()
	{
		if (!isset($this->state) || !is_array($this->state))
			throw new StatusException("ChangesState->GetState(): Error, state not available or unable to update: ",
				($this->folderId ? SYNC_STATUS_FOLDERHIERARCHYCHANGED : SYNC_FSSTATUS_CODEUNKNOWN), null, LOGLEVEL_WARN);

		return $this->state;
	}

	/**
	 * Sets the states from move operations.
	 * When src and dst state are set, a MOVE operation is being executed.
	 *
	 * @param mixed $srcState
	 * @param mixed         (opt) $dstState, default: null
	 *
	 * @access public
	 * @return boolean
	 */
	public function SetMoveStates($srcState, $dstState = null)
	{
		$this->srcState = $srcState;
		$this->dstState = $dstState;
		return true;
	}


	/**
	 * Gets the states of special move operations.
	 *
	 * @access public
	 * @return array(0 => $srcState, 1 => $dstState)
	 */
	public function GetMoveStates()
	{
		return [$this->srcState, $this->dstState];
	}


	// custom

	protected function updateState(string $type, array $change)
	{
		// Change can be a change or an add
		if ($type == "change") {
			for ($i = 0; $i < count($this->state); $i++) {
				if ($this->state[$i]["id"] == $change["id"]) {
					$this->state[$i] = $change;
					return;
				}
			}
			// Not found, add as new
			$this->state[] = $change;
		} else {
			for ($i = 0; $i < count($this->state); $i++) {
				// Search for the entry for this item
				if ($this->state[$i]["id"] == $change["id"]) {
					if ($type == "flags") {
						// Update flags
						$this->state[$i]["flags"] = $change["flags"];
					} else if ($type == "delete") {
						// Delete item
						array_splice($this->state, $i, 1);
					}
					return;
				}
			}
		}
	}
}