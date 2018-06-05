/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: AccountPanel.js 22371 2018-02-13 14:17:26Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
 
GO.users.AccountPanel = function(config)
{
	if(!config)
	{
		config={};
	}
	
	config.autoHeight=true;
	config.border=true;
	config.hideLabel=true;
	config.title = t("Account", "users");
	config.layout='form';
	config.defaults={anchor:'100%'};
	config.defaultType = 'textfield';
	//config.cls='go-form-panel';
	config.labelWidth=140;
	
	this.passwordField1 = new Ext.form.TriggerField({
		inputType: 'password', 
		fieldLabel: t("Password", "users"), 
		name: 'password',
		panel:this,
		triggerConfig:{
			tag: "button",
			type: "button",
			cls: "x-form-trigger ic-refresh",
			'ext:qtip':t("Generate password", "users")
		},
		onTriggerClick:function(){
			var pass = this.panel.randomPassword(6);
			this.panel.passwordField1.setValue(pass);
			this.panel.passwordField2.setValue(pass);
			
			Ext.MessageBox.alert(t("Password", "users"),t("The generated password is", "users")+": "+Ext.util.Format.htmlEncode(pass));
		}
		});
	this.passwordField2 = new Ext.form.TextField({
		inputType: 'password', 
		fieldLabel: t("Confirm password", "users"), 
		name: 'passwordConfirm'
		});
		
	this.usernameField = new Ext.form.TextField({
			fieldLabel: t("Username"), 
			name: 'username'
		});
		
	this.enabledField = new Ext.ux.form.XCheckbox({
		boxLabel: t("Enabled", "users"),
		name: 'enabled',
		checked: true,
		hideLabel:true
	});
	
	this.forcePasswordChange = new Ext.ux.form.XCheckbox({
		boxLabel: t("Force password change on next login", "users"),
		name: 'force_password_change',
		checked: false,
		hideLabel:true
	});

	this.invitationField = new Ext.form.Checkbox({
		boxLabel: t("Send invitation", "users"),
		name: 'send_invitation',
		checked: true,
		hideLabel:true
	});

	config.items=[
		this.usernameField,
		this.passwordField1,
		this.passwordField2,
		{
			fieldLabel: t("E-mail"),
			name: 'email',
			allowBlank: false,
			vtype:'emailAddress',
			listeners: {
				change: function(field, newValue, oldValue) {
					if(this.items.item('recoveryEmail').getValue() == "") {
						this.items.item('recoveryEmail').setValue(newValue);
					}
				},
				scope: this
			}
		},
		{
			
			itemId: 'recoveryEmail',
			fieldLabel: t("Recovery e-mail"),
			name: 'recoveryEmail',
			allowBlank: false,
			vtype:'emailAddress'
		},
		{fieldLabel: t('Display name'), name: 'displayName', allowBlank: false},
		{
			xtype:'panel',
			hideLabel:true,
			border:false,
			bodyStyle:'padding:0',
			layout:'column',
			defaults:{bodyStyle:'padding:0',border:false, layout:'form', columnWidth:.5},
			items:[{
				items:this.enabledField
			},{
				items:this.invitationField
			}]
		},
		this.forcePasswordChange
	];

	GO.users.AccountPanel.superclass.constructor.call(this, config);		
}


Ext.extend(GO.users.AccountPanel, Ext.form.FieldSet,{
	randomPassword : function(length){
		var charsets = [
			"abcdefghijklmnopqrstuvwxyz",
			"ABCDEFGHIJKLMNOPQRSTUVWXYZ",
			"1234567890",
			"!@#$%^&*()<>,."];
		
		var pass = "";
		var i;
		
		//take one from each
		for(var x=0;x<charsets.length;x++){
			i = Math.floor(Math.random() * charsets[x].length);
			pass += charsets[x].charAt(i);
		}
		
		var combined = charsets.join("");
	
		length-=charsets.length;
		
		for(var x=0;x<length;x++)
		{
			i = Math.floor(Math.random() * combined.length);
			pass += combined.charAt(i);
		}
		return pass;
	},
	setUserId : function(user_id)
	{
		this.invitationField.setDisabled(user_id>0);
		this.invitationField.getEl().up('.x-form-item').setDisplayed(!user_id);
		//this.usernameField.setDisabled(user_id>0);
		this.passwordField2.allowBlank=(user_id>0);
		this.passwordField1.allowBlank=(user_id>0);
	}
});			
