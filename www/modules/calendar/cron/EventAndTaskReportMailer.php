<?php


namespace GO\Calendar\Cron;


class EventAndTaskReportMailer extends \GO\Base\Cron\AbstractCron {
	
	/**
	 * Return true or false to enable the selection fo users and groups for 
	 * this cronjob.
	 * 
	 * CAUTION: This will give the run() function a different behaviour. 
	 *					Please see the documentation of the run() function 
	 *					to see what is different.
	 */
	public function enableUserAndGroupSupport(){
		return true;
	}
	
	/**
	 * Get the unique name of the Cronjob
	 * 
	 * @return String
	 */
	public function getLabel(){
		return \GO::t("Today's events and tasks mailer", "calendar");
	}
	
	/**
	 * Get the unique name of the Cronjob
	 * 
	 * @return String
	 */
	public function getDescription(){
		return \GO::t("Send an email with the today's events and tasks to every user in the cron", "calendar");
	}
	
	/**
	 * The code that needs to be called when the cron is running
	 * 
	 * If $this->enableUserAndGroupSupport() returns TRUE then the run function 
	 * will be called for each $user. (The $user parameter will be given)
	 * 
	 * If $this->enableUserAndGroupSupport() returns FALSE then the 
	 * $user parameter is null and the run function will be called only once.
	 * 
	 * @param \GO\Base\Cron\CronJob $cronJob
	 */
	public function run(\GO\Base\Cron\CronJob $cronJob){
		
		foreach ($cronJob->getAllUsers() as $user) {			
			\GO::language()->setLanguage($user->language); // Set the users language
			\GO::session()->runAsRoot();
			
			$pdf = $this->_getUserPdf($user);
			$this->_sendEmail($user,$pdf);
			
			\GO::language()->setLanguage(); // Set the admin language
		}
		
	}
	
	/**
	 * Get the pdf of the given user
	 * 
	 * @param \GO\Base\Model\User $user
	 * @return String
	 */
	private function _getUserPdf(\GO\Base\Model\User $user){		
		$pdf = new eventAndTaskPdf();
		$pdf->setTitle($user->name); // Set the title in the header of the PDF
		$pdf->setSubTitle(\GO::t("Today's events and tasks", "calendar")); // Set the subtitle in the header of the PDF
		$pdf->render($user); // Pass the data to the PDF object and let it draw the PDF
		
		return $pdf->Output('','s');// Output the pdf
	}
	
	/**
	 * Send the email to the users
	 * 
	 * @param \GO\Base\Model\User $user
	 * @param eventAndTaskPdf $pdf
	 * @return Boolean
	 */
	private function _sendEmail(\GO\Base\Model\User $user,$pdf){
		
		$filename = \GO\Base\Fs\File::stripInvalidChars($user->name).'.pdf'; //Set the PDF filename
		$filename = str_replace(',', '', $filename);
		
		$mailSubject = \GO::t("Today's events and tasks", "calendar");
		$body = \GO::t("You can find a list of today's events and tasks in the attached PDF.", "calendar");
		
		$message = \GO\Base\Mail\Message::newInstance(
										$mailSubject
										)->setFrom(\GO::config()->webmaster_email, \GO::config()->title)
										->addTo($user->email);

		$message->setHtmlAlternateBody(nl2br($body));
		$message->attach(new \Swift_Attachment($pdf,$filename,'application/pdf'));
		\GO::debug('CRON SEND MAIL TO: '.$user->email);
		return \GO\Base\Mail\Mailer::newGoInstance()->send($message);
	}
}

/**
 * Class to render the PDF
 */
class eventAndTaskPdf extends \GO\Base\Util\Pdf {
			
	private	$_headerFontSize = '14';
	private	$_headerFontColor = '#3194D0';
	private $_nameFontSize = '12';
	private $_timeFontSize = '12';
	private $_descriptionFontSize = '12';
	public $font = 'dejavusans';
	public $font_size=10;
	
	/**
	 * Set the title that will be printed in the header of the PDF document
	 * 
	 * @param String $title
	 */
	public function setTitle($title){
		$this->title = $title;
	}
	
	/**
	 * Set the subtitle that will be printed in the header of the PDF document
	 * 
	 * @param String $subtitle
	 */
	public function setSubTitle($subtitle){
		$this->subtitle = $subtitle;
	}
	
