/* global Ext, GO */

/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: PluginAccountDialog.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */

GO.moduleManager.onModuleReady('email',function(){
	Ext.override(GO.email.AccountDialog, {
		initComponent : GO.email.AccountDialog.prototype.initComponent.createInterceptor(function(){
							
			this.propertiesPanel.fileUpload=true;
			this.propertiesPanel.bodyCfg.enctype="multipart/form-data";
								
			this.smimePanel=new Ext.Panel({
				cls:'go-form-panel',
				title:t("SMIME settings", "smime"),
				disabled:true,
				items:[{
					xtype:'fieldset',
					labelWidth:160,
					title:t("PKCS12 certificate", "smime"),
					items:[
						{
							id:'smimeHasCert',
							xtype:'label',
							style:'display:block;margin-bottom:15px',
							html:t("You have uploaded a certificate already. SMIME support is enabled for this account.", "smime")
						},
						{
							xtype:'label',
							html:t("To upload a new PKCS12 certificate you must enter your Group-Office password. The Group-Office password must be different than your PCSK12 certificate for security reasons. No password is also prohibited.", "smime"),
							style:'display:block;margin-bottom:10px'
						},
						this.passwordField = new Ext.form.TextField({
							fieldLabel:GO.settings.config.product_name + ' ' + t("Password"),
							inputType:'password',
							name:'smime_password',
							width:200
						}),
						
						this.uploadFile = new GO.form.UploadFile({
							addText:t("Select new PKCS12 Certificate", "smime"),
							inputName : 'cert',
							max: 1
						}),
						this.deleteCert = new Ext.form.Checkbox({						
							boxLabel:t("Delete certificate", "smime"),
							labelSeparator: '',
							name: 'delete_cert',
							allowBlank: true,
							hideLabel:true,
							disabled:true
						}),
						this.downloadButton = new Ext.Button({
							handler:function(){
								window.open(GO.url("smime/certificate/download",{account_id:this.account_id}));
							},
							text:t("Download certificate", "smime"),
							disabled:true,
							scope:this
						})]
				},
				this.alwaysSignCheckbox = new Ext.ux.form.XCheckbox({
					//xtype:'checkbox',
					hideLabel:true,
					boxLabel:t("Always sign messages", "smime"),
					disabled:true,
					name:'always_sign'
				})
				]
			});
							
			this.tabPanel.add(this.smimePanel);
								
								
			this.on('show', function(){
				this.smimePanel.setDisabled(true);
				this.deleteCert.setValue(false);
			}, this);
								
			this.propertiesPanel.form.on("actioncomplete", function(form, action){
				//console.log(action.result);
				if(action.type=='submit') {
					this.uploadFile.clearQueue();
					this.passwordField.setValue('');

					// Ticket: 	#201408797
					// Need to create the upload inputfield again. 
					// Because otherwise the upload button doesn't work anymore when opening the dialog the 2nd time.
					this.uploadFile.createUploadInput(); 
					this.alwaysSignCheckbox.setDisabled(!action.result.cert);
					this.deleteCert.setDisabled(!action.result.cert);
					this.downloadButton.setDisabled(!action.result.cert);
				} else {
					this.smimePanel.setDisabled(false);
					this.alwaysSignCheckbox.setDisabled(!action.result.data.cert);
					this.deleteCert.setDisabled(!action.result.data.cert);
					this.downloadButton.setDisabled(!action.result.data.cert);
					if(!action.result.data.always_sign) {
						this.alwaysSignCheckbox.setValue(false);
					}
					if(!action.result.data.cert)
						Ext.getCmp('smimeHasCert').hide();
					else
						Ext.getCmp('smimeHasCert').show();
				}
			}, this);
		})
	});
});

