<?php

namespace go\modules\community\tasks\model;

/**
 * Progress
 *
 * Defines the progress of a task(list).
 * If omitted, the default progress is defined as follows (in order of evaluation):
 * - "completed": if the "progress" property value of all tasks is "completed".
 * - "failed": if at least one "progress" property value of a task is "failed".
 * - "in-process": if at least one "progress" property value of a task is "in-process".
 * - "needs-action": If none of the other criteria match.
 */
abstract class Progress
{
    const NeedsAction = 1; // Indicates the task needs action.
    const InProcess = 2;    // Indicates the task is in process.
    const Completed = 3;      // Indicates the task is completed.
    const Failed = 4;            // Indicates the task failed.
    const Cancelled = 5;      // Indicates the task was cancelled.

	static $db = [
		self::NeedsAction => 'needs-action',
		self::InProcess => 'in-progress',
		self::Completed => 'completed',
		self::Failed => 'failed',
		self::Cancelled => 'cancelled'
	];
}