	/**
	 * Render the pdf content.
	 * 
	 * This will render the events and the tasks of the user that is given with 
	 * the $user param.
	 * 
	 * @param \GO\Base\Model\User $user
	 */
	public function render($user){
		$this->AddPage();
		$this->setEqualColumns(2, ($this->pageWidth/2)-10);
		$eventsString = \GO::t("Appointments", "calendar");
		$tasksString = \GO::t("Tasks", "tasks");
		
		$textColor = $this->TextColor;
		$textFont = $this->getFontFamily();
		
		$events = $this->_getEvents($user);
		$tasks = $this->_getTasks($user);
		
		// RENDER EVENTS
		$this->writeHTML('<h2 style="color:'.$this->_headerFontColor.';font-size:'.$this->_headerFontSize.'px;">'.$eventsString.'</h2>', true, false, false, false, 'L');
		$this->Ln();
		
		foreach($events as $event)
			$this->_renderEventRow($event);

		$this->Ln();
		
		// RENDER TASKS
		$this->writeHTML('<h2 style="color:'.$this->_headerFontColor.';font-size:'.$this->_headerFontSize.'px;">'.$tasksString.'</h2>', true, false, false, false, 'L');
		$this->Ln();
		
		foreach($tasks as $task)
			$this->_renderTaskRow($task);
	}
	
	/**
	 * Get all today's events from the database.
	 * 
	 * @param \GO\base\Model\User $user
	 * @return \GO\Calendar\Model\Event[]
	 */
	private function _getEvents($user){
		$defaultCalendar = \GO\Calendar\Model\Calendar::model()->getDefault($user);		
		
		$todayStart = strtotime('today')+1;
		$todayEnd = strtotime('tomorrow');
		
		if($defaultCalendar){
			$findParams = \GO\Base\Db\FindParams::newInstance()
			->select()
			//->order(array('start_time','name'),array('ASC','ASC'))
			->criteria(\GO\Base\Db\FindCriteria::newInstance()
					->addCondition('calendar_id', $defaultCalendar->id)
			);
			$events = \GO\Calendar\Model\Event::model()->findCalculatedForPeriod($findParams,$todayStart,$todayEnd);
			
			return $events; //->fetchAll();
		} else {
			return array();
		}
	}
	
	/**
	 * Get all today's tasks from the database.
	 * 
	 * @param \GO\base\Model\User $user
	 * @return \GO\Tasks\Model\Task[]
	 */
	private function _getTasks($user){	
//		$defaultTasklist = \GO\Tasks\Model\Tasklist::model()->getDefault($user);
//
//		$todayStart = strtotime('today');
//		$todayEnd = strtotime('tomorrow');
//
//		if($defaultTasklist){
//			$findParams = \GO\Base\Db\FindParams::newInstance()
//			->select()
//			->order(array('start_time','name'),array('ASC','ASC'))
//			->criteria(\GO\Base\Db\FindCriteria::newInstance()
//					->addCondition('tasklist_id', $defaultTasklist->id)
//					->addCondition('start_time', $todayStart,'>=')
//					->addCondition('start_time', $todayEnd,'<')
//			);
//			$tasks = \GO\Tasks\Model\Task::model()->find($findParams);
//
//			return $tasks->fetchAll();
//		} else {
			return array();
//		}
	}
	
	/**
	 * Render the event row in the PDF
	 * 
	 * @param \GO\Calendar\Model\Event $event
	 */
	private function _renderEventRow(\GO\Calendar\Model\LocalEvent $event){	

		$html = '';
		$html .= '<tcpdf method="renderLine" />';
		$html .= '<b><font style="font-size:'.$this->_timeFontSize.'px">'.\GO\Base\Util\Date\DateTime::fromUnixtime($event->getAlternateStartTime())->format('H:i').' - '.\GO\Base\Util\Date\DateTime::fromUnixtime($event->getAlternateEndTime())->format('H:i').'</font> <font style="font-size:'.$this->_nameFontSize.'px">'.\GO\Base\Util\StringHelper::text_to_html($event->getName(), true).'</font></b>';
		$realEvent = $event->getEvent();
		if(!empty($realEvent->description))
			$html .= 	'<br /><font style="font-size:'.$this->_descriptionFontSize.'px">'.$realEvent->getAttribute('description', 'html').'</font>';
		
		$this->writeHTML($html, true, false, false, false, 'L');
	}
		
	/**
	 * Render the task row in the PDF
	 * 
	 * @param \GO\Tasks\Model\Task $task
	 */
	private function _renderTaskRow($task){
		
		$html = '';
		$html .= '<tcpdf method="renderLine" />';
		$html .= '<b><font style="font-size:'.$this->_nameFontSize.'px">'.\GO\Base\Util\StringHelper::text_to_html($task->getAttribute('name', 'html'),true).'</font></b>';
		if(!empty($task->description))
			$html .= 	'<br /><font style="font-size:'.$this->_descriptionFontSize.'px">'.$task->getAttribute('description', 'html').'</font>';

		$this->writeHTML($html, true, false, false, false, 'L');
	}
	
	/**
	 * Function to render the 2 dashes before the title
	 */
	protected function renderLine(){
		$oldX = $this->getX();
		$this->setX($oldX-14);
		$this->write(10, '--');
		$this->setX($oldX);
	}
}
