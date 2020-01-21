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
						html:t("This message is encrypted.", "smime"),
						cls:'smi-encrypt-notification'
					});
				}
									
				if(data.smime_signed){
					this.smimeLink = this.body.down(".message-header").createChild({													
						html:t("This message is digitally signed. Click here to verify the signature and import the certificate.", "smime"),
						cls:'smi-sign-notification'
													
					});
											
					this.smimeLink.on('click', function(){this.checkCert();}, this);
				}
			});
		}),
		
		checkCert : function (hideDialog, callback, scope){
			if(!hideDialog){
				if(!this.certWin){
					this.certWin = new GO.Window({
						title:t("SMIME Certificate", "smime"),
						width:600,
						height:400,
						autoScroll: true,
						closeAction:'hide',
						layout:'fit',
						items:[this.certPanel = new Ext.Panel({
							bodyStyle:'padding:10px'
						})]
					});
				}
				this.certWin.show();
			}	
			if(!this.data.path)
				this.data.path="";
			
			if(!this.smimeChecked){
				GO.request({
					maskEl:hideDialog ? this.getEl() : this.certPanel.getEl(),
					url: "smime/certificate/verify",
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
						if(!hideDialog)
							this.certPanel.update(result.html);

						this.smimeLink.update(result.text);
						this.smimeLink.addClass(result.cls);

						this.smimeChecked=true;

						if(callback && scope)
							callback.call(scope, this);
					}							
				});
			}
		}	
	});
});

