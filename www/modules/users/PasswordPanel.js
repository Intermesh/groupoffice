/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: PasswordPanel.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
 
GO.users.PasswordPanel = function(config)
{
	if(!config)
	{
		config={};
	}

	var prefix = (config.ldap_password) ? 'ldap_' : '';

	config.autoScroll=true;
	config.border=false;
	config.hideLabel=true;
	config.title = t("Change password", "users");
	config.layout='form';
	config.defaults={anchor:'100%'};
	config.defaultType = 'textfield';
	config.cls='go-form-panel';
	config.labelWidth=140;

	this.currentPasswordField = new Ext.form.TextField({
		inputType: 'password',
		fieldLabel: t("Current password", "users"),
		name: 'current_'+prefix+'password'
		});

	this.passwordField1 = new Ext.form.TextField({
		inputType: 'password',
		fieldLabel: t("New password", "users"),
		name: prefix+'password'
		});
	this.passwordField2 = new Ext.form.TextField({
		inputType: 'password',
		fieldLabel: t("Confirm password", "users"),
		name: prefix+'passwordConfirm'
		});
		
	this.recoveryEmailField = new Ext.form.TextField({
		fieldLabel: t("Recovery e-mail"),
		name: 'recoveryEmail',
		vtype:'emailAddress'
	});

	config.items=[
		new Ext.form.FieldSet({
			title: t("Current password", "users"),
			items:[
				new Ext.Container({
					html:t("You need to provide your current password to be able to modify the settings below."),
					style: {
            marginBottom: '10px'
					}
				}),
				this.currentPasswordField
			]
		}),
		new Ext.form.FieldSet({
			title: t("Change password", "users"),
			items:[
				this.passwordField1,
				this.passwordField2
			]
		}),
		new Ext.form.FieldSet({
			title: t("Recovery e-mail"),
			items:[
				new Ext.Container({
					html:t("This email address is used as recovery to send a forgotten password request to. Please use an email address that you can access from outside Group-Office."),
					style: {
            marginBottom: '10px'
					}
				}),
				this.recoveryEmailField
			]
		})
	];

	GO.users.PasswordPanel.superclass.constructor.call(this, config);
};

Ext.extend(GO.users.PasswordPanel, Ext.Panel,{
	onSaveSettings : function(){
		this.currentPasswordField.reset();
		this.passwordField1.reset();
		this.passwordField2.reset();
	}
});
