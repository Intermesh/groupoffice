/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: AccountPanel.js 21480 2017-09-28 06:46:58Z mschering $
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
	config.title = GO.users.lang.account;
	config.layout='form';
	config.defaults={anchor:'100%'};
	config.defaultType = 'textfield';
	//config.cls='go-form-panel';
	config.labelWidth=140;
	
	this.passwordField1 = new Ext.form.TriggerField({
		inputType: 'password', 
		fieldLabel: GO.users.lang['cmdFormLabelPassword'], 
		name: 'password',
		panel:this,
		triggerConfig:{
			tag: "img",
			src: Ext.BLANK_IMAGE_URL,
			cls: "x-form-trigger x-form-trigger-plus",
			'ext:qtip':GO.users.lang.generatePassword
		},
		onTriggerClick:function(){
			var pass = this.panel.randomPassword(6);
			this.panel.passwordField1.setValue(pass);
			this.panel.passwordField2.setValue(pass);
			
			Ext.MessageBox.alert(GO.users.lang['cmdFormLabelPassword'],GO.users.lang.generatedPasswordIs+": "+pass);
		}
		});
	this.passwordField2 = new Ext.form.TextField({
		inputType: 'password', 
		fieldLabel: GO.users.lang.confirmPassword, 
		name: 'passwordConfirm'
		});
		
	this.usernameField = new Ext.form.TextField({
			fieldLabel: GO.lang['strUsername'], 
			name: 'username'
		});
		
	this.enabledField = new Ext.ux.form.XCheckbox({
		boxLabel: GO.users.lang['cmdBoxLabelEnabled'],
		name: 'enabled',
		checked: true,
		hideLabel:true
	});
	
	this.forcePasswordChange = new Ext.ux.form.XCheckbox({
		boxLabel: GO.users.lang.forcePasswordChange,
		name: 'force_password_change',
		checked: false,
		hideLabel:true
	});

	this.invitationField = new Ext.form.Checkbox({
		boxLabel: GO.users.lang.sendInvitation,
		name: 'send_invitation',
		checked: true,
		hideLabel:true
	});

	config.items=[
		this.usernameField,
		this.passwordField1,
		this.passwordField2,
		{
			fieldLabel: GO.lang['strEmail'],
			name: 'email',
			allowBlank: false,
			vtype:'emailAddress',
			listeners: {
				change: function(field, newValue, oldValue) {
					if(this.items.item('recovery_email').getValue() == "") {
						this.items.item('recovery_email').setValue(newValue);
					}
				},
				scope: this
			}
		},
		{
			
			itemId: 'recovery_email',
			fieldLabel: GO.lang.strRecoveryEmail,
			name: 'recovery_email',
			allowBlank: false,
			vtype:'emailAddress'
		},
		{fieldLabel: GO.lang['strFirstName'], name: 'first_name', allowBlank: false},
		{fieldLabel: GO.lang['strMiddleName'], name: 'middle_name'},
		{fieldLabel: GO.lang['strLastName'], name: 'last_name', allowBlank: false},
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