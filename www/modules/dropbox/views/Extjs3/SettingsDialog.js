GO.dropbox.SettingsDialog = Ext.extend(GO.dialog.TabbedFormDialog , {
	
	initComponent : function(){
		
		Ext.apply(this, {
			goDialogId:'dropboxsettings',
			title: GO.lang['cmdSettings'],
			formControllerUrl: 'dropbox/settings',
			height:220,
			width:500,
			enableApplyButton:false
//			,
//			helppage:'dropbox_user_manual#Settings'
		});
		
		GO.dropbox.SettingsDialog.superclass.initComponent.call(this);	
	},
	  
	buildForm : function () {
		
		this.appKeySecretDescriptionComp = new GO.form.HtmlComponent({
			style:'margin-bottom:5px',
			html:GO.dropbox.lang.appKeySecretDescription
		});
		
		this.keyField = new Ext.form.TextField({
			name:'app_key',
			fieldLabel:GO.dropbox.lang.appKey
		});
		
		this.secretField = new Ext.form.TextField({
			name:'app_secret',
			fieldLabel:GO.dropbox.lang.appSecret
		});
		
		this.callbackUriField = new GO.form.PlainField({
			name:'callback_uri',
			fieldLabel:GO.dropbox.lang.callbackUri
		});
		
		this.webHookUriField = new GO.form.PlainField({
			name:'webhook_uri',
			fieldLabel:GO.dropbox.lang.webhookUri
		});
		
		this.propertiesPanel = new Ext.Panel({
			title:GO.lang['strProperties'],
			layout:'form',
			defaults:{
				anchor: '100%'
			},
			items:[
				this.appKeySecretDescriptionComp,
				this.keyField,
				this.secretField,
				this.callbackUriField,
				this.webHookUriField
      ]				
		});

    this.addPanel(this.propertiesPanel);
	}
});