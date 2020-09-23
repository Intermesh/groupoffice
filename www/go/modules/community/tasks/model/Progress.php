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
    const NeedsAction = 'needs-action'; // Indicates the task needs action.
    const InProcess = 'in-progress';    // Indicates the task is in process.
    const Completed = 'completed';      // Indicates the task is completed.
    const Failed = 'failed';            // Indicates the task failed.
    const Cancelled = 'cancelled';      // Indicates the task was cancelled.

	static $db = [
		1 => self::NeedsAction,
		2 => self::InProcess,
		3 => self::Completed,
		4 => self::Failed,
		5 => self::Cancelled
	];
}