<?php
/**
 * WARNING: This controller does not check authentication!
 * 
 * Controller with some maintenance functions
 */

namespace GO\Core\Controller;

use GO;
use GO\Base\Controller\AbstractController;

class CorrectionController extends AbstractController {
	
	protected function allowGuests() {
		return array();
	}
	
	protected function checkSecurityToken(){
		return true;
	}
	
	
	protected function init() {
		\GO::$disableModelCache=true; //for less memory usage
		\GO::setMaxExecutionTime(0); //allow long runs		
		GO::setMemoryLimit(256);
		ini_set('display_errors','on');		
	}
		
	/**
	 * ?r=correction/TimeregistrationExternalFee
	 * 
	 * InternalFee/Externalfee for hours registration problem
	 * 
	 * @param array $params
	 * @return 
	 */
	public function actionTimeregistrationExternalFee($params){
		$this->render('externalHeader');
		echo "<style type='text/css'>"
					.".messagebox {width:500px; position:absolute; top:32px; margin-left:-250px; left:50%; padding:.75rem 1.25rem; margin-bottom:1rem;border:1px solid transparent; border-radius:.25rem;} "
					.".error {background-color: #f8d7da; color: #721c24; border-color: #f5c6cb;}"
					.".success {background-color: #d4edda; color: #155724; border-color: #c3e6cb;}"
					.".info {background-color: #cce5ff; color: #004085; border-color: #b8daff;}"
				."</style>";
		if(!\GO::user()->isAdmin()){
			echo "<div class='messagebox error'>";
			echo "Only an admin may use this tool.";
			echo "</div>";
			$this->render('externalFooter');
			return;
		}
		echo "<script language='javascript'>"
			."function toggle(source) {"
			."	checkboxes = document.getElementsByClassName('checkbox');"
			."	for(var i=0, n=checkboxes.length;i<n;i++) {"
			."		checkboxes[i].checked = source.checked;"
			."  }"
			."}"
			."</script>";
		echo "<p><b>This page contains a tool to fix the External rates of time entries on projects created with version 6.2.83 - 6.2.87</b></p>";
		if(\GO::request()->isPost()){
			$ids = array();
			if(isset($_POST['timeentry'])){
				$ids = array_keys($_POST['timeentry']);
			} else {
				echo "<div class='messagebox info'>";
				echo "No entries selected";
				echo "</div>";
			}
			if(!empty($ids)){
				if($this->_fixRates($ids)){
					echo "<div class='messagebox success'>";
					echo "The selected entries are changed successfully";
					echo "</div>";
				} else {
					echo "<div class='messagebox error'>";
					echo "Oops, something went wrong, please try again or correct manually";
					echo "</div>";
				}
			}
		}
		echo "<p>The records below seems to be affected, please check the records you want to correct automatically and then click on \"CORRECT\".</p>";
		$affectedEntriesSQL = 'SELECT h.id as ID, from_unixtime(h.ctime) as Created_at, user.username AS Username, project.name as Project, ifnull(h.internal_fee,"null") as Timeentry_Internal, ifnull(h.external_fee,"null") as Timeentry_External, ifnull(r.internal_fee,"null") as Employee_Internal, ifnull(r.external_fee,"null") as Employee_External, ifnull(a.external_rate,"-") as Activity_External, ifnull(income_id,"No") as Invoiced ' 
		.'FROM `pr2_hours` h '
		.'left join pr2_projects project on h.project_id=project.id '
		.'left join pr2_resource_activity_rate a on a.activity_id=h.standard_task_id and a.employee_id=h.user_id and a.project_id=h.project_id '
		.'inner join pr2_resources r on h.user_id=r.user_id AND h.project_id=r.project_id '
		.'inner join go_users user on h.user_id=user.id '
		.'WHERE h.external_fee != r.external_fee '
		.'AND h.external_fee = r.internal_fee  '
		.'AND (a.external_rate IS NULL OR h.external_fee != a.external_rate) '
		.'AND h.ctime >= unix_timestamp("2018-03-06");';
		$affectedStmnt = \GO::getDbConnection()->query($affectedEntriesSQL);
		$count = $affectedStmnt->rowCount();
		echo "<p>Affected time entries: <b>$count</b></p>";
		$headersPrinted =false;
		echo "<form method='post'>";
		echo "<table width='100%' style='text-align:left;' cellspacing='0'>";
		while($affected = $affectedStmnt->fetch(\PDO::FETCH_ASSOC)){
			if(!$headersPrinted){
				echo "<tr>";
				$keys = array_keys($affected);
				echo "<th style='border-bottom:1px solid #ddd;'><input type='checkbox' onClick='toggle(this);'/></th>";
				foreach($keys as $key){
					echo "<th style='border-bottom:1px solid #ddd;'>$key</th>";
				}
				echo '<th style="border-bottom:1px solid #ddd;">This will be the correction (if selected and applied):</th>';
				echo "</tr>";
				$headersPrinted =true;
			}
			echo "<tr>";
			$checked = isset($_POST['timeentry']) && in_array($affected['ID'], array_keys($_POST['timeentry']))?'checked':'';
			echo "<th><input type='checkbox' class='checkbox' id='".$affected['ID']."' name='timeentry[".$affected['ID']."]' $checked></th>";
			foreach($affected as $key=>$value){
				echo "<td>$value</td>";
			}
			$newrate = $affected['Activity_External']!== "-"?$affected['Activity_External']:$affected['Employee_External'];
			echo "<td>The external fee of this time entry will be changed from <b>".$affected['Timeentry_External']."</b> to <b>".$newrate."</b></td>";
			echo "</tr>";
		}
		echo "</table>";
		echo "<p></p>";
		echo "<input type='submit' value='CORRECT'>";
		echo "</form>";
		$this->render('externalFooter');
	}
	
	/**
	 * Fix the given timeentries
	 * 
	 * @param array $timeentryIds
	 * @return boolean
	 */
	private function _fixRates(array $timeentryIds){
		$ids = implode($timeentryIds,',');
		$updatequery = 'update pr2_hours h '
		.'inner join pr2_resources r on h.user_id=r.user_id AND h.project_id=r.project_id '
		.'left join pr2_resource_activity_rate a on a.activity_id=h.standard_task_id and a.employee_id=h.user_id and a.project_id=h.project_id '
		.'set h.external_fee= coalesce(a.external_rate, r.external_fee) '
		.'WHERE h.id IN('.$ids.')'
		.'AND h.external_fee != r.external_fee '
		.'AND h.external_fee = r.internal_fee  '
		.'AND (a.external_rate IS NULL OR h.external_fee != a.external_rate) '
		.'AND ctime >= unix_timestamp("2018-03-06");';
		if(\GO::getDbConnection()->query($updatequery) !== false){
			return true;
		}
		return false;
	}
}
