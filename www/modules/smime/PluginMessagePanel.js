/* global GO, Ext */

/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: PluginMessagePanel.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */

GO.smime.checkCert = function(email,data) {
	var cls,text, icon;
	if(data.valid === false) {
		cls = 'smi-invalid';
		icon = 'ic-verified-user';
		text = t("The certificate is invalid!", "smime");
	} else if(data.emails.indexOf(email) === -1) {
		cls = 'smi-certemailmismatch';
		icon = 'ic-warning';
		text = t("Valid certificate but the e-mail of the certificate does not match the sender address of the e-mail.", "smime");
	} else if(data.hasOwnProperty('oscp') && !data.oscp) {
		cls = 'smi-invalid';
		icon = 'ic-close';
		text = t("The certificate is invalid!", "smime");
	} else {
		cls = 'smi-valid';
		icon = 'ic-check';
		text = t("Valid certificate", "smime");
	}
	return {cls,text,icon};
}

GO.moduleManager.onModuleReady('email',function(){

	Ext.override(GO.email.EmailClient, {
		initComponent : GO.email.EmailClient.prototype.initComponent.createSequence(function(){
			
			this.printButton.handler=function(){
				
				if(this.messagePanel.data.smime_signed && !this.messagePanel.smimeChecked){
					this.messagePanel.checkCert(true, function(){
						this.messagePanel.body.print();
					}, this);
				}else
				{
					this.messagePanel.body.print();
				}
			};
		})
	});
	

	Ext.override(GO.email.MessagePanel, {
		initComponent : GO.email.MessagePanel.prototype.initComponent.createSequence(function(){
			this.on('load',function(options, success, response, data, password){
				
				
				this.smimeChecked=false;
									
				if(password)
				{
					GO.smime.passwordsInSession[data.account_id]=true;
				}
									
				if(data.smime_encrypted){
					var el = this.body.down(".message-header").createChild({													
						html:'<i class="icon ic-lock"></i>' + t("This message is encrypted.", "smime"),
						cls:'smi-encrypt-notification'
					});
				}

				if(data.smime_signed){
					this.smimeLink = this.body.down(".message-header").createChild({													
						html:'<i class="icon ic-security"></i>' +t("This message is digitally signed. Click here to verify the signature and import the certificate.", "smime"),
						cls:'smi-sign-notification'
													
					});
											
					this.smimeLink.on('click', function(){this.checkCert();}, this);
				}
			});
		}),
		
		checkCert : function (hideDialog, callback, scope){

			if(!this.data.path)
				this.data.path="";
			
			if(!this.smimeChecked){
				GO.request({
					maskEl:this.getEl(),
					url: "smime/publicCertificate/verify",
					params:{
						uid:this.uid,
						account_id:this.account_id,
						mailbox:this.mailbox,
						filepath:this.data.path,
						email:this.data.sender					
					},
					scope: this,
					success: function(options, response, result)
					{
						var checkResult = GO.smime.checkCert(this.data.sender, result.data);
						if(!hideDialog) {
							let dlg = new GO.smime.CertificateDetailWindow();
							dlg.show();
							result.data.cls = checkResult.cls;
							result.data.text = checkResult.text;
							result.data.icon = checkResult.icon;
							dlg.load(result.data);
						}

						this.smimeLink.update('<i class="icon '+checkResult.icon+'"></i>'+ checkResult.text);
						this.smimeLink.addClass(checkResult.cls);

						this.smimeChecked=true;

						if(callback && scope)
							callback.call(scope, this);
					}							
				});
			}
		}	
	});
});

