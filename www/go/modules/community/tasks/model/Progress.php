<?php

namespace go\modules\community\tasks\model;

/**
 * Class Progress
 * Defines the progress of this task.
 * If omitted, the default progress is defined as follows (in order of evaluation):
 * - "completed": if the "progress" property value of all participants is "completed".
 * - "failed": if at least one "progress" property value of a participant is "failed".
 * - "in-process": if at least one "progress" property value of a participant is "in-process".
 * - "needs-action": If none of the other criteria match.
 */
abstract class Progress
{
    const NeedsAction = 'needs-action'; // Indicates the task needs action.
    const InProcess = 'in-progress';    // Indicates the task is in process.
    const Completed = 'completed';      // Indicates the task is completed.
    const Failed = 'failed';            // Indicates the task failed.
    const Cancelled = 'cancelled';      // Indicates the task was cancelled.
}