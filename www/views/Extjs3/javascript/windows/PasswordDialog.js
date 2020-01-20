/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: PasswordDialog.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */

GO.dialog.PasswordDialog = function(config){
	
	if(!config)
	{
		config={};
	}
	

	config.modal=true;

	Ext.apply(this, config);
	
	
	this.formPanel = new Ext.FormPanel({
		labelWidth: 120, // label settings here cascade unless overridden
		layout: 'form',
		items: [{
			xtype: "fieldset",
			items: [
				this.passwordField = new Ext.form.TextField({
					fieldLabel: t("Password"),
					name: 'password',
					inputType: 'password',
					allowBlank: false,
					anchor: '100%'
				})]
		}]

	});
	
	

	
	//var logo = Ext.getBody().createChild({tag: 'div', cls: 'go-app-logo'});
	
	GO.dialog.PasswordDialog.superclass.constructor.call(this, {
		layout: 'fit',				
		width:400,
		height:140,
		resizable: false,
		modal:true,
		closeAction:'hide',
		closable: false,
		focus: function(){
			this.formPanel.form.findField('password').focus(true);
		}.createDelegate(this),
		items: [			
		this.formPanel
		],
		
		buttons: [{				
			text: t("Ok"),
			handler: function(){
				this.pressButton('ok');
			},
			scope:this
		},{
			text: t("Cancel"),
			handler:function(){
				this.pressButton('cancel');					
			},
			scope:this
		}
		],
		keys: [{
			key: Ext.EventObject.ENTER,
			fn: function(){
				this.pressButton('ok');
			},
			scope:this
		}]
	});
    
	this.addEvents({
		buttonpressed: true
	});
	
	if(config.fn){
		if(!config.scope)
			config.scope=this;
		
		this.on('buttonpressed',config.fn, config.scope);
	}
    
};

Ext.extend(GO.dialog.PasswordDialog, GO.Window, {
	pressButton : function(button){
		this.fireEvent('buttonpressed', button, this.formPanel.form.findField('password').getValue(), this);
		if(this.handler) {
			this.handler.call(this.scope || this, this, button, this.formPanel.form.findField('password').getValue())
		}
		this.formPanel.form.reset();
		this.hide();


	}
});


