<?php

namespace GO\Imapauth;

use GO;

class ImapauthModule extends \GO\Base\Module {

	public static function initListeners() {
		//\GO::session()->addListener('beforelogin', 'GO\Imapauth\ImapauthModule', 'beforeLogin');

		$controller = new \GO\Core\Controller\AuthController();
		$controller->addListener('beforelogin', 'GO\Imapauth\ImapauthModule', 'beforeLogin');

		$controller->addListener('head', 'GO\Imapauth\ImapauthModule', 'head');
	}

	public static function head() {
		$conf = str_replace('config.php', 'imapauth.config.php', \GO::config()->get_config_file());
		if (file_exists($conf)) {
			require_once($conf);

			if (!empty($config[0]['imapauth_combo_domains'])) {
				$arr = explode(',', $config[0]['imapauth_combo_domains']);
				$domains = json_encode($arr);
				$default_domain = empty($config[0]['imapauth_default_domain']) ? $arr[0] : $config[0]['imapauth_default_domain'];
			}
		}

		if (!empty($domains) && !empty($default_domain)) {

			$script = <<< END
<script type='text/javascript'>
Ext.override(GO.dialog.LoginDialog, {
	initComponent : GO.dialog.LoginDialog.prototype.initComponent.createSequence(function(){
		var domains = $domains;
		domains.push('');
		var domainData = new Array();
		domainData[0] = ['-', domains[i]]
		for (var i=0; i<domains.length; i++) {
			if (domains[i]!='')
				domainData[i+1] = ['@'+domains[i], '@'+domains[i]];
		}

		var usernameField = this.formPanel.items.get('username');
		var fieldLabel = usernameField.fieldLabel;
		delete usernameField.fieldLabel;
		usernameField.flex=1;

		this.usernameCompositeField = new Ext.form.CompositeField({
			anchor:'100%',
			fieldLabel: fieldLabel,
			items:[
				usernameField,
				{
					flex:1,
					xtype:'combo',
					hideLabel: true,
					triggerAction : 'all',
					editable : false,
					selectOnFocus : true,
					width : 144,
					forceSelection : true,
					mode : 'local',
					value : '@$default_domain',
					hiddenName : 'domain',
					valueField : 'value',
					displayField : 'name',
					store : new Ext.data.SimpleStore({
							fields: ['name', 'value'],
							data: domainData
						})
				}
			]
		})

		this.formPanel.insert(2,this.usernameCompositeField);
	})
});
</script>

END;
		}
	}

//	public static function beforeControllerLogin($params, &$response) {
//		if (!isset($params['first_name'])) {
//			try {
//				$imap = new \GO\Base\Mail\Imap();
//				$imap->connect(
//								$config['host'], $config['port'], $mail_username, $password, $config['ssl']);
//
//				\GO::debug('IMAPAUTH: IMAP login succesful');
//				$imap->disconnect();
//
//				$user = \GO\Base\Model\User::model()->findSingleByAttribute('username', $go_username);
//				if (!$user) {
//					$response['needCompleteProfile'] = true;
//				}
//			} catch (\Exception $e) {
//				\GO::debug('IMAPAUTH: Authentication to IMAP server failed with Exception: ' . $e->getMessage() . ' IMAP error:' . $imap->last_error());
//				$imap->clear_errors();
//
//				\GO::session()->logout(); //for clearing remembered password cookies
//
//				return false;
//			}
//		}
//	}


	public static function beforeLogin($params, &$response) {

		$oldIgnoreAcl = \GO::setIgnoreAclPermissions(true);

		$ia = new Authenticator();

		if ($ia->setCredentials($params['username'], $params['password'])) {
			if ($ia->imapAuthenticate()) {
				if (!$ia->user) {
					\GO::debug("IMAPAUTH: Group-Office user doesn't exist.");
					if (!isset($params['first_name'])) {
						$response['needCompleteProfile'] = true;
						$response['success'] = false;

						$response['feedback'] = \GO::t("Please fill in some additional information to complete your user account.", "imapauth");
						return false;
					} else {
						//user doesn't exist. create it now
						$user = new \GO\Base\Model\User();
						$user->email = $ia->email;
						$user->username = $ia->goUsername;
						$user->password = $ia->imapPassword;
						$user->first_name = $params['first_name'];
						$user->middle_name = $params['middle_name'];
						$user->last_name = $params['last_name'];

						try {

							if (!$user->save()) {
								throw new \Exception("Could not save user: " . implode("\n", $user->getValidationErrors()));
							}
							if (!empty($ia->config['groups']))
								$user->addToGroups($ia->config['groups']);

							$ia->user = $user;

							$user->checkDefaultModels();

							//todo testen of deze regel nodig is om e-mail account aan te maken voor nieuwe gebruiker
							$ia->createEmailAccount($user, $ia->config, $ia->imapUsername, $ia->imapPassword);
						} catch (\Exception $e) {
							\GO::debug('IMAPAUTH: Failed creating user ' .
											$ia->goUsername . ' and e-mail ' . $ia->email .
											'Exception: ' .
											$e->getMessage(), E_USER_WARNING);
						}
					}
				}
			} else {
				$response['feedback'] = GO::t("Wrong username or password") . ' (IMAP)';
				return false;
			}
		}

		\GO::setIgnoreAclPermissions($oldIgnoreAcl);
	}

}
