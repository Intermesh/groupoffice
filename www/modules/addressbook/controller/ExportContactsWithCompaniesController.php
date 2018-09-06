<?php
/**
 * @copyright Copyright Intermesh BV.
 * @author Wesley Smits <wsmits@intermesh.nl>
 *
 */

/**
 * Class to export Contacts with companies together to a .csv file. 
 */

namespace GO\Addressbook\Controller;

use GO;

class ExportContactsWithCompaniesController extends \GO\Base\Controller\AbstractExportController{
	
	protected function allowGuests(){
		return array('*');
	}
	
	/**
	 * Export the contact model to a .csv, including the company.
	 * 
	 * @param array $params
	 */
	public function export($params) {
		
		GO::$disableModelCache=true;		
		GO::setMaxExecutionTime(420);
		
		$findParams = \GO::session()->values['contact']['findParams'];
		$model = \GO::getModel(\GO::session()->values['contact']['model']);

		
		$findParams->getCriteria()->recreateTemporaryTables();
		
		$findParams->joinRelation('company','LEFT');
						
		// Let the export handle all found records without a limit
		$findParams->limit(0); 

		
		// Create the statement
		$stmt = $model->find($findParams);
		
		// Create the csv file
		$csvFile = new \GO\Base\Fs\CsvFile(\GO\Base\Fs\File::stripInvalidChars('export.csv'));
		
		if(!$this->isCli()) {
			// Output the download headers
			\GO\Base\Util\Http::outputDownloadHeaders($csvFile, false);
		}
		
		$csvWriter = new \GO\Base\Csv\Writer('php://output');
		
		$headerPrinted = false; 
		$attrs = array();
		$compAttrs = array();
			
		foreach($stmt as $m){		
			
			$iterationStartUnix = time();			
			
			$header = array();
			$record = array();
			
			
			$attrs = $m->getAttributes();


			$compAttrs = $m->company->getAttributes();

			foreach($attrs as $attr=>$val){
				if (!$headerPrinted)
					$header[$attr] = $m->getAttributeLabel($attr);
				$record[$attr] = $m->{$attr};
			}


			foreach($compAttrs as $cattr=>$cval){

				if (!$headerPrinted) {
					$header[GO::t('company','addressbook').$cattr] = GO::t('company','addressbook').':'.$m->company->getAttributeLabel($cattr);
				}
				$record[GO::t('company','addressbook').$cattr] = $m->company->{$cattr};
			}
			
				
					
			if(!$headerPrinted){
				$csvWriter->putRecord($header);
				$headerPrinted = true;
			}

			$csvWriter->putRecord($record);
		}
	}
	
	
	////var/www/groupoffice-6.2/www/groupofficecli.php -r=addressbook/exportContactsWithCompanies/export -c=/etc/groupoffice/go62.loc/config.php --addressbook_id=1

	
	public function actionExport($params) {
		
		// When called from the interface of GO, then just run the parent function. (CLI is then not required)
		if(!$this->isCli()) {
			parent::actionExport($params);
			return;
		}
		
		$this->requireCli();
		\GO::session()->runAsRoot();
		
		$findParams = \GO\Base\Db\FindParams::newInstance()->criteria(\GO\Base\Db\FindCriteria::newInstance()->addCondition('addressbook_id', $params['addressbook_id']));
		
		
		$companyMode = false;
		if(isset($params['viewState']) && $params['viewState'] == 'company') { 
			$companyMode = true;
		}
		// Load the data from the session.
		if($companyMode) { 
			\GO::session()->values['company']['findParams'] = $findParams;
			\GO::session()->values['company']['model'] = "GO\Addressbook\Model\Company";
		} else {
			\GO::session()->values['contact']['findParams'] = $findParams;
			\GO::session()->values['contact']['model'] = "GO\Addressbook\Model\Contact";
	
		}
		
		$this->export($params);
		
	} 

	/**
	 * Return an empty array because we don't select attributes
	 * 
	 * @return array
	 */
	public function exportableAttributes() {
		return array();
	}
}
