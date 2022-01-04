/* global GO, Ext */

/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: PluginEmailComposer.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */

Ext.ns("GO.smime");

GO.smime.passwordsInSession = {};

GO.moduleManager.onModuleReady('email',function(){
	Ext.override(GO.email.EmailComposer, {
		
		initComponent : GO.email.EmailComposer.prototype.initComponent.createSequence(function(){
			this.optionsMenu.add(['-',this.signCheck = new Ext.menu.CheckItem({
				text:t("Sign with SMIME", "smime"),
				checked: false,
				listeners : {
					checkchange: function(check, checked) {	
						
						this.sendParams['sign_smime'] = checked
						? '1'
						: '0';
					},
					scope:this
				}
			}),this.encryptCheck = new Ext.menu.CheckItem({
				text:t("Encrypt with SMIME", "smime"),
				checked: false,
				listeners : {
					checkchange: function(check, checked) {						
						this.sendParams['encrypt_smime'] = checked
						? '1'
						: '0';
					},
					scope:this
				}
			})]);
		
			this.on('afterShowAndLoad',function(){


				if(!this.sendParams.encrypt_smime) {
					this.sendParams.encrypt_smime = "0";
				}

				if(!this.sendParams.sign_smime) {
					this.sendParams.sign_smime = "0";
				}

				this.signCheck.setChecked(this.sendParams.sign_smime);
				this.encryptCheck.setChecked(this.sendParams.encrypt_smime);

				this.checkSmimeSupport();
			}, this);

			
			this.fromCombo.on('select',function(){
				this.checkSmimeSupport();
			}, this);
			
			
			this.on('beforesendmail',this.askPassword,this);
			
		}),	
		
		
		askPassword : function(){				

			var record = this.fromCombo.store.getById(this.fromCombo.getValue());
				
			if(!GO.smime.passwordsInSession[record.data.account_id] && (this.sendParams['sign_smime']=="1" || this.sendParams['encrypt_smime']=="1")){
				
				if(!this.passwordDialog)
				{
					this.passwordDialog = new GO.dialog.PasswordDialog({
						title:t("Please enter the password of your SMIME certificate.", "smime"),
						fn:function(btn, password){
							
							var record = this.fromCombo.store.getById(this.fromCombo.getValue());
					
							if(btn==="cancel")
								return false;

							GO.request({
								url: 'smime/certificate/checkPassword',
								success: function(response, options, result){							
									if(result.passwordCorrect)
									{
										var record = this.fromCombo.store.getById(this.fromCombo.getValue());
										GO.smime.passwordsInSession[record.data.account_id]=true;
										this.sendMail();
									}else
									{
										this.askPassword();							
									}
								},
								params: {
									account_id: record.data.account_id,
									password:password
								},
								scope:this
							});

						},
						scope:this
					});
				}
				this.passwordDialog.show();
				
				return false;
			}else
			{
				return true;
			}			
		},

		checkSmimeSupport : function(){
			var current_id = this.fromCombo.getValue();			
			var record = this.fromCombo.store.getById(current_id);

			this.signCheck.setDisabled(!record.json.has_smime_cert);
			this.encryptCheck.setDisabled(!record.json.has_smime_cert);
			
			if(record.json.has_smime_cert && record.json.always_sign=="1"){
				// Record has an smime cert and always sign is set to true
				this.signCheck.setChecked(true);
				this.sendParams['sign_smime'] ="1";	
			} else if(!record.json.has_smime_cert){
				// Record does not have an smime cert
				this.signCheck.setChecked(false);
				this.sendParams['sign_smime'] ="0";
			} else {
				// // Record has an smime cert and always sign is set to false
				// this.signCheck.setChecked(false);
				// this.sendParams['sign_smime'] ="0";
			}
		}
	}
	);
});
        
