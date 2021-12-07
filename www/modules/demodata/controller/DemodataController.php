<?php

namespace GO\Demodata\Controller;

use GO\Base\Model\User as GOUser;
use go\core\model\Module;
use go\core\fs\Blob;
use go\core\fs\File;
use go\core\fs\Folder;
use go\core\model\Acl;
use go\core\model\Group;
use go\core\model\Link;
use go\core\model\User;
use go\core\orm\Property;
use go\core\util\DateTime;
use go\modules\community\addressbook\model\Address;
use go\modules\community\addressbook\model\Contact;
use go\modules\community\addressbook\model\AddressBook;
use go\modules\community\addressbook\model\EmailAddress;
use go\modules\community\addressbook\model\PhoneNumber;
use go\modules\community\addressbook\model\Url;
use go\modules\community\bookmarks\model\Bookmark;
use go\modules\community\bookmarks\model\Category;
use go\modules\community\comments\model\Comment;
use go\modules\community\tasks\model\Task;

class DemodataController extends \GO\Base\Controller\AbstractController {
	
	protected function allowGuests() {
		return array('create');
	}

	protected function actionCreate($params){




		
		if(\GO::modules()->summary){
			$title = "Welcome to ".\GO::config()->product_name;
			
			$announcement = \GO\Summary\Model\Announcement::model()->findSingleByAttribute('title',$title);
			if(!$announcement){
				$announcement = new \GO\Summary\Model\Announcement();
				$announcement->title=$title;
				$announcement->content='This is a demo announcements that administrators can set.<br />Have a look around.<br /><br />We hope you\'ll enjoy Group-Office as much as we do!';

				if($announcement->save()){			
					$announcement->acl->addGroup(\GO::config()->group_everyone);
				}
			}
		}

		

		
		
			
		if(\GO::modules()->projects2){

			// todo: Demo data for employees was here but this is moved to business module. (need to refactor)

			$templates = \GO\Projects2\Model\Template::model()->find();
			
			$folderTemplate = $templates->fetch();
			$projectTemplate = $templates->fetch();
			
			$status = \GO\Projects2\Model\Status::model()->findSingle();
			
			$type = \GO\Projects2\Model\Type::model()->findSingleByAttribute('name', 'Demo');
			if(!$type){
				$type = new \GO\Projects2\Model\Type();
				$type->name='Demo';
				if(!$type->save())
				{
					var_dump($type->getValidationErrors());
					exit();
				}
				$type->acl->addGroup(\GO::config()->group_internal, \GO\Base\Model\Acl::WRITE_PERMISSION);
			}
			
			
			$folderProject = \GO\Projects2\Model\Project::model()->findSingleByAttribute('name','Demo');
			if(!$folderProject){
				$folderProject = new \GO\Projects2\Model\Project();
				$folderProject->name='Demo';
				$folderProject->start_time=time();
				$folderProject->description='Just a placeholder for sub projects.';
				$folderProject->template_id=$folderTemplate->id;
				$folderProject->type_id=$type->id;
				$folderProject->status_id=$status->id;
				if(!$folderProject->save()){
						var_dump($folderProject->getValidationErrors());
					exit();
				}
				
			}
			
			
			$rocketProject = \GO\Projects2\Model\Project::model()->findSingleByAttribute('name','[001] Develop Rocket 2000');
			if(!$rocketProject){
				$rocketProject = new \GO\Projects2\Model\Project();
				$rocketProject->type_id=$type->id;
				$rocketProject->status_id=$status->id;
				$rocketProject->name='[001] Develop Rocket 2000';
				$rocketProject->description='Better range and accuracy';
				$rocketProject->template_id=$projectTemplate->id;
				$rocketProject->parent_project_id=$folderProject->id;
				$rocketProject->start_time=time();
				$rocketProject->due_time=\GO\Base\Util\Date::date_add(time(),0,1);
				$rocketProject->company_id=$acme->id;
				$rocketProject->contact_id=$wile->id;
//				$rocketProject->budget=20000;
				$rocketProject->save();
				
				$resource = new \GO\Projects2\Model\Resource();
				$resource->project_id=$rocketProject->id;
				$resource->user_id=$demo->id;
				$resource->budgeted_units=100;
				$resource->external_fee=80;
				$resource->internal_fee=40;
				$resource->save();
				
				$resource = new \GO\Projects2\Model\Resource();
				$resource->project_id=$rocketProject->id;
				$resource->user_id=$elmer->id;
				$resource->budgeted_units=16;
				$resource->external_fee=120;
				$resource->internal_fee=60;
				$resource->save();
				
				$resource = new \GO\Projects2\Model\Resource();
				$resource->project_id=$rocketProject->id;
				$resource->user_id=$linda->id;
				$resource->budgeted_units=16;
				$resource->external_fee=90;
				$resource->internal_fee=45;
				$resource->save();
				
				
				$groupTask = new \GO\Projects2\Model\Task();
				$groupTask->project_id=$rocketProject->id;
				$groupTask->description='Design';
				$groupTask->duration=8*60;
				$groupTask->user_id=$demo->id;
				$groupTask->save();
				
				
				$task = new \GO\Projects2\Model\Task();
				$task->parent_id=$groupTask->id;
				$task->project_id=$rocketProject->id;
				$task->description='Functional design';
				$task->percentage_complete=100;
				$task->duration=8*60;
				$task->user_id=$demo->id;
				$task->save();
				
				$task = new \GO\Projects2\Model\Task();
				$task->parent_id=$groupTask->id;
				$task->project_id=$rocketProject->id;
				$task->description='Technical design';
				$task->percentage_complete=50;
				$task->duration=8*60;
				$task->user_id=$demo->id;
				$task->save();
				
				
				$groupTask = new \GO\Projects2\Model\Task();
				$groupTask->project_id=$rocketProject->id;
				$groupTask->description='Implementation';
				$groupTask->duration=8*60;
				$groupTask->user_id=$demo->id;
				$groupTask->save();
				
				
				$task = new \GO\Projects2\Model\Task();
				$task->parent_id=$groupTask->id;
				$task->project_id=$rocketProject->id;
				$task->description='Models';
				$task->duration=4*60;
				$task->user_id=$demo->id;
				$task->save();
				
				$task = new \GO\Projects2\Model\Task();
				$task->parent_id=$groupTask->id;
				$task->project_id=$rocketProject->id;
				$task->description='Controllers';
				$task->duration=2*60;
				$task->user_id=$demo->id;
				$task->save();
				
				$task = new \GO\Projects2\Model\Task();
				$task->parent_id=$groupTask->id;
				$task->project_id=$rocketProject->id;
				$task->description='Views';
				$task->duration=6*60;
				$task->user_id=$demo->id;
				$task->save();
				
				$groupTask = new \GO\Projects2\Model\Task();
				$groupTask->project_id=$rocketProject->id;
				$groupTask->description='Testing';
				$groupTask->duration=8*60;
				$groupTask->user_id=$demo->id;
				$groupTask->save();
				
				
				$task = new \GO\Projects2\Model\Task();
				$task->parent_id=$groupTask->id;
				$task->project_id=$rocketProject->id;
				$task->description='GUI';
				$task->duration=8*60;
				$task->user_id=$elmer->id;
				$task->save();
				
				$task = new \GO\Projects2\Model\Task();
				$task->parent_id=$groupTask->id;
				$task->project_id=$rocketProject->id;
				$task->description='Security';
				$task->duration=8*60;
				$task->user_id=$elmer->id;
				$task->save();
				
				
				$expenseBudget = new \GO\Projects2\Model\ExpenseBudget();
				$expenseBudget->description='Machinery';
				$expenseBudget->nett=10000;
				$expenseBudget->project_id=$rocketProject->id;
				$expenseBudget->save();
				
				$expense = new \GO\Projects2\Model\Expense();				
				$expense->description='Rocket fuel';
				$expense->project_id=$rocketProject->id;
				$expense->invoice_id = "1234";
				$expense->nett=3000;
				$expense->save();
				
				
				$expense = new \GO\Projects2\Model\Expense();				
				$expense->expense_budget_id=$expenseBudget->id;
				$expense->description='Fuse machine';
				$expense->invoice_id = "1235";
				$expense->project_id=$rocketProject->id;
				$expense->nett=2000;
				$expense->save();
			}
			
			$launcherProject = \GO\Projects2\Model\Project::model()->findSingleByAttribute('name','[001] Develop Rocket Launcher');
			if(!$launcherProject){
				$launcherProject = new \GO\Projects2\Model\Project();
				$launcherProject->type_id=$type->id;
				$launcherProject->status_id=$status->id;
				$launcherProject->name='[001] Develop Rocket Launcher';
				$launcherProject->description='Better range and accuracy';
				$launcherProject->template_id=$projectTemplate->id;
				$launcherProject->parent_project_id=$folderProject->id;
				$launcherProject->start_time=time();
				$launcherProject->due_time=\GO\Base\Util\Date::date_add(time(),0,1);
				$launcherProject->company_id=$acme->id;
				$launcherProject->contact_id=$wile->id;
				$launcherProject->save();
				
				
				$resource = new \GO\Projects2\Model\Resource();
				$resource->project_id=$launcherProject->id;
				$resource->user_id=$demo->id;
				$resource->external_fee=80;
				$resource->internal_fee=40;
				$resource->budgeted_units=16;
				$resource->save();
			}
			
			$folder = \GO\Files\Model\Folder::model()->findByPath('projects2/template-icons',true);
			$folder->syncFilesystem();
			
		}
		
		
		if(Module::isInstalled('community', 'bookmarks')) {
			$category = Category::find()->where(['name' => go()->t('General', 'community', 'bookmarks')])->single();
	
			if(!$category){
				$category = new Category();
				$category->name = go()->t('General', 'community', 'bookmarks');	
				$category->setAcl([Group::ID_INTERNAL => Acl::LEVEL_READ]);
				$category->save();				
			}

			$bookmark = Bookmark::find()->where(['name' => 'Group-Office'])->single();

			if(!$bookmark){
				$bookmark = new Bookmark();
				$bookmark->categoryId =$category->id;
				$bookmark->name='Group-Office';
				$bookmark->content='https://www.group-office.com';
				$bookmark->loadMetaData();
				$bookmark->save();
			}
			
			$bookmark = Bookmark::find()->where(['name' => 'Intermesh'])->single();

			if(!$bookmark){
				$bookmark = new Bookmark();
				$bookmark->categoryId =$category->id;
				$bookmark->name='Intermesh';
				$bookmark->content='https://www.intermesh.nl';
				$bookmark->loadMetaData();
				$bookmark->save();
			}
		}
		
//		if(\GO::modules()->postfixadmin){
//			
//			
//			$domainModel= \GO\Postfixadmin\Model\Domain::model()->findSingleByAttribute('domain', 'acmerpp.demo');
//			if(!$domainModel){
//				$domainModel = new \GO\Postfixadmin\Model\Domain();
//				$domainModel->domain='acmerpp.demo';
//				$domainModel->save();
//			}
//				
//			$this->_createMailbox($domainModel, $demo);
//			$this->_createMailbox($domainModel, $elmer);
//			$this->_createMailbox($domainModel, $linda);
//			
//			
//		}
		
		if(\GO::modules()->savemailas){
			//link some demo mails
			$mimeFile = new \GO\Base\Fs\File(\GO::modules()->savemailas->path.'install/demo.eml');			
			\GO\Savemailas\Model\LinkedEmail::model()->createFromMimeFile($mimeFile, $wile);
			\GO\Savemailas\Model\LinkedEmail::model()->createFromMimeFile($mimeFile, $john);
			if(\GO::modules()->projects){
				\GO\Savemailas\Model\LinkedEmail::model()->createFromMimeFile($mimeFile, $rocketProject);
				\GO\Savemailas\Model\LinkedEmail::model()->createFromMimeFile($mimeFile, $launcherProject);
			}
			
			$mimeFile = new \GO\Base\Fs\File(\GO::modules()->savemailas->path.'install/demo2.eml');
			\GO\Savemailas\Model\LinkedEmail::model()->createFromMimeFile($mimeFile, $wile);
			\GO\Savemailas\Model\LinkedEmail::model()->createFromMimeFile($mimeFile, $john);
			if(\GO::modules()->projects){
				\GO\Savemailas\Model\LinkedEmail::model()->createFromMimeFile($mimeFile, $rocketProject);
				\GO\Savemailas\Model\LinkedEmail::model()->createFromMimeFile($mimeFile, $launcherProject);
			}
		}
		
		//useful for other modules to create stuff
		$this->fireEvent('demodata', array('users'=>array('demo'=>$demo, 'linda'=>$linda, 'elmer'=>$elmer)));
		
		if(\GO::modules()->demodata) {
			\GO::modules()->demodata->delete();
			
			go()->rebuildCache();
		}
		
		if(!$this->isCli()){
			header('Location: ' .go()->getSettings()->URL);
			exit();
		}
	}
	
	
	private function _createMailbox($domainModel, $demo){
		$demoMailbox = \GO\Postfixadmin\Model\Mailbox::model()->findSingleByAttribute('username', $demo->email);
		if(!$demoMailbox){
			$demoMailbox = new \GO\Postfixadmin\Model\Mailbox();
			$demoMailbox->domain_id=$domainModel->id;
			$demoMailbox->username=$demo->email;
			$demoMailbox->password='demo';
			$demoMailbox->name=$demo->name;
			$demoMailbox->save();				
		}			

		$accountModel = \GO\Email\Model\Account::model()->findSingleByAttribute('username', $demoMailbox->username);
		if(!$accountModel){
			$accountModel = new \GO\Email\Model\Account();
			$accountModel->user_id=$demo->id;
//			$accountModel->checkImapConnectionOnSave=false;
			$accountModel->host = 'localhost';
			$accountModel->port = 143;

			$accountModel->username = $demoMailbox->username;
			$accountModel->password = 'demo';

			$accountModel->smtp_host = "localhost";
			$accountModel->smtp_port = 25;
			$accountModel->save();
			$accountModel->addAlias($accountModel->username, $demoMailbox->name);			
		}
		
		
		
		
		
		
		
		
		
	
		
		
		
		
	}

