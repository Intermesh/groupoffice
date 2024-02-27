<?php

//$updates['202402221543'][] = function() {
//	\go\core\db\Utils::runSQLFile(\GO()->getEnvironment()->getInstallFolder()->getFile("go/modules/community/calendar/install/migrate.sql"));
//};
//
//$updates['202402221543'][] = function(){ // migrate recurrence rules
//
//	$stmt = go()->getDbConnection()->query("SELECT id, recurrenceRule,`start` FROM calendar_event WHERE recurrenceRule IS NOT NULL");
//
//	while($row = $stmt->fetch()) {
//		try {
//			$rrule = \go\core\util\Recurrence::fromString($row['rrule'], new DateTime("@" . $row["start"]));
//			$data = ['recurrenceRule' => json_encode($rrule->toArray())];
//			go()->getDbConnection()->updateIgnore('calendar_event', $data, ['id' => $row['id']])->execute();
//		} catch(Exception $e) {
//			echo "RRULE Exception:  " . $e->getMessage() ."\n";
//		}
//	}
//};

// calendar views to filters
