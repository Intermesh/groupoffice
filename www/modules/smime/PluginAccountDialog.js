/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: PluginAccountDialog.js 17553 2014-05-27 13:03:02Z mschering $
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
				title:GO.smime.lang.settings,
				disabled:true,
				items:[{
					xtype:'fieldset',
					labelWidth:160,
					title:GO.smime.lang.pkcs12Cert,
					items:[
						{
							id:'smimeHasCert',
							xtype:'label',
							style:'display:block;margin-bottom:15px',
							html:GO.smime.lang.youHaveAcert
						},
						{
							xtype:'label',
							html:GO.smime.lang.pkcs12CertInfo,
							style:'display:block;margin-bottom:10px'
						},{
							xtype:'textfield',
							fieldLabel:GO.settings.config.product_name+' '+GO.lang.strPassword,
							inputType:'password',
							name:'smime_password',
							width:200
						},
						
						this.uploadFile = new GO.form.UploadFile({
							addText:GO.smime.lang.selectPkcs12Cert,
							inputName : 'cert',
							max: 1
						}),
						this.deleteCert = new Ext.form.Checkbox({						
							boxLabel:GO.smime.lang.deleteCert,
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
							text:GO.smime.lang.downloadCert,
							disabled:true,
							scope:this
						})]
				},
				this.alwaysSignCheckbox = new Ext.ux.form.XCheckbox({
					//xtype:'checkbox',
					hideLabel:true,
					disabled:true,
					boxLabel:GO.smime.lang.alwaysSign,
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
				if(action.type=='submit'){
					this.uploadFile.clearQueue();
					
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
	})
});
        