	private function _setUserContact($user) {
		$contact = $user->createContact();

		$company = \GO\Addressbook\Model\Company::model()->findSingleByAttribute('name', 'ACME Rocket Powered Products');
		if (!$company) {
			$company = new \GO\Addressbook\Model\Company();
			$company->setAttributes(array(
					'addressbook_id' => $contact->addressbook_id,
					'name' => 'ACME Rocket Powered Products',
					'address' => '1111 Broadway',
					'address_no' => '',
					'zip' => '10019',
					'city' => 'New York',
					'state' => 'NY',
					'country' => 'US',
					'post_address' => '1111 Broadway',
					'post_address_no' => '',
					'post_zip' => '10019',
					'post_city' => 'New York',
					'post_state' => 'NY',
					'post_country' => 'US',
					'phone' => '(555) 123-4567',
					'fax' => '(555) 123-4567',
					'email' => 'info@acmerpp.demo',
					'homepage' => 'http://www.acmerpp.demo',
					'bank_no' => '',
					'vat_no' => 'US 1234.56.789.B01',
					'user_id' => 1,
					'comment' => 'The name Acme became popular for businesses by the 1920s, when alphabetized business telephone directories such as the Yellow Pages began to be widespread. There were a flood of businesses named Acme (some of these still survive[1]). For example, early Sears catalogues contained a number of products with the "Acme" trademark, including anvils, which are frequently used in Warner Bros. cartoons.[2]'
			));
			$company->save();
		}

		$contact->company_id = $company->id;
		$contact->function = 'CEO';
		$contact->cellular = '06-12345678';
		$contact->address = '1111 Broadway';
		$contact->address_no = '';
		$contact->zip = '10019';
		$contact->city = 'New York';
		$contact->state = 'NY';
		$contact->country = 'US';
		$contact->save();
		
		
		
	}

}
